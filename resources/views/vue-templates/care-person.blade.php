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
                                            <option value="Abdominal Radiology">Abdominal Radiology</option>
                                            <option value="Addiction Psychiatry">Addiction Psychiatry</option>
                                            <option value="Adolescent Medicine">Adolescent Medicine</option>
                                            <option value="Adult Reconstructive Orthopaedics">Adult Reconstructive
                                                Orthopaedics
                                            </option>
                                            <option value="Advanced Heart Failure & Transplant Cardiology">Advanced
                                                Heart Failure & Transplant Cardiology
                                            </option>
                                            <option value="Allergy & Immunology">Allergy & Immunology</option>
                                            <option value="Anesthesiology">Anesthesiology</option>
                                            <option value="Biochemical Genetics">Biochemical Genetics</option>
                                            <option value="Blood Banking - Transfusion Medicine">Blood Banking -
                                                Transfusion Medicine
                                            </option>
                                            <option value="Cardiothoracic Radiology">Cardiothoracic Radiology</option>
                                            <option value="Cardiovascular Disease">Cardiovascular Disease</option>
                                            <option value="Chemical Pathology">Chemical Pathology</option>
                                            <option value="Child & Adolescent Psychiatry">Child & Adolescent
                                                Psychiatry
                                            </option>
                                            <option value="Child Abuse Pediatrics">Child Abuse Pediatrics</option>
                                            <option value="Child Neurology">Child Neurology</option>
                                            <option value="Clinical & Laboratory Immunology">Clinical & Laboratory
                                                Immunology
                                            </option>
                                            <option value="Clinical Cardiac Electrophysiology">Clinical Cardiac
                                                Electrophysiology
                                            </option>
                                            <option value="Clinical Neurophysiology">Clinical Neurophysiology</option>
                                            <option value="Colon & Rectal Surgery">Colon & Rectal Surgery</option>
                                            <option value="Congenital Cardiac Surgery">Congenital Cardiac Surgery
                                            </option>
                                            <option value="Craniofacial Surgery">Craniofacial Surgery</option>
                                            <option value="Critical Care Medicine">Critical Care Medicine</option>
                                            <option value="Critical Care Medicine">Critical Care Medicine</option>
                                            <option value="Cytopathology">Cytopathology</option>
                                            <option value="Dermatology">Dermatology</option>
                                            <option value="Dermatopathology">Dermatopathology</option>
                                            <option value="Developmental-Behavioral Pediatrics">Developmental-Behavioral
                                                Pediatrics
                                            </option>
                                            <option value="Emergency Medicine">Emergency Medicine</option>
                                            <option value="Endocrinology, Diabetes & Metabolism">Endocrinology, Diabetes
                                                & Metabolism
                                            </option>
                                            <option value="Endovascular Surgical Neuroradiology">Endovascular Surgical
                                                Neuroradiology
                                            </option>
                                            <option value="Family Medicine">Family Medicine</option>
                                            <option value="Family Practice">Family Practice</option>
                                            <option value="Female Pelvic Medicine & Reconstructive Surgery">Female
                                                Pelvic Medicine & Reconstructive Surgery
                                            </option>
                                            <option value="Foot & Ankle Orthopaedics">Foot & Ankle Orthopaedics</option>
                                            <option value="Forensic Pathology">Forensic Pathology</option>
                                            <option value="Forensic Psychiatry">Forensic Psychiatry</option>
                                            <option value="Gastroenterology">Gastroenterology</option>
                                            <option value="Geriatric Medicine">Geriatric Medicine</option>
                                            <option value="Geriatric Psychiatry">Geriatric Psychiatry</option>
                                            <option value="Hand Surgery">Hand Surgery</option>
                                            <option value="Hematology">Hematology</option>
                                            <option value="Hematology & Oncology">Hematology & Oncology</option>
                                            <option value="Homecare Nurse">Homecare Nurse</option>
                                            <option value="Infectious Disease">Infectious Disease</option>
                                            <option value="Internal Medicine">Internal Medicine</option>
                                            <option value="Internal Medicine-Pediatrics">Internal Medicine-Pediatrics
                                            </option>
                                            <option value="Interventional Cardiology">Interventional Cardiology</option>
                                            <option value="MD">MD</option>
                                            <option value="Medical Genetics">Medical Genetics</option>
                                            <option value="Medical Microbiology">Medical Microbiology</option>
                                            <option value="Medical Toxicology">Medical Toxicology</option>
                                            <option value="Molecular Genetic Pathology">Molecular Genetic Pathology
                                            </option>
                                            <option value="Muscoskeletal Radiology">Muscoskeletal Radiology</option>
                                            <option value="Musculoskeletal Oncology">Musculoskeletal Oncology</option>
                                            <option value="Neonatal-Perinatal Medicine">Neonatal-Perinatal Medicine
                                            </option>
                                            <option value="Nephrology">Nephrology</option>
                                            <option value="Neurological Surgery">Neurological Surgery</option>
                                            <option value="Neurology">Neurology</option>
                                            <option value="Neuromuscular Medicine">Neuromuscular Medicine</option>
                                            <option value="Neuroradiology">Neuroradiology</option>
                                            <option value="Nuclear Medicine">Nuclear Medicine</option>
                                            <option value="Nuclear Radiology">Nuclear Radiology</option>


                                            <option value="Physical Therapy">Physical Therapy</option>
                                            <option value="Social Worker">Social Worker</option>
                                            <option value="Therapist">Therapist</option>
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