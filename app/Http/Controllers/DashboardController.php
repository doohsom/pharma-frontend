<?php

namespace App\Http\Controllers;

use App\Actions\DashboardActions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private $readings = [];
    private $apiBaseUrl = 'http://localhost:8080/api';
    private $cacheTimeout = 30;

    private $dashboard;
    public function __construct(DashboardActions $actions)
    {
        $this->readings = Cache::remember('sensor_readings', $this->cacheTimeout, function () {
            try {
                $response = Http::get("{$this->apiBaseUrl}/readings");
                if ($response->successful()) {
                    return $response->json()['readings'] ?? [];
                }
                return [];
            } catch (\Exception $e) {
                \Log::error("Error fetching sensor readings: " . $e->getMessage());
                return [];
            }
        });
        $this->dashboard = $actions;
    }

    public function index()
    {
        $dashboard = $this->dashboard->index(\Auth::user());
        $data = $dashboard['data'];
        $generalStats = [];
        return view('dashboard', compact('data', 'generalStats'));
    }

    public function indexes()
    {
        $generalStats = $this->getGeneralStats();
        $roleSpecificStats = $this->getRoleSpecificStats();
        $chartData = $this->getChartData();

        return view('dashboard.unified', compact('generalStats', 'roleSpecificStats', 'chartData'));
    }

    private function getGeneralStats()
    {
        return [
            'total_metrics' => [
                'users' => User::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'batches' => Batch::count(),
                'sensors' => Sensor::count(),
            ],
            'active_metrics' => [
                'active_users' => User::where('last_login', '>=', now()->subDays(30))->count(),
                'active_sensors' => Sensor::where('status', 'active')->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'active_batches' => Batch::where('status', 'in_production')->count(),
            ],
            'alerts' => [
                'low_stock' => Product::where('stock', '<=', 10)->count(),
                'expiring_batches' => Batch::where('expiry_date', '<=', now()->addMonths(3))->count(),
                'sensor_alerts' => Sensor::where('status', 'error')->count(),
                'critical_orders' => Order::where('status', 'pending')
                    ->where('created_at', '<=', now()->subDays(7))
                    ->count(),
            ]
        ];
    }

    private function getRoleSpecificStats()
    {
        $user = auth()->user();
        $stats = [];

        switch ($user->role) {
            case 'manufacturer':
                $stats = [
                    'production' => [
                        'daily_production' => Batch::where('created_at', '>=', now()->startOfDay())->count(),
                        'weekly_production' => Batch::where('created_at', '>=', now()->startOfWeek())->count(),
                        'monthly_production' => Batch::where('created_at', '>=', now()->startOfMonth())->count(),
                    ],
                    'quality' => [
                        'sensor_readings' => Sensor::where('last_reading_time', '>=', now()->subHours(24))
                            ->select('type', DB::raw('AVG(last_reading) as average_reading'))
                            ->groupBy('type')
                            ->get(),
                        'quality_alerts' => Sensor::where('status', 'error')
                            ->where('last_reading_time', '>=', now()->subHours(24))
                            ->count(),
                    ],
                ];
                break;

            case 'regulator':
                $stats = [
                    'compliance' => [
                        'expired_batches' => Batch::where('expiry_date', '<', now())->count(),
                        'non_compliant_sensors' => Sensor::where('status', 'error')->count(),
                        'quality_violations' => Sensor::where('status', 'error')
                            ->where('last_reading_time', '>=', now()->subDays(30))
                            ->count(),
                    ],
                    'auditing' => [
                        'batches_by_status' => Batch::select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->get(),
                        'sensor_health' => Sensor::select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->get(),
                    ],
                ];
                break;

            case 'vendor':
                $stats = [
                    'inventory' => [
                        'low_stock_items' => Product::where('stock', '<=', 10)->get(),
                        'popular_products' => Order::select('product_id', DB::raw('count(*) as order_count'))
                            ->groupBy('product_id')
                            ->orderByDesc('order_count')
                            ->limit(5)
                            ->get(),
                    ],
                    'orders' => [
                        'recent_orders' => Order::where('user_id', $user->id)
                            ->latest()
                            ->limit(5)
                            ->get(),
                        'order_status' => Order::where('user_id', $user->id)
                            ->select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->get(),
                    ],
                ];
                break;

            case 'logistics':
                $stats = [
                    'shipments' => [
                        'pending_shipments' => Batch::where('status', 'completed')->count(),
                        'in_transit' => Batch::where('status', 'shipped')->count(),
                        'delivered' => Batch::where('status', 'delivered')->count(),
                    ],
                    'tracking' => [
                        'recent_deliveries' => Batch::where('status', 'delivered')
                            ->latest()
                            ->limit(5)
                            ->get(),
                        'delivery_performance' => [
                            'on_time' => Batch::where('status', 'delivered')
                                ->where('updated_at', '<=', DB::raw('DATE_ADD(created_at, INTERVAL 7 DAY)'))
                                ->count(),
                            'delayed' => Batch::where('status', 'delivered')
                                ->where('updated_at', '>', DB::raw('DATE_ADD(created_at, INTERVAL 7 DAY)'))
                                ->count(),
                        ],
                    ],
                ];
                break;
        }

        return $stats;
    }

    private function getChartData()
    {
        return [
            'monthly_trends' => [
                'orders' => Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'batches' => Batch::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'sensors' => Sensor::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
            ],
            'status_distribution' => [
                'batches' => Batch::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get(),
                'sensors' => Sensor::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get(),
                'orders' => Order::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get(),
            ],
        ];
    }

    public function show()
    {
        // Group all readings by batch first
        $batches = collect($this->readings)->groupBy('batch_id');
        
        // Get latest batch readings
        $latestBatch = $batches
            ->sortByDesc(function ($batch) {
                return $batch->first()['reading_timestamp'];
            })
            ->first() ?? collect([]);

        // Get latest readings by type from the latest batch
        $latestReadings = $latestBatch
            ->groupBy('reading_type')
            ->map(function ($readings) {
                return $readings->first();
            });

        // Get excursions grouped by batch
        $excursions = $batches->map(function ($batchReadings) {
            return [
                'batch_id' => $batchReadings->first()['batch_id'],
                'timestamp' => $batchReadings->first()['reading_timestamp'],
                'readings' => $batchReadings->where('has_excursion', true)->values()
            ];
        })->filter(function ($batch) {
            return $batch['readings']->isNotEmpty();
        })->values();

        // Prepare batch summary data
        $batchSummary = $batches->map(function ($batchReadings) {
            $timestamp = $batchReadings->first()['reading_timestamp'];
            return [
                'batch_id' => $batchReadings->first()['batch_id'],
                'timestamp' => Carbon::parse($timestamp)->format('Y-m-d H:i:s'),
                'total_readings' => $batchReadings->count(),
                'excursions' => $batchReadings->where('has_excursion', true)->count(),
                'readings_by_type' => $batchReadings->groupBy('reading_type')->map(function ($typeReadings) {
                    return [
                        'avg_value' => $typeReadings->avg('value'),
                        'excursions' => $typeReadings->where('has_excursion', true)->count()
                    ];
                })
            ];
        });

        // Prepare time series data for charts
        $chartData = collect(['temperature', 'humidity', 'pressure'])->mapWithKeys(function ($type) use ($batches) {
            return [$type => $batches->map(function ($batchReadings) use ($type) {
                $typeReadings = $batchReadings->where('reading_type', $type);
                return [
                    'timestamp' => Carbon::parse($batchReadings->first()['reading_timestamp'])->format('Y-m-d H:i:s'),
                    'avg_value' => $typeReadings->avg('value'),
                    'min_value' => $typeReadings->min('value'),
                    'max_value' => $typeReadings->max('value'),
                    'batch_id' => $batchReadings->first()['batch_id']
                ];
            })->sortBy('timestamp')->values()];
        });

        return view('dashboard', [
            'latestReadings' => $latestReadings,
            'excursions' => $excursions,
            'chartData' => $chartData,
            'batchSummary' => $batchSummary,
            'latestBatch' => $latestBatch->groupBy('reading_type')
        ]);
    }

    public function refreshData()
    {
        Cache::forget('sensor_readings');
        return redirect()->route('dashboard')->with('message', 'Data refreshed successfully');
    }
}