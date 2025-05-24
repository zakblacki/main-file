{{ Form::open(array('route' => 'feature_store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Form::text('feature_heading',null, ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Description', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::textarea('feature_description', null, ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'feature_des','required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-8">
                <div class="form-group">
                    {{ Form::label('More', __('More Details Link'), ['class' => 'form-label']) }}
                    {{ Form::text('feature_more_details_link',null, ['class' => 'form-control ', 'placeholder' => __('Enter Details Link'),'required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('More Details Link Button Text', __('More Details Link Button Text'), ['class' => 'form-label']) }}
                    {{ Form::text('feature_more_details_button_text',null, ['class' => 'form-control', 'placeholder' => __('Enter Button Text')]) }}
                </div>
            </div>

            <div class="col-md-12">

                <div class="form-group">
                    {{ Form::label('Logo', __('Logo'), ['class' => 'form-label']) }}
                    <input type="file" name="feature_logo" class="form-control"  onchange="document.getElementById('image1').src = window.URL.createObjectURL(this.files[0])" required>
                    <img id="image1" width="25%" class="mt-3">

                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
    </div>
{{ Form::close() }}

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
