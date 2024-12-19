<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'blockchain_id',
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'address',
        'status',
        'created_by',
        'api_synced',
        'api_sync_error'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Helper method to format data for blockchain
    public function toBlockchainFormat()
    {
        return [
            'id' => $this->blockchain_id,
            'name' => $this->name,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'role' => $this->role,
            'address' => $this->address,
            'createdBy' => $this->created_by,
            'status' => $this->status
        ];
    }
}