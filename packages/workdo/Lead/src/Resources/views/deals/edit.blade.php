{{ Form::model($deal, ['route' => ['deals.update', $deal->id], 'method' => 'PUT','enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'deal','module'=>'Lead'])
        @endif
    </div>
    @if (module_is_active('CustomField') && !$customFields->isEmpty())
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#tab-1" role="tab"
                    aria-controls="pills-home" aria-selected="true">{{ __('Lead Detail') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#tab-2" role="tab"
                    aria-controls="pills-profile" aria-selected="false">{{ __('Custom Fields') }}</a>
            </li>
        </ul>
    @endif
    <div class="tab-content tab-bordered">
        <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
            <div class="row">
                <div class="col-6 form-group">
                    {{ Form::label('name', __('Deal Name'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required','placeholder' => __('Enter Deal Name')]) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                    {{ Form::number('price', null, ['class' => 'form-control','min'=>0]) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('pipeline_id', __('Pipeline'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::select('pipeline_id', $pipelines, null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('stage_id', __('Stage'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::select('stage_id', ['' => __('Select Stage')], null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('sources', __('Sources'), ['class' => 'form-label']) }}
                    {{ Form::select('sources[]', $sources, null, ['class' => 'form-control choices', 'id' => 'choices-multiple', 'multiple' => true]) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('products', __('Products'), ['class' => 'form-label']) }}
                    {{ Form::select('products[]', $products, null, ['class' => 'form-control choices', 'id' => 'choices-multiple1', 'multiple' => true]) }}
                </div>
                <x-mobile name="phone" label="{{__('Phone No')}}" placeholder="{{__('Enter Phone No')}}" required></x-mobile>
                <div class="col-12 form-group">
                    {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                    {{ Form::textarea('notes', null, ['class' => 'form-control summernote' , 'id' => 'deal-edit-summernote']) }}
                </div>
            </div>
        </div>
        @if (module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                <div class="col-md-6">
                    @include('custom-field::formBuilder',['fildedata' => $deal->customField])
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Update') }}</button>
</div>

{{ Form::close() }}


<script>
    var stage_id = '{{ $deal->stage_id }}';

    $(document).ready(function() {
        $("#commonModal select[name=pipeline_id]").trigger('change');
    });

    $(document).on("change", "#commonModal select[name=pipeline_id]", function() {
        $.ajax({
            url: '{{ route('stages.json') }}',
            data: {
                pipeline_id: $(this).val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#stage_id').empty();
                $("#stage_id").append(
                    '<option value="" selected="selected">{{ __('Select Stage') }}</option>');
                $.each(data, function(key, data) {
                    var select = '';
                    if (key == '{{ $deal->stage_id }}') {
                        select = 'selected';
                    }
                    $("#stage_id").append('<option value="' + key + '" ' + select + '>' +
                        data + '</option>');
                });
                $("#stage_id").val(stage_id);

            }
        })
    });
</script>
<script>
    if ($(".summernote").length > 0) {
        $('.summernote').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                ['list', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'unlink']],
            ],
            height: 200,
        });
    }
</script>
