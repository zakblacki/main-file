@php
    $userprice = !empty($plan) ? $plan->price_per_user_monthly : 0;
    $userpriceyearly = !empty($plan) ? $plan->price_per_user_yearly : 0;

    $workspaceprice = !empty($plan) ? $plan->price_per_workspace_monthly : 0;
    $workspacepriceyearly = !empty($plan) ? $plan->price_per_workspace_yearly : 0;

    $planprice = !empty($plan) ? $plan->package_price_monthly : 0;
    $planpriceyearly = !empty($plan) ? $plan->package_price_yearly : 0;
    $currancy_symbol = admin_setting('defult_currancy_symbol');
    $subscriptionDetail = SubscriptionDetails($user->id);
    $currency_setting = json_encode(Arr::only(getAdminAllSetting(), ['site_currency_symbol_position','currency_format','currency_space','site_currency_symbol_name','defult_currancy_symbol','defult_currancy','float_number','decimal_separator','thousand_separator']));
@endphp
@extends('layouts.main')
@section('page-title')
    {{ __('Pricing') }}
@endsection
@section('page-breadcrumb')
    {{ __('Pricing') }}
@endsection
@push('scripts')
@endpush
@section('content')
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-8 col-xl-7">
                    <div class="row">
                        @if($subscriptionDetail['status'] == true)
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body package-card-inner  d-flex align-items-center">
                                        <div class="package-itm theme-avtar border border-secondary">
                                            <img src="{{ (!empty(admin_setting('favicon')) && check_file(admin_setting('favicon'))) ? get_file(admin_setting('favicon')) : get_file('uploads/logo/favicon.png')}}{{'?'.time()}}" alt="">
                                        </div>
                                        <div class="package-content flex-grow-1  px-3">
                                            <h4>{{ __('Current Subscription')}}</h4>
                                            <div class="text-muted"> <a href="#activated-add-on">{{ count($purchaseds). __(' Premium Add-on Activated')}}</a></div>
                                        </div>
                                        <div class="price text-end">
                                            <small>{{  ($subscriptionDetail['status'] == true) ? $subscriptionDetail['billing_type'] : '' }}</small>
                                            <h5>{{ ($subscriptionDetail['status'] == true) ? $subscriptionDetail['total_user'].' '.__('Users') : '' }}</h5>
                                            <h5>{{ ($subscriptionDetail['status'] == true) ? $subscriptionDetail['total_workspace'].' '.__('Workspace') : '' }}</h5>
                                            <span class="time-lbl text-muted">{{ (($subscriptionDetail['status'] == true) && ($subscriptionDetail['plan_expire_date'] != null)) ? __('Expired At ').$subscriptionDetail['plan_expire_date'] : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body package-card-inner  d-flex align-items-center">
                                    <div class="package-itm theme-avtar border border-secondary">
                                        <img src="{{ (!empty(admin_setting('favicon')) && check_file(admin_setting('favicon'))) ? get_file(admin_setting('favicon')) : get_file('uploads/logo/favicon.png')}}{{'?'.time()}}" alt="">
                                    </div>
                                    <div class="package-content flex-grow-1  px-3">
                                        <h4>{{ __('Basic Package')}}</h4>
                                        <div class="text-muted"><a href="#add-on-list">{{ __('+'.count($modules)+count($purchaseds).' Premium Add-on')}}</a></div>
                                    </div>
                                    <div class="price text-end">
                                        <ins class="plan-price-text">{{ super_currency_format_with_sym($planprice) }}</ins>
                                        <span class="time-lbl text-muted plan-time-text">{{ __('/Month') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (count($modules) > 0)
                            <div class="d-flex justify-content-between bd-highlight">
                                <h5 class="mb-1" id="add-on-list">{{ __('Modules') }}</h5>
                                <h5>
                                    <label for="check-all-module" class="form-check-label pointer mx-2">{{ __('Select All Add-on') }}</label>
                                    <input type="checkbox" id="check-all-module" class="form-check-input pointer">
                                </h5>
                            </div>
                            @foreach ($modules as $module)
                                @if (!isset($module->display) || $module->display == true)
                                <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-6 product-card ">
                                    <div class="product-card-inner">
                                        <div class="card user_module">
                                            <div class="product-img">
                                                <div class="theme-avtar">
                                                    <img src="{{ $module->image }}"
                                                        alt="{{ $module->name }}" class="img-user"
                                                        style="max-width: 100%">
                                                </div>
                                                <div class="checkbox-custom">
                                                    <input type="checkbox" {{ ((isset($session) && !empty($session) && ( in_array($module->name,explode(',',$session['user_module'])) ))) ? 'checked' :''}}
                                                        class="form-check-input pointer user_module_check"
                                                        data-module-img="{{ $module->image }}"
                                                        data-module-price-monthly="{{ $module->monthly_price }}"
                                                        data-module-price-yearly="{{ $module->yearly_price }}"
                                                        data-module-alias="{{ $module->alias }}"
                                                        value="{{ $module->name }}">
                                                </div>
                                            </div>
                                            <div class="product-content">
                                                <h4> {{ $module->alias }}</h4>
                                                <p class="text-muted text-sm mb-0">
                                                    {{ isset($module->description) ? $module->description : '' }}
                                                </p>
                                                <div class="price d-flex justify-content-between">
                                                    <ins class="m-price-monthly"><span class="currency-type">{{ super_currency_format_with_sym($module->monthly_price) }}</span> <span class="time-lbl text-muted">{{ __('/Month') }}</span></ins>
                                                    <ins class="m-price-yearly d-none"><span class="currency-type">{{ super_currency_format_with_sym($module->yearly_price) }}</span> <span class="time-lbl text-muted">{{ __('/Year') }}</span></ins>
                                                </div>
                                                <a href="{{ route('software.details',$module->alias) }}" target="_new" class="btn  btn-outline-secondary w-100 mt-2">{{ __('View Details')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-lg-12 col-md-12">
                                <div class="card p-5">
                                    <div class="d-flex justify-content-center">
                                        <div class="ms-3 text-center">
                                            <h3>{{ __('Add-on Not Available') }}</h3>
                                            <p class="text-muted">{{ __('Click ') }}<a
                                                    href="{{ url('/') }}">{{ __('here') }}</a>
                                                {{ __('to back home') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                            <hr>
                        @if (!empty($purchaseds))
                        <h5 class="mb-3" id="activated-add-on">{{ __('Activated') }}</h5>
                        @foreach ($purchaseds as $purchased)
                            @if (!isset($purchased->display) || $purchased->display == true)
                            <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-6 product-card ">
                                <div class="card active_module">
                                    <div class="product-img">
                                        <div class="theme-avtar">
                                            <img src="{{ $purchased->image }}"
                                                            alt="{{ $purchased->name }}" class="img-user"
                                                            style="max-width: 100%">
                                        </div>
                                        <div class="checkbox-custom">
                                            <div class="action-btn bg-danger ms-2">
                                                {{Form::open(array('route'=>array('cancel.add.on',[\Illuminate\Support\Facades\Crypt::encrypt($purchased->name),$user->id]),'class' => 'm-0'))}}
                                                @method('GET')
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Cancel Add-on')}}"
                                                        aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('Cancel Add-on. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$purchased->name}}">
                                                        <i class="ti ti-x text-white text-white"></i>
                                                    </a>
                                                {{Form::close()}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product-content">
                                        <h4> {{ $purchased->alias }}</h4>
                                        <p class="text-muted text-sm mb-0">
                                            {{ isset($purchased->description) ? $purchased->description : '' }}
                                        </p>
                                        <div class="price d-flex justify-content-between">
                                            <ins class="m-price-monthly"><span class="currency-type">{{ super_currency_format_with_sym($purchased->monthly_price) }}</span> <span class="time-lbl text-muted">{{ __('/Month') }}</span></ins>
                                            <ins class="m-price-yearly d-none"><span class="currency-type">{{ super_currency_format_with_sym($purchased->yearly_price) }}</span> <span class="time-lbl text-muted">{{ __('/Year') }}</span></ins>
                                        </div>
                                        <a href="{{ route('software.details',$purchased->alias) }}" target="_new" class="btn  btn-outline-secondary w-100 mt-2">{{ __('View Details') }}</a>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @endif
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-5">
                    <div class="card subscription-counter">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mt-1">{{ __('Basic Package')}}</h5>
                            <label class="switch ">
                                <span class="lbl time-monthly text-primary">{{ __('Monthly')}}</span>
                                <input type="checkbox" name="time-period" class="switch-change">
                                <span class="slider round"></span>
                                <span class="lbl time-yearly">{{ __('Yearly')}}</span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="subscription-summery">
                                <ul class="list-unstyled mb-0">
                                    <li>
                                        <span class="cart-sum-left"> <i class="ti ti-vector-bezier m-2 ti-20" ></i>{{ __('Workspace ') }}:</span>
                                        <span class="cart-sum-right workspace_counter_text">0</span>
                                    </li>
                                    <li>
                                        <span class="cart-sum-left"> <i class="ti ti-users m-2 ti-20"></i>{{ __('Users ') }}:</span>
                                        <span class="cart-sum-right user_counter_text">0</span>
                                    </li>

                                    <li class="pointer extension-trigger" data-bs-toggle="collapse" data-bs-target="#extension_div">
                                        <span class="cart-sum-left"><i class="ti ti-3d-cube-sphere m-2 ti-20"></i>{{ __('Extension') }}:</span>
                                        <span class="cart-sum-right module_counter_text">0</span>
                                    </li>
                                    <div class="row align-items-center my-4 collapse" id="extension_div">
                                    </div>

                                </ul>

                                <div class="summery-footer">
                                    <div class="user-qty">
                                        <div class="lbl"> {{ __('Choose Workspace') }}:</div>
                                        <div class="qty-spinner">
                                            <button type="button" class="quantity-decrement" data-name = "workspace">
                                                <i class="ti ti-circle-minus m-2 ti-25"></i>
                                            </button>
                                            <input id="workspace_counter" type="number" data-cke-saved-name="quantity" name="quantity" class="quantity" step="1" value="0" min="0" max="1000" data-name = "workspace">
                                            <button type="button" class="quantity-increment " data-name = "workspace">
                                                <i class="ti ti-circle-plus m-2 ti-25"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="user-qty">
                                        <div class="lbl"> {{ __('Choose Users') }}:</div>
                                        <div class="qty-spinner">
                                            <button type="button" class="quantity-decrement" data-name = "user">
                                                <i class="ti ti-circle-minus m-2 ti-25"></i>
                                            </button>
                                            <input id="user_counter" type="number" data-cke-saved-name="quantity" name="quantity" class="quantity" step="1" value="0" min="0" max="1000" data-name = "user">
                                            <button type="button" class="quantity-increment " data-name = "user">
                                                <i class="ti ti-circle-plus m-2 ti-25"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <ul class="list-unstyled mb-0">
                                        <li>
                                            <span class="cart-sum-left"><h6 class="mb-0">{{ __('Basic Package') }}</h6></span>
                                            <span class="cart-sum-right"><b class="planpricetext "> <span class="final_price">{{ ($planprice > 0 ) ? super_currency_format_with_sym($planprice) : 'Free' }}</span></b></span>
                                        </li>
                                        <li>
                                            <span class="cart-sum-left"><h6 class="mb-0">{{ __('Workspace') }} <small
                                                class="text-muted workspace-price">{{ '( ' . __('Per Workspace') .  super_currency_format_with_sym($workspaceprice)  . ' )' }}</small></h6></span>
                                            <span class="cart-sum-right"><b class="workspacepricetext final_price">{{ super_currency_format_with_sym(0) }}</b></span>
                                        </li>
                                        <li>
                                            <span class="cart-sum-left"><h6 class="mb-0">{{ __('Users') }} <small
                                                class="text-muted user-price">{{ '( '. __('Per User') . super_currency_format_with_sym($userprice) . ' )' }}</small></h6></span>
                                            <span class="cart-sum-right"><b class="userpricetext final_price">{{ super_currency_format_with_sym(0) }}</b></span>
                                        </li>
                                        <li>
                                            <span class="cart-sum-left"><h6 class="mb-0">{{ __('Extension') }}:</h6></span>
                                            <span class="cart-sum-right"><b class="module_price_text final_price">{{ super_currency_format_with_sym(0) }}</b></span>
                                        </li>
                                    </ul>

                                    <div class="cart-footer-total-row bg-primary text-white rounded p-3 d-flex align-items-center justify-content-between">
                                        <div class="mini-total-price">
                                            <div class="price">
                                                <h3 class="text-white mb-0 total">{{ super_currency_format_with_sym(0) }}</h3>
                                                <span class="time-lbl plan-time-text">{{ __('/Month')}}</span>
                                            </div>
                                        </div>
                                        {{ Form::open(array('route'=>['assign.plan.user',[Crypt::encrypt($plan->id),Crypt::encrypt($user->id)]],'method'=>'POST'))}}
                                            <input type="hidden" name="workspaceprice_input" value="0" class="workspaceprice_input">
                                            <input type="hidden" name="workspace_counter_input" value="0" class="workspace_counter_input">
                                            <input type="hidden" name="user_counter_input" value="0" class="user_counter_input">
                                            <input type="hidden" name="user_module_input" value="" name="user_module_input"
                                                class="user_module_input">
                                            <input type="hidden" name="userprice_input" value="0" class="userprice_input">
                                            <input type="hidden" name="user_module_price_input" value="0" class="user_module_price_input">
                                            <input type="hidden" name="time_period" value="Month" class="time_period_input">
                                            <input type="hidden" name="workspace_module_price_input" value="0" class="workspace_module_price_input">
                                            <input type="hidden" name="coupon_code" value="" class="coupon_code">

                                            <div class="text-end form-btn">
                                                <button type="submit" class="btn btn-dark payment-btn"  >{{ __("Assign Now") }}</button>
                                            </div>
                                        {{ Form::close() }}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
@endsection
@push('scripts')
    <script>
        $(document).on("click", "#check-all-module", function() {
            if ($('#check-all-module').prop('checked')==true)
            {
                $('.user_module_check').prop('checked',true);
            }
            else
            {
                $('.user_module_check').prop('checked',false);
            }
            ChangeModulePrice();
            ChangePrice();
        });

        $(document).ready(function() {

            var userprice = '{{ $userprice }}';
            var planprice = '{{ $planprice }}';
            if(planprice  == 0){
                $(".coupon_section").addClass("d-none");
            }else{
                $(".coupon_section").removeClass("d-none");
            }
            if ($('.switch-change').prop('checked')==true)
            {
                userprice = '{{ $userpriceyearly }}';
                planprice = '{{ $planpriceyearly }}';

            }
            var user = parseInt($('.user_counter_input').val());
            var userpricetext = userprice * user;

            var currancy_symbol = '{{ $currancy_symbol }}';
            var total = parseFloat(userpricetext) + parseFloat(planprice);
            $(".total").text(formatCurrency(total,'{{ $currency_setting }}'));
        });
        $(document).on("click", ".user_module_check", function() {
            if ($(this).closest(".user_module").hasClass("active_module"))
            {
                $(this).closest(".user_module").removeClass("active_module");

            } else {
                $(this).closest(".user_module").addClass("active_module");
            }
            ChangeModulePrice();
            ChangePrice();

        });
    </script>
    <script>
         $(document).on('keyup mouseup', '#user_counter, #workspace_counter' , function() {
            var name = $(this).attr('data-name');
            var counter = parseInt($(this).val());
            if (counter <= 0 || counter > 1000 || $(this).val() == '')
            {
                $(this).val(0)
                var counter = 0;
            }
            if(name == "user")
            {
                $(".user_counter_text").text(counter);
                $(".user_counter_input").val(counter);
                ChangePrice(counter)
            }
            else if(name == "workspace")
            {
                $(".workspace_counter_text").text(counter);
                $(".workspace_counter_input").val(counter);
                ChangePrice(null,counter)
            }
        });
    </script>
    <script>
        function ChangePrice(user = null,workspace = null,user_module_price = 0 ) {
            var userprice = '{{ $userprice }}';
            var workspaceprice = '{{ $workspaceprice }}';
            var planprice = '{{ $planprice }}';

            if ($('.switch-change').prop('checked')==true)
            {
                userprice = '{{ $userpriceyearly }}';
                workspaceprice = '{{ $workspacepriceyearly }}';
                planprice = '{{ $planpriceyearly }}';

            }

            var currancy_symbol = '{{ $currancy_symbol }}';
            if (user == null) {
                var user = parseInt($('.user_counter_input').val());
            }
            if (user_module_price == 0) {
                var user_module_price = parseFloat($('.user_module_price_input').val());
            }
            if (workspace == null) {
                var  workspace= parseInt($('.workspace_counter_input').val());
            }

            var userpricetext = userprice * user;
            var workspacepricetext = workspaceprice * workspace;

            var total = userpricetext + user_module_price + workspacepricetext + parseFloat(planprice);

            $(".total").text(formatCurrency(total,'{{ $currency_setting }}'));

            $(".userpricetext").text(formatCurrency(userpricetext,'{{ $currency_setting }}'));
            $(".workspacepricetext").text(formatCurrency(workspacepricetext,'{{ $currency_setting }}'));
            $(".userprice_input").val(formatCurrency(userpricetext,'{{ $currency_setting }}'));
            $(".workspaceprice_input").val(formatCurrency(workspacepricetext,'{{ $currency_setting }}'));

        }
        function ChangeModulePrice() {
            var user_module_input = new Array();
            var user_module_price = parseFloat(0);
            var currancy_symbol = '{{ $currancy_symbol }}';
            var n = jQuery(".user_module_check:checked").length;

            var time = '/Month';
            if ($('.switch-change').prop('checked')==true)
            {
                time = '/Year';
            }

            $("#extension_div").empty();

            if (n > 0) {
                jQuery(".user_module_check:checked").each(function() {

                    var alias = $(this).attr('data-module-alias');
                    var img = $(this).attr('data-module-img');
                    var price = parseFloat($(this).attr('data-module-price-monthly'));

                    if ($('.switch-change').prop('checked')==true)
                    {
                        price = parseFloat($(this).attr('data-module-price-yearly'));
                    }

                    $("#extension_div").append(`<div class="col-md-6 col-sm-6  my-2">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar">
                                            <img src="` + img + `" alt="` + img + `" class="img-user" style="max-width: 100%">
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">` + alias + `</p>
                                            <h4 class="mb-0 text-primary">` + formatCurrency(price,'{{ $currency_setting }}') + `<span class="text-sm">`+time+`</span></h4>
                                        </div>
                                    </div>
                                </div>`);

                    user_module_input.push($(this).val());
                    user_module_price = user_module_price + price;
                });
            }
            $(".module_counter_text").text(n);
            $(".module_price_text").text(formatCurrency(user_module_price,'{{ $currency_setting }}'));
            $(".user_module_input").val(user_module_input);
            $(".user_module_price_input").val(user_module_price);
        }
    /********* qty spinner ********/
    var quantity = 0;
    $('.quantity-increment').click(function()
    {
        var id = $(this).attr('data-name');
        var t = $(this).siblings('.quantity');
        var quantity = parseInt($(t).val());
        if(quantity < 1000 || $(this).val() != '')
        {
            $(t).val(quantity + 1);
            if(id == 'user')
            {
                $(".user_counter_text").text(quantity + 1);
                $(".user_counter_input").val(quantity + 1);
            }
            else if(id == 'workspace')
            {
                $(".workspace_counter_text").text(quantity + 1);
                $(".workspace_counter_input").val(quantity + 1);
            }
        }
        else
        {
            $(t).val(1000);
            if(id == 'user')
            {
                $(".user_counter_text").text(1000);
                $(".user_counter_input").val(1000);
            }
            else if(id == 'workspace')
            {
                $(".workspace_counter_text").text(1000);
                $(".workspace_counter_input").val(1000);
            }
        }

        ChangePrice()
    });
    $('.quantity-decrement').click(function()
    {
        var id = $(this).attr('data-name');
        var t = $(this).siblings('.quantity');
        var quantity = parseInt($(t).val());
        if(quantity > 1)
        {
            $(t).val(quantity - 1);
            if(id == 'user')
            {
                $(".user_counter_text").text(quantity - 1);
                $(".user_counter_input").val(quantity - 1);
            }
            else if(id == 'workspace')
            {
                $(".workspace_counter_text").text(quantity - 1);
                $(".workspace_counter_input").val(quantity - 1);
            }

        }
        else
        {
            $(t).val(0);
            if(id == 'user')
            {
                $(".user_counter_text").text(0);
                $(".user_counter_input").val(0);
            }
            else if(id == 'workspace')
            {
                $(".workspace_counter_text").text(0);
                $(".workspace_counter_input").val(0);
            }
        }
        ChangePrice()
    });
    </script>
    <script>
        $(document).on("click",".switch-change",function()
        {
            SwitchChange()
        });

        function SwitchChange()
        {
            var workspaceprice = '{{ $workspaceprice }}';
            var userprice = '{{ $userprice }}';
            var planprice = '{{ $planprice }}';
            var currancy_symbol = '{{ $currancy_symbol }}';
            var user = parseInt($('.user_counter_input').val());
            var workspace = parseInt($('.workspace_counter_input').val());
            var time = '/Month';


            if ($('.switch-change').prop('checked') == true)
            {

                $(".time-monthly").removeClass("text-primary");
                $(".time-yearly").addClass("text-primary");

                $(".m-price-yearly").removeClass("d-none");
                $(".m-price-monthly").addClass("d-none");

                userprice = '{{ $userpriceyearly }}';
                workspaceprice = '{{ $workspacepriceyearly }}';
                planprice = '{{ $planpriceyearly }}';

                time = '/Year';

                $(".time_period_input").val('Year');

            }
            else
            {
                $(".time-yearly").removeClass("text-primary");
                $(".time-monthly").addClass("text-primary");

                $(".m-price-monthly").removeClass("d-none");
                $(".m-price-yearly").addClass("d-none");

                $(".time_period_input").val('Month');

            }

            var userpricetext = userprice * user;
            var workspacepricetext = workspaceprice * workspace;


            $(".plan-price-text").text(formatCurrency(planprice,'{{ $currency_setting }}'));
            $(".plan-time-text").text(time);

            $(".planpricetext").html('<span class="final_price">'+ formatCurrency(planprice,'{{ $currency_setting }}')  + '</span>');
            $(".user-price").text('( {{ __("Per User") }}'+ formatCurrency(userprice,'{{ $currency_setting }}') +')');
            $(".userpricetext").text(formatCurrency(userpricetext,'{{ $currency_setting }}'));
            $(".workspace-price").text('( {{ __("Per Workspace")}} '+ formatCurrency(workspaceprice,'{{ $currency_setting }}') +')');
            $(".workspacepricetext").text(formatCurrency(workspacepricetext,'{{ $currency_setting }}'));

            if(planprice  == 0){
                $(".coupon_section").addClass("d-none");
            }else{
                $(".coupon_section").removeClass("d-none");
            }
            ChangeModulePrice()
            ChangePrice()
        }
    </script>
    <script>

        $( "#payment_form" ).on( "submit", function( event ) {
            "{{session()->put('Subscription','custom_subscription')}}";
        });
         $(document).on("click",".payment_method",function() {
            var payment_action = $(this).attr("data-payment-action");
            if(payment_action != '' && payment_action != undefined)
            {
                $("#payment_form").attr("action",payment_action);
            }
            else
            {
                $("#payment_form").attr("action",'');
            }
            if ($('#bank-payment').prop('checked'))
            {
                $(".temp_receipt").attr("required", "required");
            }
            else
            {
                $(".temp_receipt").removeAttr("required");
            }
        });
    </script>
    {{-- if session is not empty --}}
    @if (isset($session) && !empty($session))
    <script>
        $(document).ready(function () {
            $('#user_counter').val("{{ $session['user_counter']}}");
            $('#user_counter').trigger('keyup')
            $('#workspace_counter').val("{{ $session['workspace_counter']}}");
            $('#workspace_counter').trigger('keyup')
            SwitchChange();
        });
    </script>
    @endif
@if (admin_setting('bank_transfer_payment_is_on') == 'on')
<script>

    $('#payment_form').submit(function(e)
    {
        if ($('#bank-payment').prop('checked'))
        {
            e.preventDefault(); // Prevent form submission


            var file = document.getElementById('temp_receipt').files[0];

            if(file != undefined)
            {
                $('.error_msg').addClass('d-none');

                // Create a new FormData object
                const formData = new FormData();

                // Add file data from the file input element
                const file = $('#temp_receipt')[0].files[0];
                formData.append('payment_receipt', file, file.name);

                // Add data from the form's input elements
                $('#payment_form input').each(function() {
                formData.append(this.name, this.value);
                });

                var url = $('#payment_form').attr('action');


                $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status == 'success')
                    {
                        toastrs('Success', response.msg, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                    else
                    {
                        toastrs('Error', response.msg, 'error');
                    }
                    // Handle success response
                },
                error: function(xhr, status, error) {
                    toastrs('Error',error, 'error');
                    // Handle error response
                }
                });

            }
            else
            {
                $('.error_msg').removeClass('d-none');
            }
        }
    });

</script>
@endif
@endpush
