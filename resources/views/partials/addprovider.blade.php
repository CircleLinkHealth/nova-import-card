<script>
    $(document).ready(function () {
        $("#addNewProvider").click(function (e) {
            $("#addProvider").modal();
            e.preventDefault();
            return false;
        });
    });
</script>

<style>
    .providerForm {
        padding: 10px;

    }
</style>

<div id="addProvider" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Please Add Provider Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form>
                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-6 control-label" for="name">Provider Name</label>
                                <div class="col-md-3">
                                    <input id="name" name="name" type="text" placeholder=""
                                           class="form-control input-md"
                                           required="">
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="speciality">Speciality</label>
                                <div class="col-md-6">
                                    <input id="speciality" name="speciality" type="text" placeholder=""
                                           class="form-control input-md"
                                           required="">
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="address">Address</label>
                                <div class="col-md-6">
                                    <input id="address" name="address" type="text" placeholder=""
                                           class="form-control input-md"
                                           required="">

                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="phone">Address</label>
                                <div class="col-md-6">
                                    <input id="phone" name="phone" type="number" placeholder=""
                                           class="form-control input-md"
                                           required="">

                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="email">Address</label>
                                <div class="col-md-6">
                                    <input id="email" name="email" type="email" placeholder=""
                                           class="form-control input-md"
                                           required="">

                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="email">Address</label>
                                <div class="col-md-6">
                                    <input id="email" name="email" type="email" placeholder=""
                                           class="form-control input-md"
                                           required="">

                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="type">Select Type</label>
                                <div class="col-md-6">
                                    <select id="type" name="type" class="form-control">
                                        <option value="clinical">Clinical (MD, RN or other clinician)</option>
                                        <option value="non-clinical">Non-clinical</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <div class="row">

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>