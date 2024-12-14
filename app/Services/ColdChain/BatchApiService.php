<?php

namespace App\Services\Coldchain;
use Illuminate\Support\Facades\Http;
use Exception;

class BatchApiService
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }

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