<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'policy_number',
        'offer_id',
        'client_name',
        'car_plate',
        'insurer_name',
        'provider_code',
        'price',
        'currency',
        'start_date',
        'end_date',
        'installment_count',
        'status',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'start_date'        => 'date',
        'end_date'          => 'date',
        'installment_count' => 'integer',
    ];
}
