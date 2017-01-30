{{--The component's css--}}
<style>
    label {
        font-size: 14px;
    }

    .providerForm {
        padding: 10px;
    }

</style>

{{--Declare any variables the component may need here--}}
{{--In this case I need routes to be able to delete multiple components--}}
<meta name="provider-destroy-route" content="{{ route('care-team.destroy', ['id'=>'']) }}">
<meta name="provider-update-route" content="{{ route('care-team.update', ['id'=>'']) }}">
<meta name="providers-search" content="{{ route('providers.search') }}">

{{--The component's Template--}}
<script type="text/x-template" id="care-person-template">

    <div v-show="care_person.user.first_name && care_person.user.last_name">
        <div class="col-md-7">
            <p style="margin-left: -10px;">
                <strong>@{{care_person.formatted_type}}
                    : </strong>@{{care_person.user.first_name}} @{{care_person.user.last_name}}
                <em>@{{care_person.user.provider_info.specialty}}</em>
            </p>
        </div>

        <div class="col-md-3">
            <p v-if="care_person.alert">Receives Alerts</p>
        </div>

        <div class="col-md-2">
            <button id="deleteCareTeamMember-@{{care_person.id}}"
                    class="btn btn-xs btn-danger problem-delete-btn"
                    v-on:click.stop.prevent="deleteCareTeamMember(care_person.id)">
                    <span>
                        <i class="glyphicon glyphicon-remove"></i>
                    </span>
            </button>
            <button class="btn btn-xs btn-primary problem-edit-btn"
                    v-on:click.stop.prevent="editCareTeamMember(care_person.id)">
            <span>
                <i class="glyphicon glyphicon-pencil"></i>
            </span>
            </button>
        </div>
    </div>

    <div id="editCareTeamModal-@{{ care_person.id }}" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Provider Details</h4>
                </div>
                <div class="modal-body">

                    <div class="row providerForm">
                        <search-providers v-if="!care_person.user.id"
                                          v-bind:first_name="care_person.user.first_name"
                                          v-bind:last_name="care_person.user.last_name"
                        ></search-providers>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="name">Provider Name</label>
                            <div class="col-md-9">
                                <div class="col-md-6">
                                    <input v-model="care_person.user.first_name" id="popup_first_name"
                                           name="popup_first_name" type="text" placeholder="First"
                                           class="form-control input-md"
                                           required="required">
                                </div>
                                <div class="col-md-6">
                                    <input v-model="care_person.user.last_name" id="popup_last_name"
                                           name="popup_last_name" type="text" placeholder="Last"
                                           class="form-control input-md"
                                           required="required">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_specialty">Specialty</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.user.provider_info.specialty" id="popup_specialty"
                                           name="popup_specialty"
                                           type="text" placeholder=""
                                           class="form-control input-md"
                                           required="required">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_address">Address</label>
                            <div class="col-md-9">
                                <div class="col-md-8">
                                    <input v-model="care_person.user.address" id="popup_address"
                                           name="popup_address"
                                           type="text" placeholder="Line 1"
                                           class="form-control input-md"
                                           required="">
                                </div>

                                <div class="col-md-4">
                                    <input v-model="care_person.user.address2" id="popup_address2"
                                           name="popup_address_2"
                                           type="text" placeholder="Line 2"
                                           class="form-control input-md"
                                           required="">
                                </div>

                                <br><br>

                                <div class="col-md-6">
                                    <input v-model="care_person.user.city" id="popup_city" name="popup_city"
                                           type="text" placeholder="City"
                                           class="form-control input-md col-md-6"
                                           required="">
                                </div>

                                <div class="col-md-3">
                                    <input v-model="care_person.user.state" id="popup_state" name="popup_state"
                                           type="text" placeholder="State"
                                           class="form-control input-md"
                                           required="">
                                </div>

                                <div class="col-md-3">
                                    <input v-model="care_person.user.zip" id="popup_zip" name="popup_zip"
                                           type="text" placeholder="Zip"
                                           class="form-control input-md"
                                           required="">
                                </div>

                                <br><br>

                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_phone">Phone Number</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.user.phone_numbers[0].number" id="popup_phone"
                                           name="popup_phone" type="text" placeholder=""
                                           class="form-control input-md"
                                           required="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_practice">Practice Name</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.user.primary_practice.display_name"
                                           id="popup_practice"
                                           name="popup_practice" type="text" placeholder=""
                                           class="form-control input-md"
                                           required="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_email">Email</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.user.email" id="popup_email" name="popup_email"
                                           type="email" placeholder=""
                                           class="form-control input-md"
                                           required="">
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_type">Select Type</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <select v-model="care_person.user.provider_info.qualification" id="popup_type"
                                            name="popup_type" class="form-control type">
                                        <option value="clinical">Clinical (MD, RN or other)</option>
                                        <option value="non-clinical">Non-clinical</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_send_alerts">Send Alerts</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.alert" id="popup_send_alerts"
                                           name="popup_send_alerts" class="form-control type" type="checkbox"
                                           style="display: inline;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="popup_send_alerts">Type</label>
                            <div class="col-md-9">
                                <div class="col-md-12">
                                    <input v-model="care_person.formatted_type" id="popup_type"
                                           name="popup_type" class="form-control type" type="text">
                                </div>
                            </div>
                        </div>
                    </div>

                    <meta name="popup_created_by" content="{{auth()->user()->id}}">
                    <meta name="popup_patient_id" content="{{$patient->id}}">

                    <div>
                        <button v-on:click="updateCareTeamMember(care_person.id)" type="submit"
                                id="editCarePerson" class="create btn btn-primary">Save
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</script>