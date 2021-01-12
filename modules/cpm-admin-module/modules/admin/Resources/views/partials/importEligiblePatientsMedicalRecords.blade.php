<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.enrollees.import.medical.records') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group" data-step="2" data-intro="Select the practice you'd like to import CCDs for.">
                        <select data-position='right' name="practice_id" class="col-sm-12 form-control select2" required>
                            <option value="" disabled selected>Select Practice</option>
                            @foreach(CircleLinkHealth\Customer\Entities\Practice::active()->orderBy('display_name')->get() as $practice)
                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <label for="enrollee_ids">Medical Records (CCDA) IDs</label>
                    <input class="form-control" type="text" name="medical_record_ids" id="medical_record_ids"
                           placeholder="135,1235,123126,123,2452" required data-step="3" data-intro="Enter Medical Records (CCDA) IDs separated by commas like so 135,1235,123126,123,2452. Make sure you do not leave a space after the comma." data-position='right'>

                    <br>

                    <input type="submit" class="btn btn-default" value="Import" name="submit" data-step="4" data-intro="Click Import. Importing CCDs may take a few minutes. You can check the status on this page <a href='{{ route('import.ccd.remix', []) }}' target='_blank'>CCDs To Import</a>">
                </div>
            </form>
        </div>
    </div>
</div>
