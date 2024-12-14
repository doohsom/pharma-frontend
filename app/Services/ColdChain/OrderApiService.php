<?php

namespace App\Services\Coldchain;
use Illuminate\Support\Facades\Http;
use Exception;

class OrderApiService
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }

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