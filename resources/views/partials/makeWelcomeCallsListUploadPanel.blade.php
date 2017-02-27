<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('make.welcome.call.list') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="patient_list">Upload *.csv patient list:</label>
                    <input type="file" name="patient_list" id="patient_list" required>

                    <input type="checkbox" name="filterLastEncounter" id="filterLastEncounter" checked>
                    <label for="">filterLastEncounter</label>

                    <input type="checkbox" name="filterProblems" id="filterProblems" checked>
                    <label for="">filterProblems</label>

                    <input type="checkbox" name="filterInsurance" id="filterInsurance" checked>
                    <label for="">filterInsurance</label>

                    <br>

                    <input type="submit" class="btn btn-default" value="Generate" name="submit">

                    <input type="checkbox" name="createPreEnrollees" id="createPreEnrollees" checked>
                    <label for="">createPreEnrollees</label>

                </div>
            </form>
        </div>
    </div>
</div>