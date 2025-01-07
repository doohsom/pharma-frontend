<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Services\UserApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserApiServiceJson;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userApiService;

    public function __construct()
    {
        $this->userApiService = new UserApiService();
    }

    public function createUser(array $data)
    {
        DB::beginTransaction();
    
        try {
            // Generate blockchain ID
            $data['blockchain_id'] = 'USER_' . uniqid();
            $data['password'] = Hash::make($data['password']);
    
            // Create user in database
            $user = User::create($data);
            Log::info('User created in database: ', ['user' => $user->toArray()]);
    
            // Try to sync with blockchain (idempotent)
            try {
                $blockchainData = $user->toBlockchainFormat();
                $blockchainResponse = $this->userApiService->createUser($blockchainData);
                Log::info('Blockchain sync successful: ', ['response' => $blockchainResponse]);
    
            } catch (Exception $e) {
                // Log blockchain sync error but don't fail the database transaction
                Log::error('Blockchain sync failed: ', [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);
    
                // Consider retrying the blockchain operation here (with exponential backoff)
            }
    
            DB::commit();
            return $user;
    
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Database transaction failed: ', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getUser($id)
    {
        $user = User::where('id', $id)
                    ->orWhere('blockchain_id', $id)
                    ->firstOrFail();

        // If not synced with blockchain, try to sync again
        if (!$user) {
            try {
                $blockchainData = $user->toBlockchainFormat();
                $this->userApiService->createUser($blockchainData);
                
                $user->save();
            } catch (Exception $e) {
                Log::error('Blockchain re-sync failed for user: ' . $user->id, [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);
            }
        }

        return $user;
    }

    public function getAllUsers() 
    {
        return $this->userApiService->getAllUsers();
    }

    public function updateUser($id, array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $id)
                    ->orWhere('blockchain_id', $id)
                    ->firstOrFail();

            // Update user data
            $user->fill($data);
            $user->save();
            Log::info('saved in db');
            // Try to sync with blockchain
            try {
                $blockchainData = $user->toBlockchainFormat();
                Log::info('blockchain data', $blockchainData);
                $blockchainResponse = $this->userApiService->updateUser($user->blockchain_id, $blockchainData);
                Log::info('blockchain response', $blockchainResponse);

                if (!$blockchainResponse) {
                    throw new Exception('Blockchain sync failed');
                }

                DB::commit();
                return $user;
            } catch (Exception $e) {
                // Log blockchain sync failure but commit DB changes
                Log::error('Blockchain sync failed during update for user: ' . $user->id, [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);

                DB::commit();
                return [
                    'user' => $user,
                    'blockchain_sync_failed' => true
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function syncUnsynced()
    {
        $unsynced = User::where('api_synced', false)->get();
        
        foreach ($unsynced as $user) {
            try {
                $blockchainData = $user->toBlockchainFormat();
                $this->userApiService->createUser($blockchainData);
                
                $user->api_synced = true;
                $user->api_sync_error = null;
                $user->save();

                Log::info('Successfully synced user: ' . $user->id);
            } catch (Exception $e) {
                Log::error('Failed to sync user: ' . $user->id, [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);
            }
        }
    }
}
