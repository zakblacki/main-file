<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Account\DataTables\DebitNoteDataTable;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\Vender;
use App\Traits\CreditDebitNoteBalance;

class CustomerDebitNotesController extends Controller
{
    use CreditDebitNoteBalance;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(DebitNoteDataTable $dataTable)
    {
        if(\Auth::user()->isAbleTo('debitnote manage'))
        {

            return $dataTable->render('account::customerDebitNote.index');
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
        if(\Auth::user()->isAbleTo('debitnote create'))
        {
            $bills = Bill::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('bill_id', 'id');

            $statues = CustomerDebitNotes :: $statues;
            return view('account::customerDebitNote.create', compact('bills','statues'));

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
        if(\Auth::user()->isAbleTo('debitnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'bill' => 'required|numeric',
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required|date_format:Y-m-d',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $bill_id = $request->bill;
            $billDue = Bill::where('id', $bill_id)->first();
            $debitAmount = floatval($request->amount);
            if($billDue){

                $billPaid = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();

                $customerDebitNotes = CustomerDebitNotes::where('bill',$bill_id)->get()->sum('amount');

                if($debitAmount > $billPaid || ($customerDebitNotes + $debitAmount)  > $billPaid)
                {
                    return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($billPaid - $customerDebitNotes) . ' credit limit of this bill.');
                }
                $vendor             = Vender::where('user_id',$billDue->user_id)->first();

                if(!empty($vendor))
                {
                    $debit              = new CustomerDebitNotes();
                    $debit->bill        = $bill_id;
                    $debit->vendor      = $vendor->vendor_id;
                    $debit->date        = $request->date;
                    $debit->amount      = $debitAmount;
                    $debit->status      = $request->status;
                    $debit->description = $request->description;
                    $debit->save();

                    // store debitnote balance vendor's table
                    $this->updateBalance('vendor', $vendor->vendor_id, $debitAmount, 'credit');

                    return redirect()->back()->with('success', __('The debit note has been created successfully.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('User is not converted into vendor.'));
                }
            }else{
                return redirect()->back()->with('error', __('The bill field is required.'));
            }
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
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($bill_id, $debitNote_id)
    {
        if(\Auth::user()->isAbleTo('debitnote edit'))
        {
            $debitNote = CustomerDebitNotes::find($debitNote_id);
            $statues   = CustomerDebitNotes :: $statues;

            return view('account::customerDebitNote.edit', compact('debitNote','statues'));
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
    public function update(Request $request, $bill_id, $debitNote_id)
    {
        if(\Auth::user()->isAbleTo('debitnote edit'))
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
            $billDue = Bill::where('id', $bill_id)->first();

            $debit = CustomerDebitNotes::find($debitNote_id);

            $debitAmount  = floatval($request->amount);

            $billPaid      = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();

            $existingDebits = CustomerDebitNotes::where('bill', $bill_id)->where('id', '!=', $debitNote_id)->get()->sum('amount');

            if (($existingDebits + $debitAmount) > $billPaid) {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($billPaid - $existingDebits) . ' credit to this bill.');
            }

            // store debitnote balance vendor's table
             $this->updateBalance('vendor', $billDue->vendor_id, $debit->amount, 'debit');

            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->status      = $request->status;
            $debit->description = $request->description;
            $debit->save();

             $this->updateBalance('vendor', $billDue->vendor_id, $request->amount, 'credit');

            return redirect()->back()->with('success', __('The debit note details are updated successfully.'));
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

    public function destroy($bill_id, $debitNote_id)
    {
        if(\Auth::user()->isAbleTo('debitnote delete'))
        {
            $debitNote = CustomerDebitNotes::find($debitNote_id);
            // store debitnote balance vendor's table
             $this->updateBalance('vendor', $debitNote->vendor, $debitNote->amount, 'debit');
            $debitNote->delete();

            return redirect()->back()->with('success', __('The debit note has been deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
