@extends('partials.providerUI')

@section('content')
    <style>
        #bloodhound .empty-message {
            padding: 5px 10px;
            text-align: center;
        }
    </style>
    <div class="container col-md-2 col-md-offset-5">
        <div id="bloodhound">
            <input class="typeahead form-control" type="text" placeholder="Enter Patient Name...">
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var states = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                // `states` is an array of state names defined in "The Basics"
                local: {!! $data !!}
        });

            states.initialize();

            $('#bloodhound .typeahead').typeahead({
                        hint: true,
                        highlight: true,
                        minLength: 3
                    },
                    {
                        name: 'matched-states',
                        displayKey: 'name',
                        source: states,
                        templates: {
                            empty: [
                                '<div class="empty-message">', 'No Patients Found...', '</div>'
                            ].join('\n'),
                            suggestion: function (data) {
                                return '<div><a href="' + data.link + '">' + data.name + '</a></div>';
                            }
                        }
                    });
        });
    </script>
@stop