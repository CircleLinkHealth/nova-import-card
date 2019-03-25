<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.enrollees.import.medical.records') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group">
                        <select name="practice_id" class="col-sm-12 form-control select2" required>
                            <option value="" disabled selected>Select Practice</option>
                            @foreach(CircleLinkHealth\Customer\Entities\Practice::active()->orderBy('display_name')->get() as $practice)
                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <label for="enrollee_ids">Medical Records (CCDA) IDs</label>
                    <input class="form-control" type="text" name="medical_record_ids" id="medical_record_ids"
                           placeholder="135,1235,123126,123,2452" required>

                    <br>

                    <input type="submit" class="btn btn-default" value="Import" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>
