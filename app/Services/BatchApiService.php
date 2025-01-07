<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BatchApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
    }

    public function getAllBatches(): Collection
    {
        try {
            $response = Http::get("{$this->baseUrl}/batches");
            
            if (!$response->successful()) {
                Log::error('Failed to fetch batches', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return collect([]);
            }

            return collect($response->json())->map(function ($batch) {
                return $this->formatBatchData($batch);
            });

        } catch (Exception $e) {
            Log::error('Error fetching batches', [
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    public function createBatch(array $data)
    {
        try {
            // Generate batch ID
            $batchId = 'BATCH_' . strtoupper(Str::random(8));

            // Structure request data with function name and arguments
            $requestData = [
                'fn' => 'CreateBatch',  // Add function name
                'args' => [             // Arguments in order expected by the contract
                    $batchId,                       // id
                    $data['order_id'],             // orderID
                    $data['logistics_id'],         // logisticsProviderID
                    $data['notes'] ?? ''           // notes
                ]
            ];

            Log::info('Creating batch:', [
                'request_data' => $requestData,
                'raw_data' => $data
            ]);

            $response = Http::post("{$this->baseUrl}/batches", $requestData);

            Log::info('Batch creation response:', [
                'status' => $response->status(),
                'body' => $response->body(), // Log full response body
                'json' => $response->json()
            ]);

            if (!$response->successful()) {
                $errorResponse = $response->json();
                $errorMessage = $errorResponse['error'] ?? 'Unknown error';
                
                Log::error('Failed to create batch:', [
                    'status_code' => $response->status(),
                    'error_message' => $errorMessage,
                    'request_data' => $requestData
                ]);

                // Handle specific errors
                if (str_contains($errorMessage, 'logistics provider')) {
                    throw new Exception('Invalid logistics provider. Please verify the selection.');
                }
                if (str_contains($errorMessage, 'order must be in')) {
                    throw new Exception('Order is not in the correct state for batch creation.');
                }
                if (str_contains($errorMessage, 'already exists')) {
                    throw new Exception('A batch with this ID already exists. Please try again.');
                }

                throw new Exception($errorMessage);
            }

            return [
                'id' => $batchId,
                'order_id' => $data['order_id'],
                'logistics_id' => $data['logistics_id'],
                'notes' => $data['notes'] ?? '',
                'status' => 'created'
            ];

        } catch (Exception $e) {
            Log::error('Error creating batch:', [
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

     /**
     * Update batch status
     */
    public function updateBatchStatus(string $id, string $status): ?array
    {
        try {
            $response = Http::put("{$this->baseUrl}/batches/{$id}/status", [
                'status' => $status
            ]);
            
            
            if (!$response->successful()) {
                Log::error('Failed to update batch status', [
                    'batch_id' => $id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            return $this->formatBatchData($response->json());

        } catch (Exception $e) {
            Log::error('Error updating batch status', [
                'batch_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get specific batch by ID
     */
    public function getBatchById(string $id): ?array
    {
        try {
            $response = Http::get("{$this->baseUrl}/batches/{$id}");
            
            if (!$response->successful()) {
                Log::error('Failed to fetch batch', [
                    'batch_id' => $id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            return $this->formatBatchData($response->json());

        } catch (Exception $e) {
            Log::error('Error fetching batch', [
                'batch_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getBatch($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/batches/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching batch: " . $e->getMessage());
        }
    }

    public function updateBatch($id, $data)
    {
        try {
            $response = Http::put("{$this->baseUrl}/batches/{$id}", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error updating batch: " . $e->getMessage());
        }
    }

    /**
     * Format batch data for consistency
     */
    protected function formatBatchData(array $batch): array
    {
        return [
            'id' => $batch['id'],
            'orderId' => $batch['orderId'],
            'productId' => $batch['productId'],
            'logisticsProviderId' => $batch['logisticsProviderId'],
            'status' => $batch['status'],
            'notes' => $batch['notes'] ?? '',
            'createdAt' => $batch['createdAt'],
            'updatedAt' => $batch['updatedAt'],
            'order' => [
                'id' => $batch['order']['id'],
                'productId' => $batch['order']['productId'],
                'receiverId' => $batch['order']['receiverId'],
                'senderId' => $batch['order']['senderId'],
                'status' => $batch['order']['status'],
                'notes' => $batch['order']['notes'] ?? '',
                'quantity' => $batch['order']['quantity'],
                'createdAt' => $batch['order']['createdAt']
            ],
            'product' => [
                'id' => $batch['product']['id'],
                'name' => $batch['product']['name'],
                'description' => $batch['product']['description'],
                'userId' => $batch['product']['userId'],
                'manufactureDate' => $batch['product']['manufactureDate'],
                'expiryDate' => $batch['product']['expiryDate'],
                'price' => $batch['product']['price'],
                'status' => $batch['product']['status'],
                'approvedBy' => $batch['product']['approvedBy'],
                'approvalDate' => $batch['product']['approvalDate'],
                'requirements' => $batch['product']['requirements'],
                'notes' => $batch['product']['notes'] ?? ''
            ]
        ];
    }
}