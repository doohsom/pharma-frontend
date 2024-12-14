<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class OrderApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:4040';
    }

    public function getAllOrders()
    {
        try {
            $response = Http::get("{$this->baseUrl}/orders");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching orders: " . $e->getMessage());
        }
    }

    public function getOrder($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/orders/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching order: " . $e->getMessage());
        }
    }

    public function createOrder($data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/orders", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error creating order: " . $e->getMessage());
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