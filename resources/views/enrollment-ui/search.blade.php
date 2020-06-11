<div id="bloodhound">
    <?php
    $route = route('home');
    ?>
    <div>
            <input onclick="this.select()" id="patient-search-text-box" class="typeahead center-align" type="text"
                                    name="users" autofocus="autofocus"
                                    placeholder="{{'Search Patients...' }}">
            <div class="progress search" style="display: none; margin-left:3.5px; background-color: lightskyblue">
                <div class="indeterminate" style="background-color: deepskyblue"></div>
            </div>
    </div>


    <script>
        jQuery(document).ready(function ($) {
            var pat = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('search'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{!! route('enrollables.enrollment.query') . '?enrollables=%QUERY' !!}',
                    wildcard: '%QUERY'
                }
            });

            pat.initialize();

            var searchBox = $('#patient-search-text-box');

            searchBox.typeahead({
                hint: false,
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
                window.location.href = @json($route) +'#' + datum.id;
                App.$emit('enrollable:load-from-search-bar')
            });

            searchBox.on('typeahead:asyncrequest', function (e, datum) {
                $('.search').show();
            });

            searchBox.on('typeahead:asynccancel', function (e, datum) {
                $('.search').hide();
            });

            searchBox.on('typeahead:asyncreceive', function (e, datum) {
                $('.search').hide();
            });
        });

    </script>
</div>

@push('styles')
    <style>

        #bloodhound {
            position: relative;
            display: inline-block;
        }


    </style>
@endpush
