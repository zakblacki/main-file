@extends('layouts.main')
@section('page-title')
    {{ __('Manage Loan Option') }}
@endsection
@section('page-breadcrumb')
{{ __('Loan Option') }}
@endsection
@section('page-action')
<div>
    @permission('loanoption create')
        <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Loan Option') }}" data-url="{{route('loanoption.create')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-3">
        @include('hrm::layouts.hrm_setup')
    </div>
    <div class="col-sm-9">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 " >
                        <thead>
                            <tr>
                                <th>{{ __('Loan Option') }}</th>
                                @if (Laratrust::hasPermission('loanoption edit') || Laratrust::hasPermission('loanoption delete'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loanoptions as $loanoption)
                            <tr>
                                <td>{{ $loanoption->name }}</td>
                                @if (Laratrust::hasPermission('loanoption edit') || Laratrust::hasPermission('loanoption delete'))
                                    <td class="Action">
                                        <span>
                                            @permission('allowanceoption edit')
                                            <div class="action-btn me-2">
                                                <a  class="mx-3 btn bg-info btn-sm  align-items-center"
                                                    data-url="{{ route('loanoption.edit', $loanoption->id) }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                    data-title="{{ __('Edit Loan Option') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            @endpermission
                                            @permission('allowanceoption delete')
                                            <div class="action-btn">
                                                {{Form::open(array('route'=>array('loanoption.destroy', $loanoption->id),'class' => 'm-0'))}}
                                                @method('DELETE')
                                                    <a
                                                        class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-bs-original-title="Delete"
                                                        aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$loanoption->id}}"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                {{Form::close()}}
                                            </div>
                                            @endpermission
                                        </span>
                                    </td>
                                @endif
                            </tr>
                            @empty
                            @include('layouts.nodatafound')
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

