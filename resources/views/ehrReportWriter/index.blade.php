@include('ehrReportWriter.head')
<body>
<div style="margin-left: 20px">
    <div>
        <h3>Hi, {{auth()->user()->display_name}}!</h3>
        <p>This tool will ensure the data is in the appropriate format to be ingested by CLH.</p>
    </div>

    <div>
        <h2>Supported Templates</h2>
        <p>The date must be in one of the 3 formats below.</p>
    </div>
    <div class="col-md-12">
        <form class="">
            <div class="col-md-4 info">
                <input type="button" value="CSV 1">
            </div>
            <div class="col-md-4">
                <input type="button" value="CSV 1">
            </div>
            <div class="col-md-4">
                <input type="button" value="CSV 1">
            </div>
        </form>
    </div>

    <div class="col-md-12">
        <h3>In case you chose JSON, here's a tool to help you validate the data structure</h3>
        <p>You may paste up to 5000 characters.</p>
    </div>

    <div>
        <form>
            <div>
           <textarea>
            Paste json patient records for validation here...
           </textarea>
            </div>
            <div>
                <input type="submit">

            </div>
        </form>
    </div>
</div>


</body>