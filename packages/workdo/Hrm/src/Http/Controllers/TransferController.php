<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\DataTables\EmpTransferDataTable;
use Workdo\Hrm\Entities\Branch;
use Workdo\Hrm\Entities\Department;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\Transfer;
use Workdo\Hrm\Events\CreateTransfer;
use Workdo\Hrm\Events\DestroyTransfer;
use Workdo\Hrm\Events\UpdateTransfer;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EmpTransferDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('transfer manage')) {
            return $dataTable->render('hrm::transfer.index');

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('transfer create')) {
            $departments  = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $branches    = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');

            return view('hrm::transfer.create', compact('employees', 'branches', 'departments'));
        } else {
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
        if (Auth::user()->isAbleTo('transfer create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'transfer_date' => 'required|after:yesterday',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $transfer                = new Transfer();
            $employee = Employee::where('user_id', '=', $request->employee_id)->first();
            if (!empty($employee)) {
                $transfer->employee_id = $employee->id;
            }
            $transfer->user_id       = $request->employee_id;
            $transfer->branch_id     = $request->branch_id;
            $transfer->department_id = $request->department_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->description   = $request->description;
            $transfer->workspace     = getActiveWorkSpace();
            $transfer->created_by    = creatorId();
            $transfer->save();

            event(new CreateTransfer($request, $transfer));

            // $setings = Utility::settings();
            $company_settings = getCompanyAllSetting();
            if (!empty($company_settings['Employee Transfer']) && $company_settings['Employee Transfer']  == true) {

                $branch  = Branch::find($transfer->branch_id);
                $department = Department::find($transfer->department_id);
                $User        = User::where('id', $transfer->user_id)->where('workspace_id', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'transfer_name' => $User->name,
                    'transfer_date' => $request->transfer_date,
                    'transfer_branch' => $branch->name,
                    'transfer_department' => $department->name,
                    'transfer_description' => $request->description,
                ];
                try {

                    $resp = EmailTemplate::sendEmailTemplate('Employee Transfer', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('transfer.index')->with('success', __('The transfer has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('transfer.index')->with('success', __('The transfer has been created successfully.'));
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
        return redirect()->back();
        return view('hrm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Transfer $transfer)
    {
        if (Auth::user()->isAbleTo('transfer edit')) {
            if ($transfer->created_by == creatorId() && $transfer->workspace == getActiveWorkSpace()) {
                $departments  = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $branches    = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');

                return view('hrm::transfer.edit', compact('transfer', 'employees', 'departments', 'branches'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Transfer $transfer)
    {
        if (Auth::user()->isAbleTo('transfer edit')) {
            if ($transfer->created_by == creatorId() && $transfer->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'branch_id' => 'required',
                        'department_id' => 'required',
                        'transfer_date' => 'required|after:yesterday',
                        'description' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $employee = Employee::where('user_id', '=', $request->employee_id)->first();
                if (!empty($employee)) {
                    $transfer->employee_id = $employee->id;
                }
                $transfer->user_id       = $request->employee_id;
                $transfer->branch_id     = $request->branch_id;
                $transfer->department_id = $request->department_id;
                $transfer->transfer_date = $request->transfer_date;
                $transfer->description   = $request->description;
                $transfer->save();

                event(new UpdateTransfer($request, $transfer));

                return redirect()->route('transfer.index')->with('success', __('The transfer details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Transfer $transfer)
    {
        if (Auth::user()->isAbleTo('transfer delete')) {
            if ($transfer->created_by == creatorId() && $transfer->workspace == getActiveWorkSpace()) {

                event(new DestroyTransfer($transfer));

                $transfer->delete();

                return redirect()->route('transfer.index')->with('success', __('The transfer has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $transfers = Transfer::find($id);
        return view('hrm::transfer.description', compact('transfers'));
    }
}
