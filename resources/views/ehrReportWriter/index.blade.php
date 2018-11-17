@extends('ehrReportWriter.head')
@section('content')

    <div class="container">
        <div class="col-md-12">
            <h3>Hi, {{auth()->user()->display_name}}!</h3>
            <p>This tool will ensure that the data is in the appropriate format to be ingested by CLH.</p>
        </div>
        <div class="col-md-12">
            @include('ehrReportWriter.messages')
        </div>
        <div class="col-md-12">
            <h2>Supported Templates</h2>
            <p>The date must be in one of the 3 formats below.</p>
        </div>
        <div class="col-md-12">
            <a class="btn btn-info" href="{{route('download', 'Single_Fields-Sheet1.csv')}}">Download single field CSV
                Template</a>
            <a class="btn btn-info" href="{{route('download', 'Numbered_Fields-Sheet1.csv')}}">Download many fields CSV
                Template</a>
            <a class="btn btn-info" href="https://gist.github.com/michalisantoniou6/853740eff3ed58814a89d12c922840c3">Download
                JSON Template</a>
        </div>

        <div class="col-md-12">
            <h3>In case you chose JSON, here's a tool to help you validate the data structure</h3>
            <p>You may paste up to 5000 characters.</p>
        </div>

        <div class="col-md-12">
            <form class="form" action="{{route('report-writer.validate')}}" method="POST">
                {{csrf_field()}}
                <div>
                    <textarea rows="15" cols="100" maxlength="5000" class="form-group" name="json" required
                              placeholder="Paste json patient records for validation here...

(Make sure each patient JSON row is inserted as such: [ row ,row , (etc)]"></textarea>
                </div>
                <div>
                    <input class="btn btn-primary" type="submit">
                </div>
            </form>
        </div>
    </div>
    <hr>
    <div class="container">
        <div class="col-md-12">
            <h3>Submit file for CLH review (Under Construction)</h3>
            <p>Here's a list of files you've uploaded to your designated Google Drive location. Please select one to review by CLH.</p>
        </div>
        <div class="col-md-12">
            <form class="form" action="{{route('report-writer.submit')}}" method="POST">
                {{csrf_field()}}
                <div class="col-md-12">
                    <ul class="form-group">
                        @foreach($files as $key => $file)
                            <input type="checkbox" name="{{$key}}[path]" value="{{$file['path']}}"> {{$file['name']}}
                            <input type="hidden" name="{{$key}}[ext]" value="{{$file['extension']}}">
                            <input type="hidden" name="{{$key}}[name]" value="{{$file['name']}}">
                            <br/>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-12 form-group">
                    <select class="select2" name="practice_id">
                        <option value="{{0}}">Select Practice</option>
                        @foreach($practices as $practice)
                            <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                        @endforeach
                    </select>
                </div>
                <br>
                <div class="col-md-12 form-group">
                    <input type="checkbox" name="filterProblems"> Filter Problems
                    <input type="checkbox" name="filterInsurance"> Filter Insurances
                    <input type="checkbox" name="filterLastEncounter"> Filter Last Encounter <br>
                </div>

                <input type="submit" class="btn btn-primary form-group" value="Review Batch">
            </form>
        </div>
    </div>

@endsection


