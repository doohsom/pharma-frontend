<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class BatchApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:4040';
    }

    public function createBatch($data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/batches", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error creating batch: " . $e->getMessage());
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
}