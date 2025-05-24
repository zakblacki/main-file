@extends('layouts.main')

@section('page-title')
    {{ __('Manage Deals') }} @if ($pipeline)
        - {{ $pipeline->name }}
    @endif
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <style>
        .comp-card {
            height: 140px;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    @permission('deal move')
        @if ($pipeline)
            <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
            <script>
                ! function(a) {
                    "use strict";
                    var t = function() {
                        this.$body = a("body")
                    };
                    t.prototype.init = function() {
                        a('[data-plugin="dragula"]').each(function() {
                            var t = a(this).data("containers"),
                                n = [];
                            if (t)
                                for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                            else n = [a(this)[0]];
                            var r = a(this).data("handleclass");
                            r ? dragula(n, {
                                moves: function(a, t, n) {
                                    return n.classList.contains(r)
                                }
                            }) : dragula(n).on('drop', function(el, target, source, sibling) {

                                var order = [];
                                $("#" + target.id + " > div").each(function() {
                                    order[$(this).index()] = $(this).attr('data-id');
                                });

                                var id = $(el).attr('data-id');

                                var old_status = $("#" + source.id).data('status');
                                var new_status = $("#" + target.id).data('status');
                                var stage_id = $(target).attr('data-id');
                                var pipeline_id = '{{ $pipeline->id }}';

                                $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div")
                                    .length);
                                $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div")
                                    .length);
                                $.ajax({
                                    url: '{{ route('deals.order') }}',
                                    type: 'POST',
                                    data: {
                                        deal_id: id,
                                        stage_id: stage_id,
                                        order: order,
                                        new_status: new_status,
                                        old_status: old_status,
                                        pipeline_id: pipeline_id,
                                        "_token": $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        if (data.success) {
                                            toastrs('success', data.success,'success');
                                        } else {
                                            toastrs('error', data.error,'error');
                                        }
                                    }
                                });
                            });
                        })
                    }, a.Dragula = new t, a.Dragula.Constructor = t
                }(window.jQuery),
                function(a) {
                    "use strict";

                    a.Dragula.init()

                }(window.jQuery);
            </script>
        @endif
    @endpermission
    <script>
        $(document).on("change", "#change-pipeline select[name=default_pipeline_id]", function() {
            $('#change-pipeline').submit();
        })
    </script>


@endpush

@section('page-breadcrumb')
    {{ __('Deals') }}
@endsection


@section('page-action')
    <div class="d-flex flex-wrap">
        @if ($pipeline)
            <div class="col-auto me-3">
                {{ Form::open(['route' => 'deals.change.pipeline', 'id' => 'change-pipeline']) }}
                {{ Form::select('default_pipeline_id', $pipelines, $pipeline->id, ['class' => 'form-control custom-form-select mx-2', 'id' => 'default_pipeline_id']) }}
                {{ Form::close() }}
            </div>
        @endif
        <div class="col-auto pt-2" style="display: inline-table;">
            @stack('addButtonHook')
        </div>
        @permission('deal import')
            <div class="col-auto pt-2">
                <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Deal Import') }}"
                    data-url="{{ route('deal.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                        class="ti ti-file-import"></i>
                </a>
            </div>
        @endpermission

        <div class="col-auto pt-2">
            <a href="{{ route('deals.list') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('List View') }}"
                class="btn btn-sm btn-primary btn-icon me-2"><i class="ti ti-list"></i> </a>
        </div>
        @permission('deal create')
            <div class="col-auto pt-2">
                <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Create Deal') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Deal') }}"
                    data-url="{{ route('deals.create') }}"><i class="ti ti-plus text-white"></i></a>
            </div>
        @endpermission
    </div>
@endsection

