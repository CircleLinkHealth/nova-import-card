@if(isset($careTeam))
    @foreach($careTeam as $member)
        <div class="col-md-5">
            <p><strong>{{$member->formatted_type}}:</strong> {{$member->formatted_title}}</p>
        </div>

        <div class="col-md-5">
            <div class="radio-inline">
                <input type="checkbox" name="ctmsa[]" id="ctm1sa" value="">
                <label for="ctm1sa"><span> </span>Send Alerts</label>
            </div>
        </div>

        <div class="col-md-2">
            <button id="deleteCareTeamMember-{{$member->id}}"
                    class="btn btn-xs btn-danger problem-delete-btn"
                    v-on:click.stop.prevent="deleteCareTeamMember($index, $problem)">
                    <span>
                        <i class="glyphicon glyphicon-remove"></i>
                    </span>
            </button>
            <button class="btn btn-xs btn-primary problem-edit-btn"
                    v-on:click.stop.prevent="editCareTeamMember($index, $problem)">
            <span>
                <i class="glyphicon glyphicon-pencil"></i>
            </span>
            </button>
        </div>
        <br>
    @endforeach
@endif

@section('scripts')
    <script>
        $("#addNewProviderFAB").click(function (e) {
            $("#addProviderModal").modal();
            e.preventDefault();
            return false;
        });
    </script>
@endsection