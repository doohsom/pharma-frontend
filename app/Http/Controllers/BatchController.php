<?php

namespace App\Http\Controllers;

use App\Services\BatchApiService;
use App\Services\OrderApiService;
use Illuminate\Http\Request;
use Exception;

class BatchController extends Controller
{
    protected $batchApiService;
    protected $orderApiService;

    public function __construct(BatchApiService $batchApiService, OrderApiService $orderApiService)
    {
        $this->batchApiService = $batchApiService;
        $this->orderApiService = $orderApiService;
    }

    public function create($orderId)
    {
        try {
            $order = $this->orderApiService->getOrder($orderId);
            
            if ($order->status !== 'BATCHED') {
                return redirect()->back()->with('error', 'Order must be in BATCHED status to create a batch');
            }

            return view('batches.create', compact('order'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'address' => 'required|string',
                'notes' => 'nullable|string',
                'requirements' => 'required|array|min:1',
                'requirements.*.type' => 'required|in:temperature,humidity,pressure',
                'requirements.*.unit' => 'required|string',
                'requirements.*.min_value' => 'required|numeric',
                'requirements.*.max_value' => 'required|numeric|gt:requirements.*.min_value',
            ]);

            $batch = $this->batchApiService->createBatch($validated);
            return redirect()->route('batches.show', $batch->id)->with('success', 'Batch created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $batch = $this->batchApiService->getBatch($id);
            return view('batches.show', compact('batch'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}