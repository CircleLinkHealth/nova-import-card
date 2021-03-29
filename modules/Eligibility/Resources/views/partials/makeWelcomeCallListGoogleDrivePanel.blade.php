<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('process.eligibility.google.drive') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group">
                        <select name="practiceName" class="col-sm-12 form-control select2" required>
                            <option value="" disabled selected>Select Practice</option>
                            @foreach(CircleLinkHealth\Customer\Entities\Practice::active()->orderBy('display_name')->get() as $practice)
                                <option value="{{$practice->name}}">{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <label for="dir">Drive Dir</label>
                    <input class="" type="text" name="dir" id="dir" required>

                    <br>

                    <label for="file">Only if processing json format: Enter the filename as it appears in Drive (eg `CircleLink.json`)</label>
                    <input class="" type="text" name="file" id="file">

                    <br>

                    <input class="" type="checkbox" name="filterLastEncounter" id="filterLastEncounter">
                    <label for="">Filter Last Encounter</label>

                    <br>

                    <input class="" type="checkbox" name="filterProblems" id="filterProblems" checked>
                    <label for="">Filter Problems</label>

                    <br>

                    <input class="" type="checkbox" name="filterInsurance" id="filterInsurance">
                    <label for="">Filter Insurance</label>

                    <br>

                    <input class="" type="checkbox" name="isPracticePull" id="isPracticePull">
                    <label for="isPracticePull">"Practice CSV Pull" Format</label>

                    <br>

                    <input type="submit" class="btn btn-default" value="Generate" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>
