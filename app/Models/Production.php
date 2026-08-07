<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Production extends Model
{
    protected $fillable = [
        'production_code',
        'production_date',
        'total_cost',
        'note',
        'status',
        'user_id',
    ];

    protected $casts = [
        'production_date' => 'date',
        'total_cost'      => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(ProductionInput::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class);
    }
}
