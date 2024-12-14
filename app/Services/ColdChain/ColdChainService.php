<?php

namespace App\Services;

class ColdChainService{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }

    // User Management
    public function createUser(array $userData)
    {
        $users = $this->readJsonFile('users.json');
        
        // Check if user already exists
        if (isset($users[$userData['id']])) {
            throw new \Exception('User already exists');
        }

        $userData['dateCreated'] = now()->toISOString();
        $userData['status'] = 'active';
        
        $users[$userData['id']] = $userData;
        $this->writeJsonFile('users.json', $users);

        return $userData;
    }

    public function getUser(string $id)
    {
        $users = $this->readJsonFile('users.json');
        
        if (!isset($users[$id])) {
            throw new \Exception('User not found');
        }

        return $users[$id];
    }

    public function getAllUsers()
    {
        return array_values($this->readJsonFile('users.json'));
    }

    // Product Management
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

    // Order Management
    public function createOrder(array $orderData)
    {
        $orders = $this->readJsonFile('orders.json');
        
        if (isset($orders[$orderData['id']])) {
            throw new \Exception('Order already exists');
        }

        $orderData['createdAt'] = now()->toISOString();
        $orderData['status'] = 'pending';
        
        $orders[$orderData['id']] = $orderData;
        $this->writeJsonFile('orders.json', $orders);

        return $orderData;
    }

    public function getOrder(string $id)
    {
        $orders = $this->readJsonFile('orders.json');
        
        if (!isset($orders[$id])) {
            throw new \Exception('Order not found');
        }

        return $orders[$id];
    }

    public function updateOrderStatus(string $id, string $status)
    {
        $orders = $this->readJsonFile('orders.json');
        
        if (!isset($orders[$id])) {
            throw new \Exception('Order not found');
        }

        $orders[$id]['status'] = $status;
        $this->writeJsonFile('orders.json', $orders);

        return $orders[$id];
    }

    public function getOrdersByStatus(string $status)
    {
        $orders = $this->readJsonFile('orders.json');
        return array_values(array_filter($orders, fn($order) => $order['status'] === $status));
    }

    public function getOrdersBySender(string $senderId)
    {
        $orders = $this->readJsonFile('orders.json');
        return array_values(array_filter($orders, fn($order) => $order['senderId'] === $senderId));
    }

    public function getOrdersByReceiver(string $receiverId)
    {
        $orders = $this->readJsonFile('orders.json');
        return array_values(array_filter($orders, fn($order) => $order['receiverId'] === $receiverId));
    }

    // Batch Management
    public function createBatch(array $batchData)
    {
        $batches = $this->readJsonFile('batches.json');
        
        if (isset($batches[$batchData['id']])) {
            throw new \Exception('Batch already exists');
        }

        $batchData['createdAt'] = now()->toISOString();
        $batchData['status'] = 'created';
        $batchData['previousCustodian'] = $batchData['custodian'];
        
        $batches[$batchData['id']] = $batchData;
        $this->writeJsonFile('batches.json', $batches);

        return $batchData;
    }

    public function getBatch(string $id)
    {
        $batches = $this->readJsonFile('batches.json');
        
        if (!isset($batches[$id])) {
            throw new \Exception('Batch not found');
        }

        return $batches[$id];
    }

    public function transferBatchCustody(string $id, string $newCustodian)
    {
        $batches = $this->readJsonFile('batches.json');
        
        if (!isset($batches[$id])) {
            throw new \Exception('Batch not found');
        }

        $batches[$id]['previousCustodian'] = $batches[$id]['custodian'];
        $batches[$id]['custodian'] = $newCustodian;
        $batches[$id]['updatedAt'] = now()->toISOString();

        $this->writeJsonFile('batches.json', $batches);
        return $batches[$id];
    }

    // Sensor Reading Management
    public function createSensorReading(array $readingData)
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        
        if (isset($readings[$readingData['id']])) {
            throw new \Exception('Sensor reading already exists');
        }

        $readingData['timestamp'] = now()->toISOString();
        $readingData['hasExcursion'] = false; // This should be calculated based on product requirements
        
        $readings[$readingData['id']] = $readingData;
        $this->writeJsonFile('sensor_readings.json', $readings);

        return $readingData;
    }

    public function getReadingsByBatch(string $batchId)
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        return array_values(array_filter($readings, fn($reading) => $reading['batchId'] === $batchId));
    }

    public function getExcursionReadings()
    {
        $readings = $this->readJsonFile('sensor_readings.json');
        return array_values(array_filter($readings, fn($reading) => $reading['hasExcursion']));
    }

    // Helper methods for JSON file handling
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