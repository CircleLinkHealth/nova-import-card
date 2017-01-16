<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('make.welcome.call.list') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="patient_list">Upload *.csv patient list:</label>
                    <input type="file" name="patient_list" id="patient_list" required>
                    <p class="help-block">Hint: You can also drop a CSV file on this panel</p>

                    <input type="submit" class="btn btn-default" value="Make Calls List" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>