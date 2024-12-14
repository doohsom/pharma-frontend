<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ProductApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:4040';
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

    public function createProduct($data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/products", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error creating product: " . $e->getMessage());
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
