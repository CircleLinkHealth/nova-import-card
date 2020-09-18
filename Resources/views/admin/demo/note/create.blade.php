@extends('cpm-admin::partials.adminUI')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Send Test Note</div>

                    <div class="panel-body">
                        <form action="{{ route('demo.note.make.pdf') }}" method="post">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="note_body">Note Text</label>
                                <textarea class="form-control" name="note_body" id="note_body"
                                          placeholder="Type text to create sample note." required></textarea>

                                <br>

                                <label for="scale">Scale</label>
                                <input class="form-control" type="number" step="0.1" name="scale" id="scale" value="0.8"
                                       required>

                                <br>

                                <input type="submit" class="btn btn-default" value="Prepare PDF Note" name="submit">
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