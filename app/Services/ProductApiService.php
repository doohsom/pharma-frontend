<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ProductApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
    }

    public function getAllProducts()
    {
        try {
            $response = Http::get("{$this->baseUrl}/products");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching products: " . $e->getMessage());
        }
    }

    public function getProduct($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/products/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching product: " . $e->getMessage());
        }
    }

    public function createProducts($data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/products", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error creating product: " . $e->getMessage());
        }
    }

    public function createProduct(array $data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/products", $data);
            if ($response->getStatusCode() !== 201) {
                throw new Exception('Unexpected response from blockchain API');
            }

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            logger()->error('Blockchain API request failed:', [
                'error' => $e->getMessage(),
                'request_data' => $data
            ]);
            throw new Exception('Failed to communicate with blockchain API: ' . $e->getMessage());
        }
    }

    public function updateProductStatus($productId, $status, $notes)
    {
        try {
            $response = Http::put("{$this->baseUrl}/products/{$productId}/status", [
                'json' => [
                    'status' => $status,
                    'notes' => $notes
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to update product status');
            }

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw new Exception('Failed to communicate with blockchain API: ' . $e->getMessage());
        }
    }

    public function updateProduct($id, $data)
    {
        try {
            $response = Http::put("{$this->baseUrl}/products/{$id}", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error updating product: " . $e->getMessage());
        }
    }

    public function deleteProduct($id)
    {
        try {
            $response = Http::delete("{$this->baseUrl}/products/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error deleting product: " . $e->getMessage());
        }
    }
}
