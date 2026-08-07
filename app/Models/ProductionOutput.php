<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOutput extends Model
{
    protected $fillable = [
        'production_id',
        'component_id',
        'product_id',
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

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getOutputName(): string
    {
        if ($this->component_id && $this->component) {
            return $this->component->name;
        }
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        return 'Unknown';
    }

    public function getOutputUnit(): string
    {
        if ($this->component_id && $this->component) {
            return $this->component->unit;
        }
        return 'Pcs';
    }
}
