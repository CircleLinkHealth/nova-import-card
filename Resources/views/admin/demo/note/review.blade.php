@extends('cpm-admin::partials.adminUI')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Send Test Note</div>

                    <a class="btn btn-info" href="{{route('download', [base64_encode($filePath)])}}">Download PDF</a>

                    <div class="panel-body">
                        <form action="{{ route('demo.note.efax') }}" method="post">
                            {{ csrf_field() }}

                            <div class="form-group">
                                {{--<select name="practice_id" class="col-sm-12 form-control select2" required>--}}
                                {{--<option value="" disabled selected>Select Practice</option>--}}
                                {{--@foreach($practices as $practice)--}}
                                {{--<option value="{{$practice->id}}">{{$practice->display_name}}</option>--}}
                                {{--@endforeach--}}
                                {{--</select>--}}
                                <input class="form-control" type="text" name="fax" placeholder="efax number" required>
                            </div>

                            <input type="hidden" name="filePath" value="{{$filePath}}">

                            <div class="form-group">
                                <input type="submit" class="btn btn-default" value="Send Note To Practice via eFax"
                                       name="submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush