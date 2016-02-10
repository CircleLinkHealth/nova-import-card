@extends('partials.providerUI')

@section('title', 'Patient Search')
@section('activity', '')

@section('content')
    <style>
        #bloodhound{
            background-color: #fff;
            min-width: 250px;
        }

        #bloodhound li{
            padding: 5px;
        }

        #bloodhound li.active{
            background-color: #eee;
        }
    </style>
    <div class="container">
        <section class="main-form">
            <div class="row">
                <div class="main-form-container col-lg-6 col-lg-offset-3 main-form-container-last"
                     style="border-bottom: 3px solid #50b2e2; padding-bottom: 100px">
                    <div class="row">
                        <div class="main-form-title">
                            Select a Patient
                        </div>
                    </div>

                    <div class="form-item form-item-spacing form-item--first col-sm-12 col-lg-12"
                         style="text-align: center">
                        <label for="patients"></label>

                        <div id="bloodhound">
                            <input class="typeahead form-item-spacing form-control" size="50" type="text"
                                   autofocus="autofocus" placeholder="Enter MRN, Patient Name or DOB [mm-dd-yyyy]">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            var pat = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('search'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                // `states` is an array of state names defined in "The Basics"
                local: {!! $data !!}


            });

            pat.initialize();

            $('#bloodhound .typeahead').typeahead({
                        hint: true,
                        highlight: true,
                        minLength: 3
                    },
                    {
                        name: 'matched-states',
                        source: pat,
                        templates: {
                            empty: [
                                '<div class="empty-message">', 'No Patients Found...', '</div>'
                            ].join('\n'),
                            suggestion: function (data) {
                                //Adam Everyman DOB: 11-25-54 Provider: TESTDRIVE
                                return '<li><a href="' + data.link + '">' + data.name + ' DOB: ' + data.DOB +' Provider: '+ data.program +'</a></li>';
                            }
                        }
                    });
            $('#bloodhound .typeahead').on('typeahead:selected', function (e, datum) {
                window.location.href = datum.link
            });
        });
    </script>
@stop