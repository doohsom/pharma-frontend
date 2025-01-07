<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SensorService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SensorController extends Controller
{
    protected $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    public function index(Request $request)
    {
        try {
            $batchId = $request->query('batch_id');
            $sensors = $this->sensorService->getSensors($batchId);

            return response()->json([
                'sensors' => $sensors
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch sensors:', [
                'error' => $e->getMessage(),
                'batch_id' => $request->query('batch_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch sensors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

 
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sensors' => 'required|array|min:1',
                'sensors.*.batch_id' => 'required|string',
                'sensors.*.type' => 'required|string|in:temperature,humidity,pressure',
                'sensors.*.location' => 'required|numeric|min:0|max:360'
            ]);

            $sensors = $this->sensorService->createSensors($validated['sensors']);

            return response()->json([
                'message' => 'Sensors created successfully',
                'sensors' => $sensors
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk sensor creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create sensors',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getBatchSensors($batchId)
    {
        try {
            $sensors = $this->sensorService->getBatchSensors($batchId);

            return response()->json([
                'sensors' => $sensors
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch batch sensors:', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch sensors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}
