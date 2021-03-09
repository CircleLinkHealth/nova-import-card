@extends('customer::provider.layouts.dashboard')

@section('title', 'Manage Enrollment')

@section('module')
    @include('core::partials.errors.errors')

    <div class="container">
        {!! Form::open(['url' => route('provider.dashboard.store.enrollment', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id' => 'edit-enrollment-form']) !!}

        <div class="row">
            <div class="input-field col s12">
                <h5>Tips</h5>
                <br/>
                <textarea id="tips" name="tips" class="validate summer-note" style="display: none;">{{$tips}}</textarea>
            </div>
        </div>

        <button class="btn blue waves-effect waves-light col s12"
                id="update-enrollment"
                form="edit-enrollment-form">
            Update Enrollment Tips
        </button>

        {!! Form::close() !!}

    </div>
@endsection

@push('styles')
    <style>
        .summer-note {

        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js" integrity="sha512-d0iS+vsW7aSSMaXWJjU5qsef6Lm+++lC4HHS/Va/M1fheKsotZ/WgrL58XLxSYPHnPcTxnZlANPGEZGtHVsbXg==" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {

            $('.summer-note').show().summernote({
                height: 300,                 // set editor height
                minHeight: null,             // set minimum height of editor
                maxHeight: null,             // set maximum height of editor
                focus: false,                  // set focus to editable area after initializing summernote
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ]
            });

            @if(\Session::has('message'))
            Materialize.toast('{{\Session::get('message')}}', 4000)
            @endif
        });
    </script>
@endpush