@extends('layouts.main')
@section('page-title')
    {{ __('Manage Announcement') }}
@endsection
@section('page-breadcrumb')
{{ __('Announcement') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
<div>
    @permission('announcement create')
        <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Announcement') }}" data-url="{{route('announcement.create')}}" data-toggle="tooltip" title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    {{ $dataTable->table(['width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
<script type="text/javascript">
    $(document).on('change', '#branch_id', function(){
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });
        function getDepartment(branch_id)
        {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }
            $.ajax({
                url: '{{ route('hrm.employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id_span').empty();
                    var select_box = '';

                    $.each(data, function(key, value)
                    {
                        select_box += '<option value="' + key + '">' + value + '</option>';
                    });

                    var select_box1 = '<select class="multi-select choices" id="department_id" data-toggle="select2" required name="department_id[]" multiple="multiple" data-placeholder="{{ __('Select '.(!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'))) }}">'
                                +'<option value="0">All</option>'
                                +select_box
                                +'</select>';

                    $('#department_id_span').html(select_box1);
                    if ($("#department_id").length) {
                        $( $("#department_id") ).each(function( index,element ) {
                            var id = $(element).attr('id');
                            var multipleCancelButton = new Choices(
                                '#'+id, {
                                    removeItemButton: true,
                                }
                            );
                        });
                    }
                }
            });
        }

        $(document).on('change', '#department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{ route('announcement.getemployee') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.employee_id').empty();
                    var emp_selct = ` <select class="form-control  employee_id" name="employee_id[]" id="employee_id"
                                            placeholder="Select Employee" multiple >
                                            </select>`;
                    $('.employee_div').html(emp_selct);

                    $('.employee_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.employee_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#employee_id', {
                        removeItemButton: true,
                    });
                }
            });
        }
</script>
@endpush
