{{ csrf_field() }}
<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="name">Provider Name</label>
        <div class="col-md-3">
            <input id="first_name" name="first_name" type="text" placeholder="First"
                   class="form-control input-md"
                   required="required">
        </div>
        <div class="col-md-3">
            <input id="last_name" name="last_name" type="text" placeholder="Last"
                   class="form-control input-md"
                   required="required">
        </div>
    </div>
</div>

<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="specialty">Specialty or Service Type</label>
        <div class="col-md-6">
            <input id="specialty" name="specialty" type="text" placeholder=""
                   class="form-control input-md"
                   required="required">
        </div>
    </div>
</div>

<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="address">Address</label>
        <div class="col-md-6">
            <input id="address" name="address" type="text" placeholder=""
                   class="form-control input-md"
                   required="">

        </div>
    </div>
</div>

<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="phone">Phone Number</label>
        <div class="col-md-6">
            <input id="phone" name="phone" type="text" placeholder=""
                   class="form-control input-md"
                   required="">

        </div>
    </div>
</div>

<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="practice">Practice Name</label>
        <div class="col-md-6">
            <input id="practice" name="practice" type="text" placeholder=""
                   class="form-control input-md"
                   required="">

        </div>
    </div>
</div>

<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="email">Email</label>
        <div class="col-md-6">
            <input id="email" name="email" type="email" placeholder=""
                   class="form-control input-md"
                   required="">

        </div>
    </div>
</div>


<div class="row providerForm">
    <div class="form-group">
        <label class="col-md-3 control-label" for="type">Select Type</label>
        <div class="col-md-6">
            <select id="type" name="type" class="form-control type">
                <option value="clinical">Clinical (MD, RN or other)</option>
                <option value="non-clinical">Non-clinical</option>
            </select>
        </div>
    </div>
</div>

<input type="hidden" id="created_by" name="created_by" value="{{auth()->user()->id}}">
<input type="hidden" id="patient_id" name="patient_id" value="{{$patient->id}}">