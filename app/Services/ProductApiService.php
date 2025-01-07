<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
        
        if (!$response->successful()) {
            Log::error('Failed to fetch products from API', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            throw new Exception('Failed to fetch products from API: ' . $response->status());
        }
        
        $products = $response->json();
        if (!is_array($products)) {
            Log::error('Invalid products response format', [
                'response' => $products
            ]);
            throw new Exception('Invalid response format from API');
        }

        $user = auth()->user();
        if (!$user) {
            throw new Exception('User not authenticated');
        }

        // Filter products based on user role and blockchain_id
        $filteredProducts = collect($products)->filter(function ($product) use ($user) {
            switch ($user->role) {
                case 'vendor':
                    // Vendors see approved products and their purchased products
                    return $product['status'] === 'approved' || 
                           ($product['purchasedBy'] ?? null) === $user->blockchain_id;
                    
                case 'regulator':
                    // Regulators only see products in for_approval stage
                    return $product['status'] === 'for_approval';
                    
                case 'manufacturer':
                    // Manufacturers see their own products (compare blockchain_id)
                    return $product['userId'] === $user->blockchain_id;
                    
                case 'logistics':
                    // Logistics see products in transit or assigned to them
                    return ($product['status'] === 'in_transit' && 
                           ($product['assignedTo'] ?? null) === $user->blockchain_id);
                    
                case 'admin':
                    // Admins see everything
                    return true;
                    
                default:
                    return false;
            }
        });

        Log::info('Products fetched and filtered successfully', [
            'role' => $user->role,
            'total_count' => count($products),
            'filtered_count' => $filteredProducts->count()
        ]);

        return $filteredProducts->values()->all();

    } catch (Exception $e) {
        Log::error('Error in getAllProducts:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
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

    public function getProductById($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/products/{$id}");
            if (!$response->successful()) {
                Log::error('Failed to fetch products from API', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new Exception('Failed to fetch products from API: ' . $response->status());
            }
            $product = $response->json();
            
            if (!$product) {
                return null;
            }

            $userRole = \Auth::user()->role;
            
            // Check if user has access to this product
            $hasAccess = match($userRole) {
                'vendor' => $product['status'] === 'approved',
                'regulator' => $product['status'] === 'pending',
                'manufacturer' => $product['userId'] === \Auth::id(),
                'admin' => true,
                default => false
            };

            if (!$hasAccess) {
                return null;
            }

            return $product;

        } catch (Exception $e) {
            Log::error('Error fetching product', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);
            
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

    public function updateProductStatus($productId, array $data)
    {
        try {
            Log::info($this->baseUrl.'/products/'.$productId.'/status');
            $response = Http::put("{$this->baseUrl}/products/{$productId}/status", [
                'status' => $data['status'],
                'notes' => $data['notes'],
                'userID' => $data['approvedBy'],
                'approvalDate' => $data['approvalDate']
            ]);

            if (!$response->successful()) {
                Log::error('Product status update failed', [
                    'productId' => $productId,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                throw new Exception('Failed to update product status: ' . 
                    ($response->json()['message'] ?? 'Unknown error'));
            }

            return $response->json();
            
        } catch (Exception $e) {
            Log::error('Product API communication error', [
                'error' => $e->getMessage(),
                'productId' => $productId,
                'trace' => $e->getTraceAsString()
            ]);
            
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
