{{ Form::open(array('route' => array('bill.debit.storenote',$bill_id),'mothod'=>'post', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
                {{Form::date('date',null,array('class'=>'form-control ','required'=>'required','placeholder'=>'Select Date','max' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('amount', !empty($vendor->debit_note_balance)?$vendor->debit_note_balance:0, array('class' => 'form-control','required'=>'required','step'=>'0.01','min'=>'0','placeholder'=>__('Enter Amount'))) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3,'placeholder'=>__('Enter Description'))) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
