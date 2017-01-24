<script type="text/x-template" id="care-team-template">

    <meta name="provider-destroy-route" content="{{ route('provider.destroy', ['id'=>'']) }}">

    <ul class="col-xs-12">
        <li v-for="member in careTeamCollection">
            <div class="col-md-5">
                <p style="margin-left: -10px;"><strong>@{{member.formatted_type}}: </strong>@{{member.formatted_title}}
                </p>
            </div>

            <div class="col-md-5">
                <div class="radio-inline">
                    <input type="checkbox" name="ctmsa[]" id="ctm1sa" value="">
                    <label for="ctm1sa"><span></span>Send Alerts</label>
                </div>
            </div>

            <div class="col-md-2">
                <button id="deleteCareTeamMember-@{{member.id}}"
                        class="btn btn-xs btn-danger problem-delete-btn"
                        v-on:click.stop.prevent="deleteCareTeamMember(member.id, $index)">
                    <span>
                        <i class="glyphicon glyphicon-remove"></i>
                    </span>
                </button>
                <button class="btn btn-xs btn-primary problem-edit-btn"
                        v-on:click.stop.prevent="editCareTeamMember(member.id, $index)">
            <span>
                <i class="glyphicon glyphicon-pencil"></i>
            </span>
                </button>
            </div>
            <br>
        </li>
    </ul>
</script>

<care-team-container></care-team-container>

@section('scripts')
    <script src="/js/view-care-plan.js"></script>
    <script>
        $("#addNewProviderFAB").click(function (e) {
            $("#addProviderModal").modal();
            e.preventDefault();
            return false;
        });
    </script>
@endsection