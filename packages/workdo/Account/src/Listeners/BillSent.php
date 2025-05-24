<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\BillAccount;
use Workdo\Account\Entities\BillProduct;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Events\SentBill;
use Workdo\ProductService\Entities\ProductService;

class BillSent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SentBill $event)
    {

        $bill = $event->bill;


        // for chart of accounts data save

        //save for bill product data
        $bill_products = BillProduct::where('bill_id', $bill->id)->get();
        foreach ($bill_products as $bill_product) {
            $product = ProductService::find($bill_product->product_id);
            $totalTaxPrice = 0;
            $taxes = AccountUtility::tax($bill_product->tax);
            foreach ($taxes as $tax) {
                $taxPrice = AccountUtility::taxRate($tax['rate'], $bill_product->price, $bill_product->quantity, $bill_product->discount);
                $totalTaxPrice += $taxPrice;
            }
            $itemAmount = ($bill_product->price * $bill_product->quantity) - ($bill_product->discount) + $totalTaxPrice;
            $data1 = [
                'account_id' => $product->expense_chartaccount_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $itemAmount,
                'reference' => 'Bill',
                'reference_id' => $bill->id,
                'reference_sub_id' => $product->id,
                'date' => $bill->bill_date,
            ];
            AccountUtility::addTransactionLines($data1);


        }
        //save for bill account data
        $bill_accounts =BillAccount::where('ref_id',$bill->id)->get();
        foreach ($bill_accounts as $bill_account)
        {
            $data2 = [
                'account_id' => $bill_account->chart_account_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $bill_account->price,
                'reference' => 'Bill Account',
                'reference_id' => $bill->id,
                'reference_sub_id' => $bill_account->id,
                'date' => $bill->bill_date,
            ];

            AccountUtility::addTransactionLines($data2);

        }
    }
}
