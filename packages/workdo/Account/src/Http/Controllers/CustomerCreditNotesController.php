<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\CustomerCreditNotes;
use App\Models\Invoice;
use App\Models\User;
use Workdo\Account\DataTables\CreditNoteDataTable;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Customer;
use App\Traits\CreditDebitNoteBalance;

class CustomerCreditNotesController extends Controller
{
    use CreditDebitNoteBalance;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(CreditNoteDataTable $dataTable)
    {

        if(Auth::user()->isAbleTo('creditnote manage'))
        {
            return $dataTable->render('account::customerCreditNote.index');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function create()
    {
        if(\Auth::user()->isAbleTo('creditnote create'))
        {
            $invoices = Invoice::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('invoice_id', 'id');
            $statues = CustomerCreditNotes :: $statues;
            return view('account::customerCreditNote.create', compact('invoices','statues'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request)
    {
        if(\Auth::user()->isAbleTo('creditnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'invoice' => 'required|numeric',
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $invoice_id = $request->invoice;
            $invoiceDue = Invoice::where('id', $invoice_id)->first();
            $creditAmount = floatval($request->amount);

            if($invoiceDue){

                $invoicePaid = $invoiceDue->getTotal() - $invoiceDue->getDue() - $invoiceDue->invoiceTotalCreditNote();

                $customerCreditNotes = CustomerCreditNotes::where('invoice',$invoice_id)->get()->sum('amount');

                if($creditAmount > $invoicePaid || ($customerCreditNotes + $creditAmount)  > $invoicePaid)
                {
                    return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($invoicePaid-$customerCreditNotes) . ' credit limit of this invoice.');
                }
                $customer = Customer::where('customer_id', '=', $invoiceDue->customer_id)->first();
                if(empty($customer)){
                    $customer = User::find($invoiceDue->customer_id);
                }
                if(!empty($customer))
                {
                    $credit              = new CustomerCreditNotes();
                    $credit->invoice     = $invoice_id;
                    $credit->customer    = $customer->id;
                    $credit->date        = $request->date;
                    $credit->amount      = $creditAmount;
                    $credit->status      = $request->status;
                    $credit->description = $request->description;
                    $credit->save();

                    // store creditnote customer's table
                    $this->updateBalance('customer', $customer->id, $creditAmount, 'credit');

                    return redirect()->route('custom-credit.note')->with('success', __('Credit Note successfully created.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('User is not converted into customer.'));
                }
            }else{
                return redirect()->back()->with('error', __('The invoice field is required.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($invoice_id, $creditNote_id)
    {
        if(\Auth::user()->isAbleTo('creditnote edit'))
        {
            $creditNote = CustomerCreditNotes::find($creditNote_id);
            $statues = CustomerCreditNotes :: $statues;
            return view('account::customerCreditNote.edit', compact('creditNote','statues'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $invoice_id, $creditNote_id)
    {
        if(\Auth::user()->isAbleTo('creditnote edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required|date_format:Y-m-d',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceDue    = Invoice::where('id', $invoice_id)->first();

            $credit        = CustomerCreditNotes::find($creditNote_id);

            $creditAmount  = floatval($request->amount);

            $invoicePaid   = $invoiceDue->getTotal() - $invoiceDue->getDue() - $invoiceDue->invoiceTotalCreditNote();

            $existingCredits = CustomerCreditNotes::where('invoice', $invoice_id)->where('id', '!=', $creditNote_id)->get()->sum('amount');

            if (($existingCredits + $creditAmount) > $invoicePaid) {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($invoicePaid - $existingCredits) . ' credit to this invoice.');
            }

             // store creditnote customer's table
            $this->updateBalance('customer', $invoiceDue->customer_id, $credit->amount, 'debit');

            $credit->date        = $request->date;
            $credit->amount      = $creditAmount;
            $credit->status      = $request->status;
            $credit->description = $request->description;
            $credit->save();

            // store creditnote customer's table
            $this->updateBalance('customer', $invoiceDue->customer_id, $creditAmount, 'credit');

            return redirect()->back()->with('success', __('The credit note details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($invoice_id, $creditNote_id)
    {
        if(\Auth::user()->isAbleTo('creditnote delete'))
        {
            $creditNote = CustomerCreditNotes::find($creditNote_id);
            // store creditnote customer's table
            $this->updateBalance('customer', $creditNote->customer, $creditNote->amount, 'debit');

            $creditNote->delete();

            return redirect()->back()->with('success', __('The credit note has been deleted.'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
