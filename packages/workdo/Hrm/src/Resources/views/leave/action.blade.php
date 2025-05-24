    {{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table modal-table">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type ') }}</th>
                    <td>{{ !empty($leavetype->title) ? $leavetype->title : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Appplied On') }}</th>
                    <td>{{ company_date_formate($leave->applied_on) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ company_date_formate($leave->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ company_date_formate($leave->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Reason') }}</th>
                    <td class="text-wrap text-break">{{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>{{ !empty($leave->status) ? $leave->status : '' }}</td>
                </tr>
                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
            </table>
        </div>
    </div>
    @if ($leave->status == 'Pending')
        <div class="modal-footer">
            <input type="submit" value="{{ __('Approved') }}" class="btn btn-primary" name="status">
            <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger" name="status">
        </div>
    @endif
    {{ Form::close() }}