@section('content')
    @if ($pipeline)
        <div class="row row-gap">
            <div class="col-xl-3 col-sm-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wap gap-2 align-items-center justify-content-between">
                            <div class="deals-content">
                                <h6 class="m-b-20">{{ __('Total Deals') }}</h6>
                                <h3 class="text-primary mb-0">{{ $cnt_deal['total'] }}</h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-success badge">
                                    <i class="fas fa-rocket text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="deals-content">
                                    <h6 class="m-b-20">{{ __('This Month Total Deals') }}</h6>
                                    <h3 class="text-info mb-0">{{ $cnt_deal['this_month'] }}</h3>
                                </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-info badge">
                                    <i class="fas fa-rocket text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                            <div class="deals-content">
                                <h6 class="m-b-20">{{ __('This Week Total Deals') }}</h6>
                                <h3 class="text-warning mb-0">{{ $cnt_deal['this_week'] }}</h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-warning badge">
                                    <i class="fas fa-rocket text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                            <div class="deals-content">
                                <h6 class="m-b-20">{{ __('Last 30 Days Total Deals') }}</h6>
                                <h3 class="text-danger mb-0">{{ $cnt_deal['last_30days'] }}</h3>
                            </div>
                            <div class="col-auto">
                                <div class="theme-avtar bg-danger badge">
                                    <i class="fas fa-rocket text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @php
                    $stages = $pipeline->dealStages;
                    $json = [];
                    foreach ($stages as $stage) {
                        $json[] = 'task-list-' . $stage->id;
                    }
                @endphp
                <div class="row kanban-wrapper horizontal-scroll-cards pt-4" data-plugin="dragula"
                    data-containers='{!! json_encode($json) !!}'>
                    @foreach ($stages as $stage)
                        @php($deals = $stage->deals())
                        <div class="col" id="progress">
                            <div class="card card-list">
                                <div class="card-header">
                                    <div class="float-end">
                                        <button class="btn btn-sm btn-primary btn-icon count">
                                            {{ count($deals) }}
                                        </button>
                                    </div>
                                    <h4 class="mb-0">{{ $stage->name }}</h4>
                                </div>
                                <div id="task-list-{{ $stage->id }}" data-id="{{ $stage->id }}"
                                    class="card-body kanban-box">
                                    @foreach ($deals as $deal)
                                        <div class="card grid-card" data-id="{{ $deal->id }}">
                                            @php($labels = $deal->labels())
                                                @if ($labels)
                                                    <div class="card-header border-0 pb-3 d-flex align-items-center justify-content-between pt-3 ps-3">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($labels as $label)
                                                            <div class="badge bg-{{ $label->color }} p-2 px-3">
                                                                {{ $label->name }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="card-header border-0 pb-2 position-relative pt-3 ps-3">
                                                @endif
                                                @if (Auth::user()->type != 'client' && Auth::user()->type != 'staff')
                                                    <div class="card-header-right">
                                                        <div class="btn-group card-option">
                                                            @if (!$deal->is_active)
                                                                <div class="btn dropdown-toggle">
                                                                    <a href="#" class="action-item"
                                                                        data-toggle="dropdown" aria-expanded="false"><i
                                                                            class="fas fa-lock"></i></a>
                                                                </div>
                                                            @else
                                                                <button type="button" class="btn dropdown-toggle"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="ti ti-dots-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    @permission('deal edit')
                                                                        <a data-url="{{ URL::to('deals/' . $deal->id . '/labels') }}"
                                                                            data-ajax-popup="true"
                                                                            data-title="{{ __('Labels') }}"
                                                                            class="dropdown-item"><i class="ti ti-copy me-1"></i> {{ __('Labels') }}</a>
                                                                    @endpermission
                                                                    @permission('deal show')
                                                                        @if($deal->is_active)
                                                                            <a href="{{route('deals.show',$deal->id)}}" class="dropdown-item" data-original-title="{{__('View')}}"><i class="ti ti-eye me-1"></i>   {{ __('View') }}</a>
                                                                        @endif
                                                                    @endpermission
                                                                    @permission('deal edit')
                                                                        <a data-url="{{ URL::to('deals/' . $deal->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit') }}"
                                                                            class="dropdown-item"><i class="ti ti-pencil me-1"></i> {{ __('Edit') }}</a>
                                                                    @endpermission
                                                                    @permission('deal delete')
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['deals.destroy', $deal->id],
                                                                            'id' => 'delete-form-' . $deal->id,
                                                                        ]) !!}
                                                                        <a class="dropdown-item show_confirm text-danger"
                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                                            <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                                                        </a>
                                                                        {!! Form::close() !!}
                                                                    @endpermission
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-header border-0 pt-0 pb-0 position-relative">
                                                <h5><a href="@permission('deal show') @if ($deal->is_active) {{ route('deals.show', $deal->id) }} @else # @endif @else # @endpermission"
                                                        class="text-body text-primary">{{ $deal->name }} </a></h5>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <ul class="list-inline mb-0">
                                                        <li class="list-inline-item d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Task"><i
                                                                class="f-16 text-primary ti ti-list me-1"></i>{{ count($deal->tasks) }}/{{ count($deal->complete_tasks) }}
                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        <i class="text-primary ti ti-report-money me-1"></i>
                                                        {{ currency_format_with_sym($deal->price) }}
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-secondary-subtle border-top">
                                                    <ul class="list-inline mb-0">
                                                        <li class="list-inline-item d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Product"><i
                                                                class="f-16 text-primary ti ti-shopping-cart me-1"></i>{{ count($deal->products()) }}
                                                        </li>
                                                        <li class="list-inline-item d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Source"><i
                                                                class="f-16 text-primary ti ti-social me-1"></i>{{ count($deal->sources()) }}
                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        @foreach ($deal->users as $user)
                                                            <img alt="image" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ $user->name }}"
                                                                data-bs-placement="top" aria-label="{{ $user->name }}"
                                                                title="{{ $user->name }}"
                                                                @if ($user->avatar) src="{{ get_file($user->avatar) }}" @else src="{{ get_file('uploads/users-avatar/avatar.png') }}" @endif
                                                                class="rounded-circle " width="25" height="25">
                                                        @endforeach

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
