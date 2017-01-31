{{--The component's css--}}
<style>
    .modal label {
        font-size: 14px;
    }

    .providerForm {
        padding: 10px;
    }

    .validation-error {
        padding: 3px;
        margin-bottom: 10px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
</style>

{{--Declare any variables the component may need here--}}
{{--In this case I need routes to be able to delete multiple components--}}
<meta name="provider-update-route" content="{{ route('care-team.update', ['id'=>'']) }}">
<meta name="providers-search" content="{{ route('providers.search') }}">

{{--The component's Template--}}
<script type="text/x-template" id="care-person-modal-template">


    <div id="successModal-@{{ care_person.id }}" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Care Team Updated</h4>
                </div>
                <div class="modal-body">
                    <p>The changes you made to @{{ care_person.user.first_name }} @{{ care_person.user.last_name }} will
                        be reflected on the patient's care team.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>

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

                    <form v-form name="addCarePersonForm">
                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="name">Provider Name</label>
                                <div class="col-md-9">
                                    <div class="col-md-6">
                                        <input v-model="care_person.user.first_name" id="first_name"
                                               name="first_name" type="text" placeholder="First"
                                               class="form-control input-md"
                                               v-form-ctrl
                                               required>
                                        <p class="validation-error alert-danger text-right"
                                           v-if="addCarePersonForm.first_name.$error.required">*required</p>
                                    </div>
                                    <div class="col-md-6">
                                        <input v-model="care_person.user.last_name" id="last_name"
                                               name="last_name" type="text" placeholder="Last"
                                               class="form-control input-md"
                                               v-form-ctrl
                                               required>
                                        <p class="validation-error alert-danger text-right"
                                           v-if="addCarePersonForm.last_name.$error.required">*required</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="specialty">Specialty</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">

                                        <select v-select2="care_person.user.provider_info.specialty" id="specialty"
                                                class="cpm-select2" name="specialty" v-form-ctrl require
                                                style="width: 100%;">
                                            <option value=""></option>
                                            @include('partials.specialties')
                                        </select>

                                        <p class="validation-error alert-danger text-right"
                                           v-if="addCarePersonForm.specialty.$error.required">*required</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="address">Address</label>
                                <div class="col-md-9">
                                    <div class="col-md-8">
                                        <input v-model="care_person.user.address" id="address"
                                               name="address"
                                               type="text" placeholder="Line 1"
                                               class="form-control input-md"
                                               required="">
                                    </div>

                                    <div class="col-md-4">
                                        <input v-model="care_person.user.address2" id="address2"
                                               name="address_2"
                                               type="text" placeholder="Line 2"
                                               class="form-control input-md"
                                               required="">
                                    </div>

                                    <br><br>

                                    <div class="col-md-6">
                                        <input v-model="care_person.user.city" id="city" name="city"
                                               type="text" placeholder="City"
                                               class="form-control input-md col-md-6"
                                               required="">
                                    </div>

                                    <div class="col-md-3">
                                        <input v-model="care_person.user.state" id="state" name="state"
                                               type="text" placeholder="State"
                                               class="form-control input-md"
                                               required="">
                                    </div>

                                    <div class="col-md-3">
                                        <input v-model="care_person.user.zip" id="zip" name="zip"
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
                                <label class="col-md-3 control-label" for="phone">Phone Number</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <input v-model="care_person.user.phone_numbers[0].number" id="phone"
                                               name="phone" type="text" placeholder=""
                                               class="form-control input-md"
                                               required="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="practice">Practice Name</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <input v-model="care_person.user.primary_practice.display_name"
                                               id="practice"
                                               name="practice" type="text" placeholder=""
                                               class="form-control input-md"
                                               required="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="email">Email</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <input v-model="care_person.user.email"
                                               id="email"
                                               name="email"
                                               type="email"
                                               placeholder=""
                                               class="form-control input-md"
                                               v-form-ctrl>
                                        <p class="validation-error alert-danger"
                                           v-if="addCarePersonForm.email.$error.email">invalid email.</p>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="type">Clinical Type</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <select v-model="care_person.user.provider_info.qualification" id="type"
                                                name="type" class="form-control type">
                                            <option value="clinical">Clinical (MD, RN or other)</option>
                                            <option value="non-clinical">Non-clinical</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="send_alerts">Send Alerts</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <input v-model="care_person.alert" id="send_alerts"
                                               name="send_alerts" class="form-control type" type="checkbox"
                                               style="display: inline;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row providerForm">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="send_alerts">Type</label>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <input v-model="care_person.formatted_type" id="type"
                                               name="type" class="form-control type" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <meta name="created_by" content="{{auth()->user()->id}}">
                        <meta name="patient_id" content="{{$patient->id}}">

                        <div>
                            <button v-on:click.stop.prevent="updateCarePerson(care_person.id)"
                                    type="submit"
                                    id="editCarePerson"
                                    class="create btn btn-primary"
                                    v-bind:disabled="addCarePersonForm.$invalid"
                            >Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</script>