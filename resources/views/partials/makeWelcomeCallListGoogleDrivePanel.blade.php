<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('process.eligibility.google.drive') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group">
                        <select name="practiceName" class="col-sm-12 form-control" required>
                            <option value="" disabled selected>Select Practice</option>
                            @foreach(App\Practice::get()->sortBy('name')->values() as $practice)
                                <option value="{{$practice->name}}">{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <label for="dir">Drive Dir</label>
                    <input type="text" name="dir" id="dir" required>

                    <br>

                    <input type="checkbox" name="filterLastEncounter" id="filterLastEncounter">
                    <label for="">filterLastEncounter</label>

                    <br>

                    <input type="checkbox" name="filterProblems" id="filterProblems" checked>
                    <label for="">filterProblems</label>

                    <br>

                    <input type="checkbox" name="filterInsurance" id="filterInsurance">
                    <label for="">filterInsurance</label>

                    <br>

                    <input type="checkbox" name="localDir" id="localDir">
                    <label for="localDir">localDir</label>

                    <input type="submit" class="btn btn-default" value="Generate" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>