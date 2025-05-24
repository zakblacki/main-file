<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\DataTables\EmpAwardDataTable;
use Workdo\Hrm\Entities\Award;
use Workdo\Hrm\Entities\AwardType;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Events\CreateAward;
use Workdo\Hrm\Events\DestroyAward;
use Workdo\Hrm\Events\UpdateAward;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EmpAwardDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('award manage')) {

            return $dataTable->render('hrm::award.index');
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
        if (Auth::user()->isAbleTo('award create')) {
            $employees = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');
            $awardtypes = AwardType::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('hrm::award.create', compact('employees', 'awardtypes'));
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
        if (Auth::user()->isAbleTo('award create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'award_type' => 'required',
                    'date' => 'required|after:yesterday',
                    'gift' => 'required',
                    'description' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $award              = new Award();
            $employee = Employee::where('user_id', '=', $request->employee_id)->first();
            if (!empty($employee)) {
                $award->employee_id = $employee->id;
            }
            $award->user_id     = $request->employee_id;
            $award->award_type  = $request->award_type;
            $award->date        = $request->date;
            $award->gift        = $request->gift;
            $award->description =  $request->description;
            $award->workspace   = getActiveWorkSpace();
            $award->created_by  = creatorId();
            $award->save();

            event(new CreateAward($request, $award));

            $awardtype = AwardType::find($request->award_type);
            $company_settings = getCompanyAllSetting();

            if (!empty($company_settings['New Award']) && $company_settings['New Award']  == true) {
                $User        = User::where('id', $request->employee_id)->where('workspace_id', '=',  getActiveWorkSpace())->first();

                $uArr = [
                    'award_name' => $User->name,
                    'award_date' => $award->date,
                    'award_type' => $awardtype->name,
                ];
                try {
                    $resp = EmailTemplate::sendEmailTemplate('New Award', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('award.index')->with('success', __('The award has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            return redirect()->route('award.index')->with('success', __('The award has been created successfully.'));
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
    public function edit(Award $award)
    {
        if (Auth::user()->isAbleTo('award edit')) {
            if ($award->created_by == creatorId() && $award->workspace == getActiveWorkSpace()) {
                $employees = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');
                $awardtypes = AwardType::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                return view('hrm::award.edit', compact('award', 'awardtypes', 'employees'));
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
    public function update(Request $request, Award $award)
    {
        if (Auth::user()->isAbleTo('award edit')) {
            if ($award->created_by == creatorId() && $award->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'award_type' => 'required',
                        'date' => 'required|after:yesterday',
                        'gift' => 'required',
                        'description' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $employee = Employee::where('user_id', '=', $request->employee_id)->first();
                if (!empty($employee)) {
                    $award->employee_id = $employee->id;
                }
                $award->user_id     = $request->employee_id;
                $award->award_type  = $request->award_type;
                $award->date        = $request->date;
                $award->gift        = $request->gift;
                $award->description = $request->description;
                $award->save();
                event(new UpdateAward($request, $award));
                return redirect()->route('award.index')->with('success', __('The award details are updated successfully.'));
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
    public function destroy(Award $award)
    {
        if (Auth::user()->isAbleTo('award delete')) {
            if ($award->created_by == creatorId() && $award->workspace == getActiveWorkSpace()) {
                event(new DestroyAward($award));
                $award->delete();

                return redirect()->route('award.index')->with('success', __('The award has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $awards = Award::find($id);
        return view('hrm::award.description', compact('awards'));
    }

}
