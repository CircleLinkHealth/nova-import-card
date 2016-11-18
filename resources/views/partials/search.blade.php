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
            /*height: 30px;*/
            /*padding: 8px 12px;*/
            /*font-size: 24px;*/
            /*line-height: 30px;*/
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
            border: 2px solid #63bbe8;
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
            position: absolute !important;
            left:-99px !important;
            max-height: 250px;
            min-height: 220px;
            overflow-y: auto;
            width: 600px;
            margin: 12px 0;
            padding: 8px 0;
            background-color: #fff;
            border: 1px solid #ccc;
            -webkit-border-radius: 8px;
            -moz-border-radius: 8px;
            border-radius: 8px;
            -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            box-shadow: 0 5px 10px rgba(0,0,0,.2);
        }

        .tt-suggestion {
            text-align: left;
            padding: 3px 20px;
            font-size: 16px;
            line-height: 28px;
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
        <label for="patients"></label>
        <div id="bloodhound">
            <input class="typeahead form-item-spacing form-control" size="50" type="text" name="users"
                   autofocus="autofocus" placeholder="Enter MRN, Patient Name or DOB [mm-dd-yyyy]">
        </div>
    <script>
        jQuery(document).ready(function ($) {
            var pat = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('search'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: 'queryPatient?users=%QUERY',
                    wildcard: '%QUERY'
                }
            });

            pat.initialize();

            $('#bloodhound .typeahead').typeahead({
                hint: true,
                highlight: true,
                minLength: 3
            },{
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

            $('#bloodhound .typeahead').on('typeahead:selected', function (e, datum) {
                window.location.href = datum.link;
                datum.val(datum.name);
            });
        });
    </script>
