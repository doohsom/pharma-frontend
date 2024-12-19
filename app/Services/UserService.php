<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Services\UserApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserApiServiceJson;

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
            
            // Create user in database
            $user = User::create($data);

            // Try to sync with blockchain
            try {
                $blockchainData = $user->toBlockchainFormat();
                $blockchainResponse = $this->userApiService->createUser($blockchainData);
                
                $user->save();

                DB::commit();
                return $user;
            } catch (Exception $e) {
                // Log blockchain sync error but don't fail the transaction
                
                $user->save();

                Log::error('Blockchain sync failed for user: ' . $user->id, [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);

                DB::commit();
                return $user;
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUser($id)
    {
        $user = User::where('id', $id)
                    ->orWhere('blockchain_id', $id)
                    ->firstOrFail();

        // If not synced with blockchain, try to sync again
        if (!$user->api_synced) {
            try {
                $blockchainData = $user->toBlockchainFormat();
                $this->userApiService->createUser($blockchainData);
                
                $user->api_synced = true;
                $user->api_sync_error = null;
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

            $user->update($data);

            // Try to sync with blockchain
            try {
                $blockchainData = $user->toBlockchainFormat();
                $this->userApiService->updateUser($user->blockchain_id, $blockchainData);
                
                $user->api_synced = true;
                $user->api_sync_error = null;
                $user->save();

                DB::commit();
                return $user;
            } catch (Exception $e) {
                $user->api_synced = false;
                $user->api_sync_error = $e->getMessage();
                $user->save();

                Log::error('Blockchain sync failed during update for user: ' . $user->id, [
                    'error' => $e->getMessage(),
                    'user' => $user->toArray()
                ]);

                DB::commit();
                return $user;
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
