{{ Form::open(array('route' => array('invoice.credit.storenote',$invoice_id),'mothod'=>'post', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{Form::date('date',null,array('class'=>'form-control ','required'=>'required','placeholder'=>'Select Issue Date','max' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{ Form::number('amount', !empty($customer->credit_note_balance)?$customer->credit_note_balance:0, array('class' => 'form-control','required'=>'required','step'=>'0.01','min'=>'0')) }}
            </div>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', '', ['class'=>'form-control','rows'=>'3','placeholder' => 'Enter Description']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
