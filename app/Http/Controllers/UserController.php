<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        try {
            
            $users = $this->userService->getAllUsers();
            Log::info($users[0]);
            return view('users.index', compact('users'));
        } catch (Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch users.');
        }
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:8|confirmed',
                'phoneNumber' => 'required|string|max:20',
                'role' => 'required|string|in:regulator,manufacturer,logistics,vendor',
                'address' => 'required|string',
                'status' => 'required|string|in:active,inactive,pending',
            ]);

            // Handle compliance document if present
            if ($request->hasFile('complianceDocument')) {
                $path = $request->file('complianceDocument')->store('complianceDocuments', 'public');
                $validated['compliance_document'] = $path;
            }

            $validated['created_by'] = auth()->id() ?? 'ADMIN_001';
            
            $user = $this->userService->createUser($validated);

            if (!$user->api_synced) {
                return redirect()->route('users.index')
                    ->with('warning', 'User created in database but blockchain sync failed. Will retry sync later.')
                    ->with('error_details', $user->api_sync_error);
            }

            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
        } catch (Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userService->getUser($id);
            return view('users.show', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $user = $this->userService->getUser($id);
            return view('users.edit', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'role' => 'required|string|in:user,admin,manager',
                'address' => 'required|string',
                'status' => 'required|string|in:active,inactive,pending',
            ]);

            if ($request->hasFile('compliance_document')) {
                $path = $request->file('compliance_document')->store('compliance_documents', 'public');
                $validated['compliance_document'] = $path;
            }

            $user = $this->userService->updateUser($id, $validated);
            return redirect()->route('users.index')->with('success', 'User updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function sensor()
    {
        $count = 50;
        $readings = [];
        $startTime = strtotime('2024-02-01 10:00:00');
        $batchIds = ['BATCH_001', 'BATCH_002'];
        $sensorIds = ['SENSOR_001', 'SENSOR_002', 'SENSOR_003'];
        $types = ['temperature', 'humidity', 'pressure'];
        $locations = ['Transit Hub NYC', 'Highway I-95', 'Distribution Center PA', 'Vendor Warehouse FL'];

        for ($i = 0; $i < $count; $i++) {
            $timestamp = date('Y-m-d\TH:i:s\Z', $startTime + ($i * 900)); // Every 15 minutes
            $temperature = rand(10, 90) / 10; // Random temperature between 1.0 and 9.0
            $batchId = $batchIds[array_rand($batchIds)];
            $sensorId = $sensorIds[array_rand($sensorIds)];
            $type = $types[array_rand($types)];
            $location = $locations[array_rand($locations)];
            $type = 
            
            $readings[] = [
                "id" => "READ_" . str_pad($i + 4, 3, '0', STR_PAD_LEFT),
                "sensorId" => $sensorId,
                "readingType" => $type,
                "value" => $temperature,
                "batchId" => $batchId,
                "location" => $location,
                "timestamp" => $timestamp,
                "hasExcursion" => $temperature < 2.0 || $temperature > 8.0
            ];
        }


        // Generate extra readings and merge with existing ones
        $existingData = json_decode(file_get_contents('storage/app/mock/sensor_readings.json'), true);
        $existingData['readings'] = array_merge($existingData['readings'], $readings);

        // Save updated sensor readings
        file_put_contents(
            'storage/app/mock/sensor_readings.json',
            json_encode($existingData, JSON_PRETTY_PRINT)
        );
    }

    
}