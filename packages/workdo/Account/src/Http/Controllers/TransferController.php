<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\DataTables\TransferDataTable;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\Transfer;
use Workdo\Account\Events\CreateBankTransfer;
use Workdo\Account\Events\DestroyBankTransfer;
use Workdo\Account\Events\UpdateBankTransfer;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(TransferDataTable $dataTable)
    {
        if(Auth::user()->isAbleTo('bank transfer manage'))
        {
            $account = BankAccount::where('workspace', getActiveWorkSpace())->get()->pluck('holder_name', 'id');
            return $dataTable->render('account::transfer.index',compact('account'));
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
        if(Auth::user()->isAbleTo('bank transfer create'))
        {
            $bankAccount = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('account::transfer.create', compact('bankAccount'));
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
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('bank transfer create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'from_account' => 'required|numeric',
                                   'to_account' => 'required|numeric',
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required',
                                   'from_type' => 'required',
                                   'to_type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $transfer                 = new Transfer();
            $transfer->from_account   = $request->from_account;
            $transfer->to_account     = $request->to_account;
            $transfer->from_type      = $request->from_type;
            $transfer->to_type        = $request->to_type;
            $transfer->amount         = $request->amount;
            $transfer->date           = $request->date;
            $transfer->payment_method = 0;
            $transfer->reference      = $request->reference;
            $transfer->description    = $request->description;
            $transfer->created_by      = creatorId();
            $transfer->workspace      = getActiveWorkSpace();
            $transfer->save();

            Transfer::bankAccountBalance($request->from_account, $request->amount, 'debit');

            Transfer::bankAccountBalance($request->to_account, $request->amount, 'credit');
            event(new CreateBankTransfer($request,$transfer));

            return redirect()->route('bank-transfer.index')->with('success', __('The amount has been created successfully.'));
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
    public function edit($id)
    {
        $transfer = Transfer::find($id);
        if(Auth::user()->isAbleTo('bank transfer edit'))
        {
            $bankAccount = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            return view('account::transfer.edit', compact('bankAccount', 'transfer'));
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
    public function update(Request $request, $id)
    {
        $transfer = Transfer::find($id);
        if(Auth::user()->isAbleTo('bank transfer edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                    'from_account' => 'required|numeric',
                                    'to_account' => 'required|numeric',
                                    'amount' => 'required|numeric|gt:0',
                                    'date' => 'required',
                                    'from_type' => 'required',
                                     'to_type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            Transfer::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
            Transfer::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');


            $transfer->from_account   = $request->from_account;
            $transfer->to_account     = $request->to_account;
            $transfer->from_type      = $request->from_type;
            $transfer->to_type        = $request->to_type;
            $transfer->amount         = $request->amount;
            $transfer->date           = $request->date;
            $transfer->payment_method = 0;
            $transfer->reference      = $request->reference;
            $transfer->description    = $request->description;
            $transfer->save();


            Transfer::bankAccountBalance($request->from_account, $request->amount, 'debit');
            Transfer::bankAccountBalance($request->to_account, $request->amount, 'credit');
            event(new UpdateBankTransfer($request,$transfer));

            return redirect()->route('bank-transfer.index')->with('success', __('The amount details are updated successfully.'));
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
    public function destroy($id)
    {
        $transfer = Transfer::find($id);

        if(Auth::user()->isAbleTo('bank transfer delete'))
        {
            if($transfer->created_by == creatorId() && $transfer->workspace == getActiveWorkSpace())
            {
                Transfer::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
                Transfer::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');
                event(new DestroyBankTransfer($transfer));

                $transfer->delete();

                return redirect()->route('bank-transfer.index')->with('success', __('The amount transfer has been deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
