{{ Form::model($saturationdeduction, ['route' => ['saturationdeduction.update', $saturationdeduction->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('deduction_option', __('Deduction Options'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('deduction_option', $deduction_options, null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Select Deduction Option')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('type', $saturationdeduc, null, ['class' => 'form-control amount_type ', 'required' => 'required', 'placeholder' => __('Select Type')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', $saturationdeduction->type == 'percentage' ? __('Percentage') : __('Amount'), ['class' => 'form-label amount_label']) }}<x-required></x-required>
                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Amount'), 'step' => '0.10']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
