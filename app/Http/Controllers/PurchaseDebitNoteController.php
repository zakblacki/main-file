<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use App\Models\PurchaseDebitNote;
use Workdo\Account\Entities\Vender;
use App\Traits\CreditDebitNoteBalance;

class PurchaseDebitNoteController extends Controller
{
    use CreditDebitNoteBalance;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('purchases.index');

}

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($purchase_id)
    {
        if(\Auth::user()->isAbleTo('purchase debitnote create'))
        {
            $purchaseDue = Purchase::where('id', $purchase_id)->first();
            $vendor      = Vender::where('user_id', $purchaseDue->vender_id)->first();
            return view('debitNote.create', compact('vendor', 'purchase_id'));
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
    public function store(Request $request, $purchase_id)
    {
        if(Auth::user()->isAbleTo('purchase debitnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required|numeric|gt:0',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $purchaseDue = Purchase::where('id', $purchase_id)->first();
            $vendor  = Vender::where('user_id', $purchaseDue->user_id)->first();

            if($request->amount > $vendor->debit_note_balance)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($vendor->debit_note_balance) . ' debit limit of this bill.');
            }

            if($request->amount > $purchaseDue->getDue())
            {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($purchaseDue->getDue()) . ' credit limit of this purchase.');
            }

            if(($purchaseDue->getDue() - $request->amount) <= 0)
            {
                $purchaseDue->status = 4;
                $purchaseDue->save();
            } else {
                $purchaseDue->status = 3;
                $purchaseDue->save();
            }

            $debit              = new PurchaseDebitNote();
            $debit->purchase    = $purchase_id;
            $debit->vendor      = $vendor->id;
            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();

            // store debitnote balance vendor's table
            $this->updateBalance('vender', $vendor->id, $request->amount, 'debit');

            return redirect()->back()->with('success', __('The debit note has been created successfully'));
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
        return redirect()->back()->with('error', __('Permission denied.'));
        return view('pos::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($purchase_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('purchase debitnote edit'))
        {
            $debitNote = PurchaseDebitNote::find($debitNote_id);

            return view('debitNote.edit', compact('debitNote'));
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
    public function update(Request $request, $purchase_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('purchase debitnote edit'))
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
            $purchaseDue = Purchase::where('id', $purchase_id)->first();

            $debit = PurchaseDebitNote::find($debitNote_id);

            $vendor  = Vender::where('user_id', $purchaseDue->user_id)->first();

            if($request->amount > $vendor->debit_note_balance + $debit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($vendor->debit_note_balance + $debit->amount) . ' debit limit of this purchase.');
            }

            if($request->amount > $purchaseDue->getDue() + $debit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($purchaseDue->getDue() + $debit->amount) . ' credit limit of this purchase.');
            }

            if(($purchaseDue->getDue() + $debit->amount) - $request->amount <= 0)
            {
                $purchaseDue->status = 4;
                $purchaseDue->save();
            } else {
                $purchaseDue->status = 3;
                $purchaseDue->save();
            }


            // store creditnote customer's table
            $this->updateBalance('vender', $vendor->id, $debit->amount, 'credit');

            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();

            // store debitnote balance vendor's table
            $this->updateBalance('vender', $vendor->id, $request->amount, 'debit');

            return redirect()->back()->with('success', __('The debit note details are updated successfully'));
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
    public function destroy($purchase_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('purchase debitnote delete'))
        {
            $debitNote = PurchaseDebitNote::find($debitNote_id);
            $purchase = Purchase::find($debitNote->purchase);
            if($purchase)
            {
                $billDue = $purchase->getDue() + $debitNote->amount;
                $total = $purchase->getTotal();

                if ( $billDue > 0 && $billDue != $total) {
                    $purchase->status = 3;
                } elseif($billDue == $total) {
                    $purchase->status = 2;
                }
                $purchase->save();

                // store debitnote balance vendor's table
                $this->updateBalance('vender', $debitNote->vendor, $debitNote->amount, 'credit');

                $debitNote->delete();

                return redirect()->back()->with('success', __('The debit note has been deleted.'));

            }
            return redirect()->back()->with('error', __('debit note not found!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

