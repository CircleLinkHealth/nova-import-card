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
            width: 672px !important;
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
            height: 40px;
            font-size: 15px;
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
            left:0px !important;
            max-height: 250px;
            min-height: 220px;
            overflow-y: auto;
            width: 672px !important;
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
                   autofocus="autofocus" placeholder="Please enter a Patient Name, an MRN or a Date of Birth (mm-dd-yyyy)">
        </div>
