<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'role_id',
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
    
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

}