<style>
    .providerForm {
        padding: 10px;
    }
</style>

<div id="success" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Care Person Created!</h4>
            </div>
            <div class="modal-body">
                <p><span id="newProviderName"></span> will be added to the patient's care team.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="addProviderModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Please Edit Provider Details</h4>
            </div>
            <div class="modal-body">
                <form id="form" name="create" method="post"
                      action="{{URL::route('provider.store', array('patientId' => $patient->id))}}">

                    @include('partials.providerFields')

                    <div class="modal-footer">
                        <div class="row result">

                        </div>
                    </div>


                    <div>
                        <button type="submit" id="createCarePerson" class="create btn btn-primary">Add</button>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

<script>

    $("#addNewProviderFAB").click(function (e) {
        $("#addProviderModal").modal();
        e.preventDefault();
        return false;
    });

    $("#addNewProvider").click(function (e) {
        $("#addProviderModal").modal();
        e.preventDefault();
        return false;
    });

</script>





