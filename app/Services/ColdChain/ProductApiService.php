<?php

namespace App\Services\Coldchain;
use Illuminate\Support\Facades\Http;
use Exception;

class ProductApiService
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }

    public function createProduct(array $productData)
    {
        $products = $this->readJsonFile('products.json');
        
        if (isset($products[$productData['id']])) {
            throw new \Exception('Product already exists');
        }

        $productData['createdAt'] = now()->toISOString();
        $productData['status'] = 'pending';
        $productData['approvedBy'] = null;
        $productData['approvalDate'] = null;
        
        $products[$productData['id']] = $productData;
        $this->writeJsonFile('products.json', $products);

        return $productData;
    }

    public function getProduct(string $id)
    {
        $products = $this->readJsonFile('products.json');
        
        if (!isset($products[$id])) {
            throw new \Exception('Product not found');
        }

        return $products[$id];
    }

    public function getAllProducts()
    {
        return array_values($this->readJsonFile('products.json'));
    }

    public function approveProduct(string $id, string $regulatorId)
    {
        $products = $this->readJsonFile('products.json');
        
        if (!isset($products[$id])) {
            throw new \Exception('Product not found');
        }

        $products[$id]['status'] = 'approved';
        $products[$id]['approvedBy'] = $regulatorId;
        $products[$id]['approvalDate'] = now()->toISOString();

        $this->writeJsonFile('products.json', $products);
        return $products[$id];
    }

    private function readJsonFile(string $filename)
    {
        $filepath = $this->mockDataPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }

        $content = file_get_contents($filepath);
        return json_decode($content, true) ?? [];
    }

    private function writeJsonFile(string $filename, array $data)
    {
        if (!file_exists($this->mockDataPath)) {
            mkdir($this->mockDataPath, 0755, true);
        }

        $filepath = $this->mockDataPath . '/' . $filename;
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
