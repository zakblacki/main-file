{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'leave','module'=>'Hrm'])
        @endif
    </div>
    <div class="row">
        @if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type))
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::select('employee_id', $employees, $leave->employee_id, ['class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                </div>
            </div>
        @else
            {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0,['id' => 'employee_id']) !!}
        @endif
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label']) }}<x-required></x-required>
                <select name="leave_type_id" id="leave_type_id" class="form-control" required='required'>
                    @foreach ($leavetypes as $type)
                        <option value="{{ $type->id }}" @if ($type->id == $leave->leave_type_id) selected @endif>
                            {{ $type->title }} (<p class="float-right pr-5">
                                {{ $type->days }}</p>)</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Remark'), 'rows' => '3']) }}
            </div>
        </div>
        @if ($leave->status == 'Pending')
            @permission('leave approver manage')
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                        <select name="status" id="" class="form-control select2">
                            <option value="">{{ __('Select Status') }}</option>
                            <option value="Pending" @if ($leave->status == 'Pending') selected="" @endif>
                                {{ __('Pending') }}
                            </option>
                            <option value="Approved" @if ($leave->status == 'Approve') selected="" @endif>
                                {{ __('Approve') }}
                            </option>
                            <option value="Reject" @if ($leave->status == 'Reject') selected="" @endif>{{ __('Reject') }}
                            </option>
                        </select>
                    </div>
                </div>
            @endpermission
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
<script>
    $(document).ready(function () {
        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if(employee_id)
            {
                $('#employee_id').trigger('change');
            }
        }, 100);
    });
</script>
