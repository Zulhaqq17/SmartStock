<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Use these constants everywhere instead of typing the strings.
    public const MANAGER = 'Manager';
    public const WAREHOUSE = 'Warehouse Staff';
    public const PRODUCTION = 'Production Worker';

    protected $fillable = ['role_name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
