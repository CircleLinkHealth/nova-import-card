
    <label for="patients"></label>
    <div id="bloodhound">
            <input style="margin-top: -9px; margin-bottom: -41px; width: 32%" class="typeahead form-item-spacing form-control" size="50" type="text" name="users"
                   autofocus="autofocus" @if(!empty($patient->id)) placeholder="{!!$patient->fullName!!}"> @else placeholder="Please enter a Patient Name, an MRN or a Date of Birth (mm-dd-yyyy)">@endif

    </div>
