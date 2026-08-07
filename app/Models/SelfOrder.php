<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelfOrder extends Model
{
    protected $fillable = [
        'order_token',
        'queue_number',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'pickup_name',
        'pickup_phone',
        'subtotal',
        'tax_amount',
        'total',
        'order_type',
        'service_area_id',
        'notes',
        'payment_method',
        'status',
        'payment_transaction_id',
        'transaction_id',
        'shift_id',
        'processed_by',
        'cancelled_by',
        'cancelled_reason',
        'cancelled_at',
        'paid_at',
        'claimed_at',
        'processing_at',
        'completed_at',
        'pickup_confirmed_at',
        'idempotency_key',
        'customer_ip',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'tax_amount'           => 'decimal:2',
        'total'                => 'decimal:2',
        'cancelled_at'         => 'datetime',
        'paid_at'              => 'datetime',
        'claimed_at'           => 'datetime',
        'processing_at'        => 'datetime',
        'completed_at'         => 'datetime',
        'pickup_confirmed_at'  => 'datetime',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------

    public function getQueueDisplayAttribute(): string
    {
        return str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_payment'  => 'Menunggu Pembayaran',
            'waiting_payment'  => 'Menunggu Kasir',
            'paid'             => 'Sudah Dibayar',
            'processing'       => 'Diproses',
            'ready'            => 'Siap Diambil',
            'completed'        => 'Selesai',
            'cancelled'        => 'Dibatalkan',
            'expired'          => 'Kadaluarsa',
            default            => $this->status ?? '-',
        };
    }
}
