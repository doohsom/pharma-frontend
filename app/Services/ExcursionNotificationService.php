<?php

// app/Services/ExcursionNotificationService.php
namespace App\Services;

use App\Models\Batch;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ExcursionAlert;
use Illuminate\Support\Facades\Log;

class ExcursionNotificationService
{
    public function notifyExcursion($batchId, $sensorId, $reading)
    {
        try {
            // Get batch and related data
            $batch = Batch::with(['order.product.manufacturer', 'logistics'])->findOrFail($batchId);
            
            $orderDetails = [
                'order_id' => $batch->order_id,
                'product_name' => $batch->order->product->name,
            ];

            // Get manufacturer and logistics contacts
            $manufacturer = $batch->order->product->manufacturer;
            $logistics = $batch->logistics;

            // Send notifications
            if ($manufacturer) {
                $manufacturer->notify(new ExcursionAlert($batchId, $sensorId, $reading, $orderDetails));
            }

            if ($logistics) {
                $logistics->notify(new ExcursionAlert($batchId, $sensorId, $reading, $orderDetails));
            }

            Log::info("Excursion notifications sent for batch {$batchId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send excursion notifications: " . $e->getMessage());
            return false;
        }
    }
}