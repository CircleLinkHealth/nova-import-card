
    <label for="patients"></label>
    <div id="bloodhound">
            <input style="margin-top: -9px; margin-bottom: -41px; width: auto" class="typeahead form-item-spacing form-control" size="50" type="text" name="users"
                   autofocus="autofocus" @if(!empty($patient->id)) placeholder="{!!$patient->fullName!!}"> @else placeholder="Enter a Patient Name, MRN or DOB (mm-dd-yyyy)">@endif

    </div>
