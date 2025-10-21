<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'registered_at',
    ];
    
    /**
    * The attributes that should be cast.
    *
    * @var array
    */
    protected function casts(): array
    {
        return [
        'registered_at' => 'datetime',
        ];
    }

}