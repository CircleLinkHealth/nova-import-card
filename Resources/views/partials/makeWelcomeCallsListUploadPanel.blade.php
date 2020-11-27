<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('make.welcome.call.list') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group">
                        <select name="practice_id" class="col-sm-12 form-control" required>
                            <option value="" disabled selected>Select Practice</option>
                            @foreach(CircleLinkHealth\Customer\Entities\Practice::get()->sortBy('name')->values() as $practice)
                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <label for="patient_list">Upload *.csv patient list:</label>
                    <input type="file" name="patient_list" id="patient_list" required>

                    <input type="checkbox" name="filterLastEncounter" id="filterLastEncounter">
                    <label for="">filterLastEncounter</label>

                    <input type="checkbox" name="filterProblems" id="filterProblems" checked>
                    <label for="">filterProblems</label>

                    <input type="checkbox" name="filterInsurance" id="filterInsurance">
                    <label for="">filterInsurance</label>

                    <br>

                    <input type="submit" class="btn btn-default" value="Generate" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>