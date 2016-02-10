@extends('partials.providerUI')

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
        .typeahead,
        .tt-query,
        .tt-hint {
          width: 396px;
          height: 30px;
          padding: 8px 12px;
          font-size: 24px;
          line-height: 30px;
          border: 2px solid #ccc;
          -webkit-border-radius: 8px;
             -moz-border-radius: 8px;
                  border-radius: 8px;
          outline: none;
        }

        .typeahead {
          background-color: #fff;
        }

        .typeahead:focus {
          border: 2px solid #0097cf;
        }

        .tt-query {
          -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
             -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
                  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        }

        .tt-hint {
          color: #999
        }

        .tt-menu {
          width: 422px;
          margin: 12px 0;
          padding: 8px 0;
          background-color: #fff;
          border: 1px solid #ccc;
          border: 1px solid rgba(0, 0, 0, 0.2);
          -webkit-border-radius: 8px;
             -moz-border-radius: 8px;
                  border-radius: 8px;
          -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
             -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
                  box-shadow: 0 5px 10px rgba(0,0,0,.2);
        }

        .tt-suggestion {
          padding: 3px 20px;
          font-size: 18px;
          line-height: 24px;
        }

        .tt-suggestion:hover {
          cursor: pointer;
          color: #fff;
          background-color: #0097cf;
        }

        .tt-suggestion.tt-cursor {
          color: #fff;
          background-color: #0097cf;

        }

        .tt-suggestion p {
          margin: 0;
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
                            suggestion: function (data) {
                                //Adam Everyman DOB: 11-25-54 Provider: TESTDRIVE
                                return '<li><a href="' + data.link + '">' + data.name + ' DOB: ' + data.DOB +' Provider: '+ data.program +'</a></li>';
                            },
                            empty: [
                                '<div class="empty-message">', 'No Patients Found...', '</div>'
                            ].join('\n'),
                        }
                    });
            $('#bloodhound .typeahead').on('typeahead:selected', function (e, datum) {
                window.location.href = datum.link
            });
        });
    </script>
@stop