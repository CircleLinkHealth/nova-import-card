@include('ehrReportWriter.head')
<body>

    <div>
        <h3>Hi, {{auth()->user()->display_name}}!</h3>
        <p>This tool will ensure the data is in the appropriate format to be ingested by CLH.</p>
    </div>

    <div>
        <h2>Supported Templates</h2>
        <p>The date must be in one of the 3 formats below.</p>
    </div>
    <div class="col-md-12">
        <a class="btn btn-info" href="{{route('download', 'Single_Fields-Sheet1.csv')}}">Download single field CSV Template</a>
        <a class="btn btn-info" href="{{route('download', 'Numbered_Fields-Sheet1.csv')}}">Download many fields CSV Template</a>
        <a class="btn btn-info" href="https://gist.github.com/michalisantoniou6/853740eff3ed58814a89d12c922840c3">Download JSON Template</a>
    </div>

    <div class="col-md-12">
        <h3>In case you chose JSON, here's a tool to help you validate the data structure</h3>
        <div class="col-md-12">
            @include('ehrReportWriter.messages')
        </div>
        <p>You may paste up to 5000 characters.</p>
    </div>

    <div class="col-md-12" >
        <form class="form" action="{{route('report-writer.validate')}}" method="POST">
            {{csrf_field()}}
            <div>
           <textarea rows="15" cols="100" maxlength="5000" class="form-group" name="json" required placeholder="Paste json patient records for validation here..."></textarea>
            </div>
            <div>
                <input class="btn btn-primary" type="submit">
            </div>
        </form>
    </div>




</body>