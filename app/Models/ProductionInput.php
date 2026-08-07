<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionInput extends Model
{
    protected $fillable = [
        'production_id',
        'ingredient_id',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'quantity'  => 'float',
        'unit_cost' => 'float',
        'subtotal'  => 'float',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
