<?php

namespace Workdo\Account\Entities;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerCreditNotes extends Model
{
    use HasFactory;


    protected $fillable = [
        'invoice',
        'customer',
        'amount',
        'date',
    ];


    public function custom_customer()
    {
        return $this->hasOne(\Workdo\Account\Entities\Customer::class, 'id', 'customer');
    }

    public function invoices()
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice');
    }

    public static $statues = [
        'Pending',
        'Partially Used',
        'Fully Used',
    ];
}

