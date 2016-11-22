
{{--<style>--}}

    {{--.searchbox-label{--}}
        {{--position: relative;--}}
        {{--z-index: 99;--}}
        {{--top: 20px;--}}
        {{--color: #50b2e2;--}}
        {{--padding-left: 10px;--}}
        {{--font-size: 16px;--}}
    {{--}--}}

{{--</style>--}}

<label class="searchbox-label" for="patients"></label>
<div id="bloodhound">
    <input style="margin-top: -9px; margin-bottom: -41px; width: auto;"
           class="form-control typeahead form-item-spacing form-control" size="50" type="text" name="users"
           autofocus="autofocus" @if(!empty($patient->id)) placeholder="{!!$patient->fullName!!}"> @else
        placeholder="Enter a Patient Name, MRN or DOB (mm-dd-yyyy)">@endif

</div>
<script src="{{ asset('/js/patientsearch.js') }}"></script>
