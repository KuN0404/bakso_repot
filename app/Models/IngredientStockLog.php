<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStockLog extends Model
{
    protected $fillable = [
        'ingredient_id',
        'user_id',
        'type',
        'amount',
        'final_stock',
        'note',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'final_stock' => 'float',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
