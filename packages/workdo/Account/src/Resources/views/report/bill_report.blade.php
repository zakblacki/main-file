@extends('layouts.main')
@section('page-title')
    {{ __('Bill Summary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Report') }},
    {{ __('Bill Summary') }}
@endsection
@push('css')
<style>
    .bill_status{
        min-width: 94px;
    }
</style>
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var chartBarOptions = {
                 series: [{
                    name: '{{ __('Bill') }}',
                    data: {!! json_encode($billTotal) !!},

                },{
                    name: '{{ __('Purchase') }}',
                    data: {!! json_encode($purchaseTotal) !!},

                } ],
                chart: {
                    height: 300,
                    type: 'bar',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },

                xaxis: {
                    categories: {!! json_encode($monthList) !!},
                    title: {
                        text: '{{ __('Months') }}'
                    }
                },
                colors: ['#6fd944', '#FF3A6E'],

                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                markers: {
                    size: 4,
                    colors: ['#6fd944', '#FF3A6E'],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                },
                yaxis: {
                    title: {
                        text: '{{ __('Bill / Purchase') }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();
    </script>
    <script src="{{ asset('packages/workdo/Account/src/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }


    </script>
@endpush
@section('page-action')
    <div>
        <a  class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Download') }}">
            <i class="ti ti-download"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" multi-collapse mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.bill.summary'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2">
                                        <div class="btn-box">
                                            {{ Form::label('start_month', __('Start Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('start_month', isset($_GET['start_month']) ? $_GET['start_month'] : date('Y-01'), ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2">
                                        <div class="btn-box">
                                            {{ Form::label('end_month', __('End Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('end_month', isset($_GET['end_month']) ? $_GET['end_month'] : date('Y-12'), ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2">
                                        <div class="btn-box">
                                            {{ Form::label('vendor', __('Vendor'), ['class' => 'form-label']) }}
                                            {{ Form::select('vendor', $vendor, isset($_GET['vendor']) ? $_GET['vendor'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Vendor']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2">
                                        <div class="btn-box">
                                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                            {{ Form::select('status', $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Status']) }}
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a  class="btn btn-sm btn-primary me-1"
                                            onclick="document.getElementById('report_bill_summary').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.bill.summary') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-3">
            <div class="col">
                <input type="hidden"
                    value="{{ $filter['status'] . ' ' . __('Bill') . ' ' . 'Report of' . ' ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] . ' ' . __('of') . ' ' . $filter['vendor'] }}"
                    id="filename">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Report') }} :</h5>
                    <h6 class="report-text mb-0 mt-1">{{ __('Bill Summary') }}</h6>
                </div>
            </div>
            @if ($filter['vendor'] != __('All'))
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h5 class="report-text gray-text mb-0">{{ __('Vendor') }} :</h5>
                        <h6 class="report-text mb-0 mt-1">{{ $filter['vendor'] }}</h6>
                    </div>
                </div>
            @endif
            @if ($filter['status'] != __('All'))
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h5 class="report-text gray-text mb-0">{{ __('Status') }} :</h5>
                        <h6 class="report-text mb-0 mt-1">{{ $filter['status'] }}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Duration') }} :</h5>
                    <h6 class="report-text mb-0 mt-1">{{ $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}</h6>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Total Bill') }}</h5>
                    <h6 class="report-text mb-0">{{ currency_format_with_sym($totalBill) }}</h6>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Total Paid') }}</h5>
                    <h6 class="report-text mb-0">{{ currency_format_with_sym($totalPaidBill) }}</h6>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Total Due') }}</h5>
                    <h6 class="report-text mb-0">{{ currency_format_with_sym($totalDueBill) }}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12" id="bill-container">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item me-1">
                                    <a class="nav-link active" id="profile-tab3" data-bs-toggle="pill" href="#summary"
                                        role="tab" aria-controls="pills-summary"
                                        aria-selected="true">{{ __('Summary') }}</a>
                                </li>
                                <li class="nav-item me-1">
                                    <a class="nav-link" id="contact-tab4" data-bs-toggle="pill" href="#bills"
                                        role="tab" aria-controls="pills-invoice"
                                        aria-selected="false">{{ __('Bills') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contact-tab5" data-bs-toggle="pill" href="#purchase"
                                        role="tab" aria-controls="pills-invoice"
                                        aria-selected="false">{{ __('Purchase') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="table-responsive tab-pane fade fade" id="bills" role="tabpanel"
                                        aria-labelledby="profile-tab3">
                                        <table class="table table-flush" id="report-dataTable">
                                            <thead>
                                                <tr>
                                                    <th> {{ __('Bill') }}</th>
                                                    <th> {{ __('Date') }}</th>
                                                    <th> {{ __('Vendor') }}</th>
                                                    <th> {{ __('Category') }}</th>
                                                    <th> {{ __('Status') }}</th>
                                                    <th> {{ __('Paid Amount') }}</th>
                                                    <th> {{ __('Due Amount') }}</th>
                                                    <th> {{ __('Payment Date') }}</th>
                                                    <th> {{ __('Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($bills as $bill)
                                                    <tr>
                                                        <td class="Id">
                                                            <a href="{{ route('bill.show', \Crypt::encrypt($bill->id)) }}"
                                                                class="btn btn-outline-primary">{{ \Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                                        </td>
                                                        </td>
                                                        <td>{{ company_date_formate($bill->send_date) }}</td>
                                                        <td> {{ !empty($bill->vendor_name) ? $bill->vendor_name : '-' }} </td>
                                                        @if (module_is_active('ProductService'))
                                                            <td>{{ !empty($bill->categories_name) ? $bill->categories_name : '-' }}
                                                            </td>
                                                        @else
                                                            <td>-</td>
                                                        @endif
                                                        <td>
                                                            @if ($bill->status == 0)
                                                                <span
                                                                    class="badge  bg-info p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                            @elseif($bill->status == 1)
                                                                <span
                                                                    class="badge  bg-primary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                            @elseif($bill->status == 2)
                                                                <span
                                                                    class="badge  bg-secondary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                            @elseif($bill->status == 3)
                                                                <span
                                                                    class="badge  bg-warning p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                            @elseif($bill->status == 4)
                                                                <span
                                                                    class="badge  bg-success p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                            @endif
                                                        </td>
                                                        <td> {{ currency_format_with_sym($bill->getTotal() - $bill->getDue()) }}
                                                        </td>
                                                        <td> {{ currency_format_with_sym($bill->getDue()) }}</td>
                                                        <td>{{ !empty($bill->lastpayment_date) ? company_date_formate($bill->lastpayment_date) : '' }}
                                                        </td>
                                                        <td> {{ currency_format_with_sym($bill->getTotal()) }}</td>
                                                    </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive tab-pane fade fade" id="purchase" role="tabpanel"
                                        aria-labelledby="profile-tab3">
                                        <table class="table table-flush" id="report-dataTable">
                                            <thead>
                                                <tr>
                                                    <th> {{ __('Purchase') }}</th>
                                                    <th> {{ __('Date') }}</th>
                                                    <th> {{ __('Vendor') }}</th>
                                                    <th> {{ __('Category') }}</th>
                                                    <th> {{ __('Status') }}</th>
                                                    <th> {{ __('Paid Amount') }}</th>
                                                    <th> {{ __('Due Amount') }}</th>
                                                    <th> {{ __('Payment Date') }}</th>
                                                    <th> {{ __('Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($purchases as $purchase)
                                                    <tr>
                                                        <td class="Id">
                                                            <a href="{{ route('purchases.show', \Crypt::encrypt($purchase->id)) }}"
                                                                class="btn btn-outline-primary">{{ \App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id) }}</a>
                                                        </td>
                                                        </td>
                                                        <td>{{ company_date_formate($purchase->send_date) }}</td>
                                                        @if(!empty($purchase->vender_name))
                                                                <td> {{ (!empty( $purchase->vender_name)?$purchase->vender_name:'') }} </td>
                                                        @elseif(empty($purchase->vender_name))
                                                            <td>{{($purchase->user->name)}}</td>
                                                        @else
                                                            @if(module_is_active('Account'))
                                                                <td> {{ (!empty( $purchase->vender)?$purchase->vender->name:'') }} </td>
                                                            @else
                                                                <td>-</td>
                                                            @endif
                                                        @endif
                                                        @if (module_is_active('ProductService'))
                                                            <td>{{ !empty($purchase->categories_name) ? $purchase->categories_name : '-' }}
                                                            </td>
                                                        @else
                                                            <td>-</td>
                                                        @endif
                                                        <td>
                                                            @if ($purchase->status == 0)
                                                                <span
                                                                    class="badge bg-info p-2 px-3 purchase_status">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                            @elseif($purchase->status == 1)
                                                                <span
                                                                    class="badge bg-primary p-2 px-3 purchase_status">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                            @elseif($purchase->status == 2)
                                                                <span
                                                                    class="badge bg-secondary p-2 px-3 purchase_status">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                            @elseif($purchase->status == 3)
                                                                <span
                                                                    class="badge bg-warning p-2 px-3 purchase_status">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                            @elseif($purchase->status == 4)
                                                                <span
                                                                    class="badge bg-success p-2 px-3 purchase_status">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                            @endif
                                                        </td>
                                                        <td> {{ currency_format_with_sym($purchase->getTotal() - $purchase->getDue()) }}
                                                        </td>
                                                        <td> {{ currency_format_with_sym($purchase->getDue()) }}</td>
                                                        <td>{{ !empty($purchase->lastpayment_date) ? company_date_formate($purchase->lastpayment_date) : '' }}
                                                        </td>
                                                        <td> {{ currency_format_with_sym($purchase->getTotal()) }}</td>
                                                    </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel"
                                        aria-labelledby="profile-tab3">
                                        <div class="scrollbar-inner">
                                            <div id="chart-sales" data-color="primary" data-type="bar"
                                                data-height="300"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
