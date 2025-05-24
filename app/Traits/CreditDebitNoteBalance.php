<?php

namespace App\Traits;

use Workdo\Account\Entities\Customer;
use Workdo\Account\Entities\Vender;

trait CreditDebitNoteBalance
{
    public function  updateBalance($users, $id, $amount, $type)
    {
        if ($users == 'customer') {
            $customer = Customer::find($id);
                if(!empty($customer)) {
                    if ($type == 'debit') {
                        $oldBalance = $customer->credit_note_balance;
                        $userBalance = $oldBalance - $amount;
                        $customer->credit_note_balance = $userBalance;
                        $customer->save();
                    } elseif ($type == 'credit') {
                        $oldBalance = $customer->credit_note_balance;
                        $userBalance = $oldBalance + $amount;
                        $customer->credit_note_balance = $userBalance;
                        $customer->save();
                    }
                }
        } else {
            $vendor = Vender::find($id);
            if(!empty($vendor)){
                if ($type == 'debit') {
                    $oldBalance = $vendor->debit_note_balance;
                    $userBalance = $oldBalance - $amount;
                    $vendor->debit_note_balance = $userBalance;
                    $vendor->save();
                } elseif ($type == 'credit') {
                    $oldBalance = $vendor->debit_note_balance;
                    $userBalance = $oldBalance + $amount;
                    $vendor->debit_note_balance = $userBalance;
                    $vendor->save();
                }
            }
        }
    }

}
