<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\DebitNote;
use Workdo\Account\Entities\Vender;
use App\Traits\CreditDebitNoteBalance;

class DebitNoteController extends Controller
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
    public function create($bill_id)
    {
        if (Auth::user()->isAbleTo('debitnote create')) {
            $billDue = Bill::where('id', $bill_id)->first();
            $vendor  = Vender::where('user_id', $billDue->user_id)->first();

            return view('account::debitNote.create', compact('billDue','vendor','bill_id'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $bill_id)
    {
        if (Auth::user()->isAbleTo('debitnote create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required|date_format:Y-m-d',
                    'amount' => 'required|numeric|gt:0',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $billDue = Bill::where('id', $bill_id)->first();
            $vendor  = Vender::where('user_id', $billDue->user_id)->first();

            if($request->amount > $vendor->debit_note_balance)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($vendor->debit_note_balance) . ' debit limit of this bill.');
            }

            if($request->amount > $billDue->getDue())
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($billDue->getDue()) . ' debit limit of this bill.');
            }

            if(($billDue->getDue() - $request->amount) <= 0)
            {
                $billDue->status = 4;
                $billDue->save();
            } else {
                $billDue->status = 3;
                $billDue->save();
            }

            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $vendor->id;
            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = isset($request->description) ? $request->description : '--';
            $debit->save();

            // store debitnote balance vendor's table
            $this->updateBalance('vendor', $vendor->id, $request->amount, 'debit');

            return redirect()->back()->with('success', __('The debit note has been created successfully.'));
        } else {
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
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($bill_id, $debitNote_id)
    {
        if (Auth::user()->isAbleTo('debitnote edit')) {
            $debitNote = DebitNote::find($debitNote_id);

            return view('account::debitNote.edit', compact('debitNote'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $bill_id, $debitNote_id)
    {
        if (Auth::user()->isAbleTo('debitnote edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric|gte:0',
                    'date' => 'required|date_format:Y-m-d',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $billDue = Bill::where('id', $bill_id)->first();

            $debit   = DebitNote::find($debitNote_id);

            $vendor  = Vender::where('user_id', $billDue->user_id)->first();

            if($request->amount > $vendor->debit_note_balance + $debit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($vendor->debit_note_balance + $debit->amount) . ' debit limit of this bill.');
            }

            if($request->amount > $billDue->getDue() + $debit->amount)
            {
                return redirect()->back()->with('error', 'Maximum ' .currency_format_with_sym($billDue->getDue() + $debit->amount) . ' debit limit of this bill.');
            }

            if(($billDue->getDue() + $debit->amount) - $request->amount <= 0)
            {
                $billDue->status = 4;
                $billDue->save();
            } else {
                $billDue->status = 3;
                $billDue->save();
            }

            // store creditnote customer's table
            $this->updateBalance('vendor', $vendor->id, $debit->amount, 'credit');

            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->save();

            $this->updateBalance('vendor', $vendor->id, $request->amount, 'debit');



            return redirect()->back()->with('success', __('The debit note details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($bill_id, $debitNote_id)
    {
        if (Auth::user()->isAbleTo('debitnote delete'))
        {
            $debitNote = DebitNote::find($debitNote_id);
            $bill = Bill::find($debitNote->bill);
            if($bill)
            {
                $billDue = $bill->getDue() + $debitNote->amount;
                $total = $bill->getTotal();

                if ( $billDue > 0 && $billDue != $total) {
                    $bill->status = 3;
                } elseif($billDue == $total) {
                    $bill->status = 2;
                }
                $bill->save();

                // store debitnote balance vendor's table
                $this->updateBalance('vendor', $debitNote->vendor, $debitNote->amount, 'credit');

                $debitNote->delete();

                return redirect()->back()->with('success', __('The debit note has been deleted.'));
            }
            return redirect()->back()->with('error', __('Bill not found!'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
