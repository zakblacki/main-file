@extends('layouts.main')

@section('page-title')
    {{ __('Referral Program') }}
@endsection

@section('page-breadcrumb')
    {{ __('Referral Program') }}
@endsection


@push('scripts')
    <script>
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')

        });

        $('.tab-link').on('click', function() {
            var tabId = $(this).data('tab');
            $('.tabcontent').addClass('d-none');
            $('#' + tabId).removeClass('d-none');

            $('.tab-link').removeClass('active');
            $(this).addClass('active');
        });
    </script>
@endpush
@php
    $companyName = $company_settings['company_name'] ?? \Auth::user()->name;
@endphp
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#" class="list-group-item list-group-item-action border-0 tab-link active"
                                data-tab="guideline">{{ __('GuideLine') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 tab-link"
                                data-tab="referral-transaction">{{ __('Referral Transaction') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 tab-link"
                                data-tab="payout">{{ __('Payout') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}

                    <!--Site Settings-->
                    <div id="guideline" class="card tabcontent">
                        <div class="card-header">
                            <h5>{{ __('GuideLine') }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6 col-md-6 ">
                                    <div class = "border border-2 p-3">
                                        <h4>{{ __('Refer ') . $companyName .  __(' and earn ') . (isset($setting) ? $setting->percentage . '%' : '') . __(' per paid signup!') }}</h4>
                                        {!! isset($setting) ? $setting->guideline : '' !!}
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6 col-md-6 mt-5">
                                    <h4 class="text-center">{{ __('Share Your Link') }}</h4>
                                    <div class="d-flex justify-content-between">
                                        <a href="#!" class="btn btn-sm btn-light-primary w-100 cp_link"
                                            data-link="{{ route('register', ['ref_id' => \Auth::user()->referral_code]) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                            data-bs-original-title="Click to copy business link">
                                            {{ route('register', ['ref' => \Auth::user()->referral_code]) }}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-copy ms-1">
                                                <rect x="9" y="9" width="13" height="13" rx="2"
                                                    ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                @if(isset($setting) && $setting->is_enable == 0 || !isset($setting))
                                        <h6 class="text-end text-danger text-md mt-2">{{ __('Note : super admin has disabled the referral program.') }}</h6>
                                    @endif
                            </div>
                        </div>

                    </div>

                    <div id="referral-transaction" class="card tabcontent d-none">
                        <div class="card-header">
                            <h5>{{ __('Referral Transaction') }}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Company Name') }}</th>
                                            <th>{{ __('Plan Name') }}</th>
                                            <th>{{ __('Plan Price') }}</th>
                                            <th>{{ __('Commission (%)') }}</th>
                                            <th>{{ __('Commission Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $key => $transaction)
                                            <tr>
                                                <td> {{ ++$key }} </td>
                                                <td>{{ !empty($transaction->company_id) ? $transaction->user_name : '-' }}
                                                </td>
                                                <td>{{ !empty($transaction->plan_name) ? $transaction->plan_name : 'Basic Package' }}
                                                </td>
                                                <td>{{ super_currency_format_with_sym($transaction->plan_price) }}</td>
                                                <td>{{ $transaction->commission }}</td>
                                                <td>{{ super_currency_format_with_sym(($transaction->plan_price * $transaction->commission) / 100) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="payout" class="tabcontent d-none">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="">{{ __('PayOut') }}</h5>
                                    </div>
                                    <div class="col-6 text-end">
                                        @if (\Auth::user()->commission_amount > $paidAmount)
                                            @if ($paymentRequest == null)
                                                <a href="#"
                                                    data-url = "{{ route('request.amount.sent', [\Illuminate\Support\Facades\Crypt::encrypt(\Auth::user()->id)]) }}"
                                                    data-ajax-popup="true" class="btn btn-primary btn-sm"
                                                    data-title="{{ __('Send Request') }}" data-bs-toggle="tooltip"
                                                    title="{{ __('Send Request') }}">
                                                    <span class="btn-inner--icon"><i
                                                            class="ti ti-corner-up-right"></i></span>
                                                </a>
                                            @else
                                                <a href="{{ route('request.amount.cancel', \Auth::user()->id) }}"
                                                    class="btn btn-danger btn-sm" data-title="{{ __('Cancel Request') }}"
                                                    data-bs-toggle="tooltip" title="{{ __('Cancel Request') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">

                                    <div class="col-lg-6 col-md-12">
                                        <div class="border border-2 p-3">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto mb-3 mb-sm-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="theme-avtar badge bg-primary">
                                                            <i class="ti ti-report-money"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <small class="text-muted">{{ __('Total') }}</small>
                                                            <h6 class="m-0">{{ __('Commission Amount') }}</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto text-end">
                                                    <h4 class="m-0">{{ super_currency_format_with_sym(\Auth::user()->commission_amount) }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="border border-2 p-3">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto mb-3 mb-sm-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="theme-avtar badge bg-primary">
                                                            <i class="ti ti-report-money"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <small class="text-muted">{{ __('Paid') }}</small>
                                                            <h6 class="m-0">{{ __('Paid Amount') }}</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto text-end">
                                                    <h4 class="m-0">{{ super_currency_format_with_sym($paidAmount) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="">{{ __('Payout History') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Company Name') }}</th>
                                                <th>{{ __('Requested Date') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Requested Amount') }}</th>
                                                <th>{{ __('Coupon Code') }}</th>
                                                <th>{{ __('Coupon Status') }}</th>
                                                <th>{{ __('Coupon Expiry Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transactionsOrder as $key => $transaction)
                                                <tr>
                                                    <td>{{ ++$key }}</td>
                                                    <td>{{ \Auth::user()->name }}</td>
                                                    <td>{{ $transaction->date }}</td>
                                                    <td>
                                                        @if ($transaction->status == 0)
                                                            <span
                                                                class="status_badge badge bg-danger p-2 px-3">{{ __(\App\Models\TransactionOrder::$status[$transaction->status]) }}</span>
                                                        @elseif($transaction->status == 1)
                                                            <span
                                                                class="status_badge badge bg-warning p-2 px-3">{{ __(\App\Models\TransactionOrder::$status[$transaction->status]) }}</span>
                                                        @elseif($transaction->status == 2)
                                                            <span
                                                                class="status_badge badge bg-primary p-2 px-3">{{ __(\App\Models\TransactionOrder::$status[$transaction->status]) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ super_currency_format_with_sym($transaction->req_amount) }}</td>
                                                    <td>{{ $transaction->coupon_code ?? '-' }}</td>
                                                    <td>
                                                        @if(!empty($coupon) && $coupon->limit == $usedCoupun)
                                                            <span class="p-2 px-3 status_badge badge bg-primary">{{ __('Used') }}</span>
                                                        @elseif(!empty($coupon))
                                                            <span class="p-2 px-3 status_badge badge bg-danger">{{ __('Not Used') }}</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ !empty($transaction->expiry_date) ? company_date_formate($transaction->expiry_date):'-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
