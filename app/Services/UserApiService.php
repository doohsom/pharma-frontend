<?php
// app/Services/UserApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class UserApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050';
    }

    public function getAllUsers()
    {
        try {
            $response = Http::get("{$this->baseUrl}/users");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    public function getUser($id)
    {
        try {
            $response = Http::get("{$this->baseUrl}/users/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }

    public function createUser($data)
    {
        try {
            $response = Http::post("{$this->baseUrl}/users", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error creating user: " . $e->getMessage());
        }
    }

    public function updateUser($id, $data)
    {
        try {
            $response = Http::put("{$this->baseUrl}/users/{$id}", $data);
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    public function deleteUser($id)
    {
        try {
            $response = Http::delete("{$this->baseUrl}/users/{$id}");
            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }
}