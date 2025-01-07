<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BatchApiService;
use App\Services\OrderApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\ExcursionNotificationService;
use App\Services\SensorService;
use App\Services\UserApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BatchController extends Controller
{
    private $apiBaseUrl = 'http://localhost:8030/api';

    private $apiBaseUrls = 'http://localhost:5050/api';
    private $cacheTimeout = 30;



    protected $batchService;
    protected $orderService;
    protected $sensorService;
    protected $userService;
    protected $excursionService;

    public function __construct(BatchApiService $batchService, OrderApiService $orderService, SensorService $sensorService, UserApiService $userApiService, ExcursionNotificationService $excursionService)
    {
        $this->batchService = $batchService;
        $this->orderService = $orderService;
        $this->sensorService =  $sensorService;
        $this->userService = $userApiService;
        $this->excursionService = $excursionService;

    }

    public function index(): View
    {
        $batches = $this->batchService->getAllBatches();
        Log::info($batches);
        return view('batch.index', compact('batches'));
    }

    public function indexer()
    {
        Log::info('hello');
        $readings = Cache::remember('sensor_readings', $this->cacheTimeout, function () {
            try {
                $response = Http::get("{$this->apiBaseUrls}/batches");
                Log::info('hjsfhjs',[$response]);
                if ($response->successful()) {
                    return $response->json()['readings'] ?? [];
                }
                return [];
            } catch (\Exception $e) {
                \Log::error("Error fetching sensor readings: " . $e->getMessage());
                return [];
            }
        });

        // Group readings by batch and prepare summary
        $batches = collect($readings)
            ->groupBy('batchId')
            ->map(function ($batchReadings) {
                $timestamp = $batchReadings->first()['timestamp'];
                return [
                    'batch_id' => $batchReadings->first()['batchId'],
                    'timestamp' => Carbon::parse($timestamp),
                    'total_readings' => $batchReadings->count(),
                    'excursions' => $batchReadings->where('hasExcursion', true)->count(),
                    'readings_by_type' => $batchReadings->groupBy('readingType')
                        ->map(function ($typeReadings) {
                            return [
                                'avg_value' => round($typeReadings->avg('value'), 2),
                                'excursions' => $typeReadings->where('hasExcursion', true)->count()
                            ];
                        })
                ];
            })
            ->sortByDesc('timestamp');

        return view('batch.eindex', [
            'batches' => $batches
        ]);
    }

     /**
     * Get batch details for the modal
     */
    public function show(string $id): JsonResponse
    {
        $batch = $this->batchService->getBatchById($id);
        
        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json($batch);
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:created,in_transit,delivered'
        ]);
        Log::info($validated);
        Log::info($id);

        $batch = $this->batchService->updateBatchStatus($id, $validated['status']);
        
        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json($batch);
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'order_id' => 'required|string',
                'logistics_id' => 'required|string',
                'notes' => 'required|string'
            ]);

            // Verify order exists and status
            $order = $this->orderService->getOrder($validated['order_id']);
            if (!$order) {
                throw new Exception('Order not found');
            }

            Log::info('Order details:', $order);
            $order = $order['order'];

            if ($order['status'] !== 'to_be_batched') {
                throw new Exception('Order must be in to_be_batched state to create a batch');
            }

            // Verify logistics provider
            $logisticsProvider = $this->userService->getUser($validated['logistics_id']);
            Log::info('Logistics provider details:', [
                'provider' => $logisticsProvider,
                'requested_id' => $validated['logistics_id']
            ]);

            if (!$logisticsProvider) {
                throw new Exception('Logistics provider not found');
            }

            if ($logisticsProvider['role'] !== 'logistics') {
                throw new Exception('Selected user is not a logistics provider');
            }

            if ($logisticsProvider['status'] !== 'active') {
                throw new Exception('Logistics provider is not active');
            }

            // Create batch
            $batch = $this->batchService->createBatch([
                'order_id' => $validated['order_id'],
                'logistics_id' => $validated['logistics_id'],
                'notes' => $validated['notes']
            ]);

            return redirect()->route('batches.show', $batch['id'])
                ->with('success', 'Batch created successfully');

        } catch (Exception $e) {
            Log::error('Batch creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function create($orderId)
    {
        try {
            Log::info('orderr');
            // Get order details
            $order = $this->orderService->getOrder($orderId);
            Log::info('maybe an order', ['htiei' => $order['order']]);
            Log::info('maybe a product', ['htiei' => $order['product']]);

            if (!$order) {
                throw new Exception('Order not found');
            }

            $orderResult = $order['order'];
            $productResult = $order['product'];
            if ($orderResult['status'] !== 'to_be_batched') {
                throw new Exception('Order must be in to_be_batched state to create a batch');
            }

            Log::info('exceotion statys');

            // Get active logistics providers
            $logisticsPartners = $this->userService->getUsersByRole('logistics');
            
            // Filter for active logistics providers only
            $activeLogisticsPartners = array_filter($logisticsPartners, function($partner) {
                return $partner['status'] === 'active';
            });

            Log::info('Available logistics partners:', [
                'total' => count($logisticsPartners),
                'active' => count($activeLogisticsPartners)
            ]);

            return view('batch.create', [
                'order' => $order['order'],
                'logisticsPartners' => array_values($activeLogisticsPartners)
            ]);

        } catch (Exception $e) {
            return redirect()->route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

 public function shows($batchId)
{
    $currentMinute = now()->format('Y-m-d-H-i');
    $cacheKey = "batch_{$batchId}_{$currentMinute}";
    Log::info($cacheKey);

    $readings = Cache::remember($cacheKey, 60, function () use ($batchId) {
        try {
            $response = Http::get("{$this->apiBaseUrl}/readings/batch/{$batchId}");
            if ($response->successful()) {
                return $response->json()['readings'] ?? [];
            }
            return [];
        } catch (\Exception $e) {
            \Log::error("Error fetching batch readings: " . $e->getMessage());
            return [];
        }
    });

    $batchReadings = collect($readings);
    
    if ($batchReadings->isEmpty()) {
        return redirect()->route('batches.index')
            ->with('error', 'Batch not found');
    }

    $batchReadings->each(function ($reading) use ($batchId) {
        if ($reading['hasExcursion']) {
            // Use cache to prevent duplicate notifications
            $excursionKey = "excursion_{$reading['id']}";
            if (!Cache::has($excursionKey)) {
                $this->excursionService->notifyExcursion(
                    $batchId,
                    $reading['sensorId'],
                    $reading
                );
                // Cache the excursion notification for 1 hour
                Cache::put($excursionKey, true, 3600);
            }
        }
    });

    // Get the start time from the earliest reading
    $timestamp = $batchReadings->min('timestamp');
    $startTime = $timestamp ? Carbon::parse($timestamp) : now();

    // Get latest reading for each sensor
    $latestSensorReadings = $batchReadings
        ->groupBy('sensorId')
        ->map(function ($readings) {
            return $readings->sortByDesc('timestamp')->first();
        })
        ->values(); 

    // Manually paginate readings
    $page = request()->get('page', 1);
    $perPage = 20;
    $allReadings = $batchReadings->sortByDesc('timestamp')->values();
    $paginatedReadings = new \Illuminate\Pagination\LengthAwarePaginator(
        $allReadings->forPage($page, $perPage),
        $allReadings->count(),
        $perPage,
        $page,
        ['path' => request()->url()]
    );

    // Add AJAX response handler
    if (request()->ajax()) {
        return response()->json([
            'latestSensorReadings' => $latestSensorReadings,
            'paginatedReadings' => $paginatedReadings->items(),
            'excursions' => $batchReadings->where('hasExcursion', true)->count(),
            'lastUpdated' => now()->toIso8601String()
        ]);
    }

    return view('batch.eshow', [
        'batchId' => $batchId,
        'timestamp' => $startTime,  // Added this line
        'latestSensorReadings' => $latestSensorReadings,
        'excursions' => $batchReadings->where('hasExcursion', true),
        'paginatedReadings' => $paginatedReadings,
        'lastUpdated' => now()
    ]);
}

    public function sensorReadings($batchId, $sensorId)
    {
        Log::info("{$this->apiBaseUrl}/readings/batch/{$batchId}");

        $readings = Cache::remember("batch_{$batchId}_sensor_{$sensorId}", $this->cacheTimeout, function () use ($batchId, $sensorId) {
            try {
                $response = Http::get("{$this->apiBaseUrl}/readings/batch/{$batchId}");
                if ($response->successful()) {
                    return collect($response['readings'] ?? [])
                        ->where('sensorId', $sensorId)
                        ->sortByDesc('timestamp')
                        ->values()
                        ->all();
                }
                return [];
            } catch (\Exception $e) {
                \Log::error("Error fetching sensor readings: " . $e->getMessage());
                return [];
            }
        });

        return response()->json([
            'readings' => $readings
        ]);
    }

    public function showSensor($batchId, $sensorId)
{
    $currentMinute = now()->format('Y-m-d-H-i');
    $cacheKey = "batch_{$batchId}_{$currentMinute}";

    $readings = Cache::remember($cacheKey, 60, function () use ($batchId) {
        try {
            $response = Http::get("{$this->apiBaseUrl}/readings/batch/{$batchId}");
            if ($response->successful()) {
                return $response->json()['readings'] ?? [];
            }
            return [];
        } catch (\Exception $e) {
            \Log::error("Error fetching batch readings: " . $e->getMessage());
            return [];
        }
    });

    $batchReadings = collect($readings);
    
    // Filter readings for specific sensor
    $sensorReadings = $batchReadings->where('sensorId', $sensorId)->values();
    
    if ($sensorReadings->isEmpty()) {
        return redirect()->route('batch.show', $batchId)
            ->with('error', 'Sensor data not found');
    }

    // Get latest reading
    $latestReading = $sensorReadings->sortByDesc('timestamp')->first();

    // Get statistics
    $stats = [
        'min' => $sensorReadings->min('value'),
        'max' => $sensorReadings->max('value'),
        'avg' => round($sensorReadings->average('value'), 2),
        'excursions' => $sensorReadings->where('hasExcursion', true)->count()
    ];

    // Paginate readings
    $page = request()->get('page', 1);
    $perPage = 50;
    $paginatedReadings = new \Illuminate\Pagination\LengthAwarePaginator(
        $sensorReadings->sortByDesc('timestamp')->forPage($page, $perPage),
        $sensorReadings->count(),
        $perPage,
        $page,
        ['path' => request()->url()]
    );

    if (request()->ajax()) {
        return response()->json([
            'latestReading' => $latestReading,
            'readings' => $paginatedReadings->items(),
            'stats' => $stats,
            'lastUpdated' => now()->toIso8601String()
        ]);
    }

    return view('batch.sensor', [
        'batchId' => $batchId,
        'sensorId' => $sensorId,
        'latestReading' => $latestReading,
        'readings' => $paginatedReadings,
        'stats' => $stats,
        'lastUpdated' => now()
    ]);
}

    public function refreshBatch($batchId)
    {
        Cache::forget("batch_{$batchId}");
        return redirect()->route('batch.show', $batchId)
            ->with('message', 'Data refreshed successfully');
    }


    private function calculateStdDev($values)
    {
        $mean = $values->avg();
        $variance = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        })->avg();
        
        return sqrt($variance);
    }

    public function refresh()
    {
        Cache::forget('sensor_readings');
        return redirect()->route('batch.eindex')
            ->with('message', 'Data refreshed successfully');
    }
}