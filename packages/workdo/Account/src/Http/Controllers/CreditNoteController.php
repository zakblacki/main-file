<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\CreditNote;
use App\Models\Invoice;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Customer;
use App\Traits\CreditDebitNoteBalance;

class CreditNoteController extends Controller
{
    use CreditDebitNoteBalance;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('account::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($invoice_id)
    {
        if(Auth::user()->isAbleTo('creditnote create'))
        {
            $invoiceDue  = Invoice::where('id', $invoice_id)->first();
            $customer    = Customer::where('user_id', $invoiceDue->user_id)->first();
            return view('account::creditNote.create', compact('customer', 'invoice_id'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $invoice_id)
    {
        if(Auth::user()->isAbleTo('creditnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceDue = Invoice::where('id', $invoice_id)->first();
            $customer = Customer::where('user_id', $invoiceDue->user_id)->first();

            if($request->amount > $customer->credit_note_balance)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($customer->credit_note_balance) . ' credit limit of this invoice.');
            }

            if($request->amount > $invoiceDue->getDue())
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($invoiceDue->getDue()) . ' credit limit of this invoice.');
            }


            $credit              = new CreditNote();
            $credit->invoice     = $invoice_id;
            $credit->customer    = $customer->id;
            $credit->date        = $request->date;
            $credit->amount      = $request->amount;
            $credit->description = isset($request->description) ? $request->description : '--';
            $credit->save();

            if($invoiceDue->getDue() <= 0)
            {
                $invoiceDue->status = 4;
                $invoiceDue->save();
            } else {
                $invoiceDue->status = 3;
                $invoiceDue->save();
            }

            // store creditnote customer's table
            $this->updateBalance('customer', $customer->id, $credit->amount, 'debit');

            return redirect()->back()->with('success', __('The credit note has been created successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($invoice_id, $creditNote_id)
    {
        if(Auth::user()->isAbleTo('creditnote edit'))
        {
            $creditNote = CreditNote::find($creditNote_id);

            return view('account::creditNote.edit', compact('creditNote'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
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
        if(Auth::user()->isAbleTo('creditnote edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $invoiceDue = Invoice::where('id', $invoice_id)->first();

            $credit = CreditNote::find($creditNote_id);
            $customer = Customer::where('user_id', $invoiceDue->user_id)->first();

            if($request->amount > $customer->credit_note_balance + $credit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($customer->credit_note_balance + $credit->amount) . ' credit limit of this invoice.');
            }

            if($request->amount > $invoiceDue->getDue() + $credit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($invoiceDue->getDue() + $credit->amount ) . ' credit limit of this invoice.');
            }

            if(($invoiceDue->getDue() + $credit->amount ) - $request->amount <= 0)
            {
                $invoiceDue->status = 4;
                $invoiceDue->save();
            } else {
                $invoiceDue->status = 3;
                $invoiceDue->save();
            }

            // store creditnote customer's table
            $this->updateBalance('customer', $customer->id, $credit->amount, 'credit');

            $credit->date        = $request->date;
            $credit->amount      = $request->amount;
            $credit->description = $request->description;
            $credit->save();

            $this->updateBalance('customer', $customer->id, $request->amount, 'debit');

            return redirect()->back()->with('success', __('The credit note details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($invoice_id, $creditNote_id)
    {
        if(Auth::user()->isAbleTo('creditnote delete'))
        {
            $creditNote = CreditNote::find($creditNote_id);
            if($creditNote)
            {
                $invoice = Invoice::find($creditNote->invoice);
                $invoiceDue = $invoice->getDue() + $creditNote->amount;
                $total   = $invoice->getTotal();

                if ( $invoiceDue > 0 && $invoiceDue != $total) {
                    $invoice->status = 3;
                } elseif($invoiceDue == $total) {
                    $invoice->status = 2;
                }
                $invoice->save();

                $this->updateBalance('customer', $creditNote->customer, $creditNote->amount, 'credit');
                $creditNote->delete();

                return redirect()->back()->with('success', __('The credit note has been deleted.'));
            }
            return redirect()->back()->with('error', __('Credit note not found!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
