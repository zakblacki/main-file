{{ Form::open(array('url' => 'warehouses-transfer','class' => 'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('from_warehouse', __('From Warehouse'),['class'=>'form-label']) }}<x-required></x-required>
            <select class="form-control select" name="from_warehouse" id="warehouse_id" placeholder="Select Warehouse">
                <option value="">{{__('Select Warehouse')}}</option>
                @foreach($from_warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('to_warehouse',__('To Warehouse'),array('class'=>'form-label')) }}<x-required></x-required>
            {{ Form::select('to_warehouse', $to_warehouses,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6" id="product_div">
            {{Form::label('product',__('Product'),array('class'=>'form-label')) }}<x-required></x-required>
            <select class="form-control select" name="product_id" id="product_id" placeholder="Select Product" required>
            </select>
        </div>

        <div class="form-group col-md-6" id="qty_div">
            {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('quantity',null, array('class' => 'form-control','id' => 'quantity','required'=>'required','placeholder' =>'Enter Quantity')) }}
        </div>


        <div class="form-group col-lg-6">
            {{Form::label('date',__('Date'))}}
            {{Form::date('date',null,array('class'=>'form-control datepicker w-100 mt-2'))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Form::close() }}
