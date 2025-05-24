@if(module_is_active('Account') && !empty($vender))
    <div class="row row-gap">
        @if(isset($vender['billing_name']))
            <div class="col-sm-5 col-12">
                <h6>{{__('Bill to')}}</h6>
                <div class="bill-to">
                    <p class="mb-0">
                        <span>{{$vender['billing_name']}}</span><br>
                        <span>{{$vender['billing_address']}}</span><br>
                        <span>{{$vender['billing_city'].' , '.$vender['billing_state'].' ,'. $vender['billing_zip']}}</span><br>
                        <span>{{$vender['billing_country']}}</span><br>
                        <span>{{$vender['billing_phone']}}</span><br>
                    </p>
                </div>
            </div>
            <div class="col-sm-5 col-12">
                <h6>{{__('Ship to')}}</h6>
                <div class="bill-to">
                    <p class="mb-0">
                        <span>{{$vender['shipping_name']}}</span><br>
                        <span>{{$vender['shipping_address']}}</span><br>
                        <span>{{$vender['shipping_city'].' , '.$vender['shipping_state'].' ,'. $vender['shipping_zip']}}</span><br>
                        <span>{{$vender['shipping_country']}}</span><br>
                        <span>{{$vender['shipping_phone']}}</span><br>
                    </p>
                </div>
            </div>
        @else
            <div class="col-md-10">
                    <div class="mt-3"><h6>{{$vender['name']}}</h6><h6>{{$vender['email']}}</h6></div>
                <h6 class="">{{__('Please Set Vendor Shipping And Billing  Details !')}}
                    @if(module_is_active('Account'))
                        <a href="{{ route('vendors.index') }}">{{ __('Click Here')}}</a>
                    @endif
                </h6>
            </div>
        @endif
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm btn btn-danger">{{__('Remove')}}</a>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-5">
            <h6 class="mt-5">{{__('Please Set vender Details !')}}
                <div class="mt-3"><h6>{{$vender['name']}}</h6><h6>{{$vender['email']}}</h6></div>
                @if(module_is_active('Account'))
                    <a href="{{ route('vendors.index') }}">{{ __('Click Here')}}</a>
                @endif
            </h6>
        </div>
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm btn  btn-danger">{{__(' Remove')}}</a>
        </div>
        {{-- <div class="col-md-2 mt-5">
            <a href="#" id="remove" class="text-sm">{{__(' Remove')}}</a>
        </div> --}}
    </div>
@endif
