@extends('ehrReportWriter.head')
@section('content')

    <div class="container">
        <div class="col-md-12" style="margin: 15px">
            @include('ehrReportWriter.messages')
        </div>
        <div class="col-md-12">
            <h3>Process file from "My Google Drive Folder" for eligibility</h3>
            <p>If you've just uploaded a file to your Google Drive folder, please refresh this page to see the file.</p>
        </div>
        <div class="col-md-12">
            <form class="form" action="{{route('report-writer.submit')}}" method="POST">
                {{csrf_field()}}
                <div class="col-md-12">
                    <ul class="form-group">
                        @foreach($files as $key => $file)
                            <input type="checkbox" name="googleDriveFiles[{{$key}}][path]" value="{{$file['path']}}"> {{$file['name']}}
                            <input type="hidden" name="googleDriveFiles[{{$key}}][ext]" value="{{$file['extension']}}">
                            <input type="hidden" name="googleDriveFiles[{{$key}}][name]" value="{{$file['name']}}">
                            <br/>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-12 form-group">
                    <select class="select2" name="practice_id">
                        <option value="{{null}}">Select Practice</option>
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


