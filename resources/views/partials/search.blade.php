<div id="bloodhound" class="col-md-12">
    <input id="patient-search-text-box" class="form-control typeahead form-item-spacing" type="text" style="width: 100% !important;"
           name="users" autofocus="autofocus"
           placeholder="{{ !empty($patient->id) ? $patient->fullName : 'Enter a Patient Name, MRN or DOB (mm-dd-yyyy)' }}">
</div>

@section('scripts')
    <script>
        jQuery(document).ready(function ($) {
            var pat = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('search'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{{route('patients.query')}}',
                    wildcard: '%QUERY'
                }
            });

            pat.initialize();

            var searchBox = $('#patient-search-text-box');

            searchBox.typeahead({
                hint: true,
                highlight: true,
                minLength: 3
            }, {
                source: pat.ttAdapter(),
                // This will be appended to "tt-dataset-" to form the class name of the suggestion menu.
                name: 'User_list',
                // the key from the array we want to display (name,id,email,etc...)
                displayKey: 'hint',
                limit: 50,
                templates: {
                    empty: [
                        '<div class="empty-message">No Patients Found</div>'
                    ]
                }
            });

            searchBox.on('typeahead:selected', function (e, datum) {
                window.location.href = datum.link;
                datum.val(datum.name);
            });
        });

    </script>
@endsection