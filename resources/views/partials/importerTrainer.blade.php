<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('post.train.importing.algorithm') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="medical_record">Upload CCDAs to import</label>
                    <input type="file" name="medical_records[]" id="medical_record" multiple required>
                    {{--<p class="help-block">Hint: You can also drop a CSV file on this panel</p>--}}

                    <br>
                    <input type="submit" class="btn btn-success" value="Create Careplan(s)" name="submit">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="{{route('import.ccd.remix')}}" class="btn btn-default">View Imported CCDAs</a>
                </div>
            </form>
        </div>
    </div>
</div>