<div id="bloodhound">
    <input id="patient-search-text-box" class="form-control typeahead form-item-spacing" type="text"
           name="users" autofocus="autofocus"
           placeholder="{{ !empty($patient->id) ? $patient->getFullName() : 'Enter a Patient Name, MRN or DOB (mm-dd-yyyy)' }}">
    <div class="search loader" style="display: none;"></div>
</div>

@push('styles')
    <style>

        #bloodhound {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .search.loader {
            border: 5px solid #31C6F9;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
            border-top: 5px solid #555;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            position: absolute;
            right: 20px;
            top: 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @endpush

@push('scripts')
    <script>
        jQuery(document).ready(function ($) {
            var pat = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('search'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{!! route('patients.query') . '?users=%QUERY' !!}',
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
                        '<div class="empty-message">No Patients Found...</div>'
                    ]
                }
            });

            searchBox.on('typeahead:selected', function (e, datum) {
                window.location.href = datum.link;
            });

            searchBox.on('typeahead:asyncrequest', function (e, datum) {
                $('.search.loader').show();
            });

            searchBox.on('typeahead:asynccancel', function (e, datum) {
                $('.search.loader').hide();
            });

            searchBox.on('typeahead:asyncreceive', function (e, datum) {
                $('.search.loader').hide();
            });
        });

    </script>
@endpush