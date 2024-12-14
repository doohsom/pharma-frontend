<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class UserApiServiceJson
{
    private $mockDataPath;

    public function __construct()
    {
        $this->mockDataPath = storage_path('app/mock');
    }


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
        $data =  $this->readJsonFile('user.json');
        return $data['users'] ?? [];
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