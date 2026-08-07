<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentStockLog extends Model
{
    protected $fillable = [
        'component_id',
        'user_id',
        'type',
        'amount',
        'final_stock',
        'note',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'amount'      => 'float',
        'final_stock' => 'float',
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
