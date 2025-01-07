<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrderApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
    }

   
    public function getAllOrders()
    {
        try {
            Log::info('Fetching all orders from API');
            $response = Http::get("{$this->baseUrl}/orders");
    
            if (!$response->successful()) {
                Log::error('Failed to fetch orders from API:', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('Failed to fetch orders from API');
            }
    
            $orders = $response->json();
            Log::info('Successfully fetched orders:', [
                'count' => count($orders),
                'sample' => array_slice($orders, 0, 2) // Log first two orders for debugging
            ]);
    
            return $orders;
        } catch (Exception $e) {
            Log::error('Error in getAllOrders:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getOrder($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/orders/{$id}");
            Log::info($response);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching order: " . $e->getMessage());
        }
    }

    public function createOrder(array $data)
    {
        try {
            // Generate order ID with specific format
            $orderId = 'ORDER_' . strtoupper(Str::random(8));

            // Format request data exactly as expected by the API/smart contract
            $requestData = [
                'ID' => $orderId,
                'ProductID' => $data['product_id'],
                'ReceiverID' => 'USER_6773bf9212bc5',
                'SenderID' => $data['sender_id'],
                'Notes' => $data['notes'] ?? '',
                'Quantity' => (float) $data['quantity']
            ];

            Log::info('Sending order creation request:', [
                'request_data' => $requestData
            ]);

            $response = Http::post("{$this->baseUrl}/orders", $requestData);

            // Log complete response for debugging
            Log::debug('API Response:', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $errorResponse = $response->json();
                Log::error('Order creation failed:', [
                    'status_code' => $response->status(),
                    'error' => $errorResponse['error'] ?? 'Unknown error',
                    'request_data' => $requestData
                ]);

                $errorMessage = $errorResponse['error'] ?? 'Unknown error';
                
                // Handle specific smart contract errors
                if (str_contains($errorMessage, 'already exists')) {
                    throw new Exception('Order ID already exists. Please try again.');
                }
                if (str_contains($errorMessage, 'does not exist')) {
                    throw new Exception('Invalid product or user reference.');
                }

                throw new Exception($errorMessage);
            }

            // Return a consistent response structure
            return [
                'id' => $orderId,
                'status' => 'pending',
                'product_id' => $data['product_id'],
                'receiver_id' => $data['receiver_id'],
                'sender_id' => $data['sender_id'],
                'quantity' => (float) $data['quantity'],
                'notes' => $data['notes'] ?? '',
                'created_at' => now()->format('Y-m-d\TH:i:s\Z')
            ];

        } catch (\Exception $e) {
            Log::error('API Connection error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Could not connect to blockchain service. Please try again.');
            
        } catch (Exception $e) {
            Log::error('Order creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function updateOrder($id, $data)
    {
        try {
            $response = Http::put("{$this->baseUrl}/orders/{$id}", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error updating order: " . $e->getMessage());
        }
    }

    public function updateOrderStatus($orderId, array $data)
    {
        try {
            Log::info('Updating order status:', [
                'order_id' => $orderId,
                'new_status' => $data['status']
            ]);

            // The API expects just the status, not other data
            $requestData = [
                'status' => $data['status']
            ];

            Log::info('Sending update request to blockchain:', [
                'request_data' => $requestData
            ]);

            $response = Http::put("{$this->baseUrl}/orders/{$orderId}/status", $requestData);

            if (!$response->successful()) {
                Log::error('Failed to update order status:', [
                    'status_code' => $response->status(),
                    'response' => $response->json()
                ]);

                $errorMessage = $response->json()['error'] ?? 'Unknown error';
                
                if (str_contains($errorMessage, 'invalid status')) {
                    throw new Exception('Invalid status provided: ' . $data['status']);
                }
                
                if (str_contains($errorMessage, 'failed to endorse transaction')) {
                    throw new Exception('Failed to validate order update. Please verify the status and try again.');
                }

                throw new Exception($errorMessage);
            }

            // Store notes and updatedBy in your local database if needed
            // ... 

            $result = $response->json();
            Log::info('Order status updated successfully:', [
                'order_id' => $orderId,
                'new_status' => $data['status']
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Error updating order status:', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function deleteOrder($id)
    {
        try {
            $response = Http::delete("{$this->baseUrl}/orders/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error deleting order: " . $e->getMessage());
        }
    }
}