<?php
// app/Services/UserApiService.php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UserApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://localhost:5050/api';
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

    public function getUser($id)
    {
        try {
            Log::info('Fetching user details:', ['user_id' => $id]);
            
            $response = Http::get("{$this->baseUrl}/users/{$id}");
            
            if (!$response->successful()) {
                Log::error('Failed to fetch user:', [
                    'user_id' => $id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }

            $user = $response->json();
            Log::info('User details fetched:', [
                'user_id' => $id,
                'role' => $user['role'] ?? 'unknown',
                'status' => $user['status'] ?? 'unknown'
            ]);

            return $user;

        } catch (Exception $e) {
            Log::error('Error fetching user:', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getUsersByRole($role)
    {
        try {
            Log::info('Fetching users by role:', ['role' => $role]);
            
            $response = Http::get("{$this->baseUrl}/users");
            
            if (!$response->successful()) {
                Log::error('Failed to fetch users:', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return [];
            }

            $users = $response->json();
            
            // Filter users by role
            $filteredUsers = array_filter($users, function($user) use ($role) {
                return isset($user['role']) && $user['role'] === $role;
            });

            Log::info('Filtered users by role:', [
                'role' => $role,
                'total_users' => count($users),
                'filtered_count' => count($filteredUsers)
            ]);

            return array_values($filteredUsers);

        } catch (Exception $e) {
            Log::error('Error fetching users by role:', [
                'role' => $role,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}