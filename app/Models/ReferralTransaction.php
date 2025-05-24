<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'plan_id',
        'plan_price',
        'commission',
        'minimum_threshold_amount',
        'referral_code',
        'status',
        'req_amount',
    ];

}
