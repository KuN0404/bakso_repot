<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'invoice_number',
        'midtrans_order_id',
        'qr_code_url',
        'payment_method',
        'amount',
        'status',
        'midtrans_response',
        'webhook_received_at',
        'paid_at',
        'expired_at',
        'idempotency_key',
        'created_by',
        'source',
        'self_order_id',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'midtrans_response'   => 'array',
        'webhook_received_at' => 'datetime',
        'paid_at'             => 'datetime',
        'expired_at'          => 'datetime',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'initiated'  => 'Diinisiasi',
            'pending'    => 'Menunggu',
            'paid'       => 'Dibayar',
            'failed'     => 'Gagal',
            'expired'    => 'Kadaluarsa',
            'cancelled'  => 'Dibatalkan',
            default      => $this->status ?? '-',
        };
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
