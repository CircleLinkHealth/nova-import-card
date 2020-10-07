<template>
    <div class="phone-numbers col-sm-12">
        <div class="input-group">
            <span v-if="this.error !== ''" class="help-block" style="color: red">{{this.error}}</span>
            <h5 v-if="!loading && shouldDisplayNumberToCallText" style="padding-left: 4px; color: #50b2e2;">Select a number to call</h5>

            <div v-if="hasManyPrimaryNumbers" class="alert alert-warning" role="alert">
                It seems that there are more than one primary numbers. Please choose the correct one.
            </div>

            <template v-for="(number, index) in patientPhoneNumbers">
                <div class="numbers">
                    <div v-if="callEnabled && number.number !== ''" style="margin-top: 7px;">
                        <input name="isPrimary"
                               class="to-call"
                               style="margin-left: 20px;"
                               @click="selectedNumber(number.number)"
                               type="radio"
                               v-model="selectedNumberToCall">
                    </div>

                    <div v-if="shouldShowPhoneTextBox(number)" style="display: inline-flex;">
                        <div class="types">
                            <input name="type"
                                   class="form-control phone-type"
                                   :class="{bgColor : numberIsPrimary(number)}"
                                   type="text"
                                   :value="number.type"
                                   :disabled="true"/>
                        </div>

                        <span class="input-group-addon plus-one">+1</span>
                        <input name="number"
                               class="form-control phone-number"
                               :class="{bgColor : numberIsPrimary(number)}"
                               type="tel"
                               :value="number.number"
                               :disabled="true"/>
                    </div>

                    <button v-if="shouldShowMakePrimary(number)"
                            class="btn btn-success btn-sm update-primaryNumber"
                            type="button"
                            style="display: inline;"
                            @click="updatePrimaryPhone(number.phoneNumberId)"
                            :disabled="disableMakePrimary(number)">
                        Make primary
                    </button>

                    <button v-if="number.isPrimary === false && number.number.length !== 0"
                            type="button"
                            class="btn btn-danger btn-sm remove-phone"
                            title="Delete Phone Number"
                            @click="deletePhone(number)"
                            :disabled="loading">
                        Delete
                    </button>
                </div>
                <br>
            </template>

            <div v-for="(input, index) in newInputs"
                 :class="paddingLeft">
                <div style="padding-right: 14px; margin-left: -10px;">
                    <div class="numbers">
                        <div class="types">
                       <v-select id="numberType"
                                 :class="{borderColor : phoneTypeIsRequired()}"
                                 v-model="newPhoneType"
                                 :options="phoneTypesFiltered">
                       </v-select>
                   </div>


                <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                <input name="number"
                       class="form-control phone-number"
                       :maxlength="maxNumberLength()"
                       type="tel"
                       title="10-digit US Phone Number"
                       :placeholder="input.placeholder"
                       v-model="newPhoneNumber"
                       :disabled="loading"/>

                <button v-if="!loading"
                   class="btn btn-sm remove-input"
                        type="button"
                        title="Remove extra field"
                        @click="removeInputField(index)">
                    Cancel
                </button>


                        <button v-if="addNewFieldClicked"
                                class="btn btn-sm save-number"
                                style="display: inline;"
                                type="button"
                                @click="addNewNumber"
                                :disabled="disableSaveButton">
                            {{setSaveBtnText}}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="! loading" class="helpers">
                <div v-if="phoneTypeIsRequired()">
                    <span class="help-block"
                          title="Missing agent phone number type"
                          style="color: #ff6565; font-size: 15px; cursor: pointer">
               Please choose phone number type.
            </span>
                </div>
            </div>

            <button v-if="!loading && this.newInputs.length === 0"
                    class="add-new-number"
                    title="Add Phone Number"
                    type="button"
                    @click="addPhoneField()">
                <i class="fa fa-fw fa-plus"></i>
                Add phone number
            </button>

            <div v-if="shouldShowAgentContactComponent">
                <edit-patient-agent-contact ref="editPatientAgentContact"
                                                :user-id="userId"
                                                :call-enabled="callEnabled"
                                                :alt-contact="agentContactDetails[0]">
                </edit-patient-agent-contact>
            </div>
        </div>

        <div style="margin-left: 7px;">
            <loader v-if="loading"></loader>
        </div>
    </div>

</template>


<script>
    import LoaderComponent from "./loader";
    import axios from "../bootstrap-axios";
    import EventBus from '../admin/time-tracker/comps/event-bus'
    import CallNumber from "./call-number";
    import EditPatientAgentContact from "./edit-patient-agent-contact";
    import VueSelect from "vue-select";
    import {mapActions} from 'vuex';
    import {addNotification} from '../../../../resources/assets/js/store/actions.js';

    const agent = 'agent';

    export default {
        name: "edit-patient-number",

        components: {
            'loader': LoaderComponent,
            'call-number':CallNumber,
            'edit-patient-agent-contact':EditPatientAgentContact,
            'v-select': VueSelect
        },

        props: [
            'userId',
            'callEnabled',
            'allowNonUsPhones',
            'error',
        ],

        data(){
            return {
                hasManyPrimaryNumbers:false,
                loading:false,
                patientPhoneNumbers:[],
                newPhoneType:'',
                newPhoneNumber:'',
                newInputs:[],
                phoneTypes:[],
                makeNewNumberPrimary:false,
                primaryNumber:'',
                selectedNumberToCall:'',
                phoneTypesFiltered:[],
                agentContactDetails:[
                    {
                        agentEmail:'',
                        agentName:'',
                        agentRelationship:'',
                        agentTelephone:[],
                    }
                ],
            }
        },

        computed:{
            paddingLeft(){
                return this.callEnabled ? 'extraInputs' : 'shortPadding';
            },

            shouldShowAgentContactComponent(){
                return this.agentNumberIsSet || (!this.loading && this.callEnabled);
            },

            shouldDisplayAgentDetailsText(){
                 return this.callEnabled
                     && this.agentContactDetails[0].agentRelationship.length !==0
                     && this.agentContactDetails[0].agentName.length !==0;
            },

            shouldDisplayNumberToCallText(){
                return this.callEnabled && ! this.emptyPatientPhones;
            },

            allowAddingNewNumber(){
                const existingNumbers = this.patientPhoneNumbers.filter(number=>number.number.length !== 0
                && number.type !== 'Agent');

                return !this.loading && this.newInputs.length === 0
                    && existingNumbers.length < this.phoneTypes.length;
            },

            disableSaveButton(){
                return this.loading
                    || this.newPhoneType === null
                    || isNaN(this.newPhoneNumber.toString())
                    || this.newPhoneNumber.toString().length !== this.maxNumberLength();

            },

            emptyPatientPhones(){
                return this.patientPhoneNumbers.length === 0;
            },

            setSaveBtnText(){
                if(this.makeNewNumberPrimary || this.emptyPatientPhones){
                    return'Save & Make Primary';
                }

                if (this.newNumberIsAgent){
                    return "Save agent number";
                }

                return "Add Number";
            },

            newNumberIsAgent(){
                if (this.newPhoneType === null){
                    return false;
                }
                return this.newPhoneType.toLowerCase() === agent;
            },

            addNewFieldClicked(){
                return this.newInputs.length > 0;
            },

            agentNumberIsSet(){
                return this.patientPhoneNumbers.filter(number=>number.number.length !== 0
                    && number.type.toLowerCase() === agent).length !== 0;
            },
        },

        methods: Object.assign(mapActions(['addNotification']), {

            shouldShowPhoneTextBox(number){
                if(this.hasManyPrimaryNumbers){
                    return true;
                }

                return number.number.length !== 0;
            },

            disableMakePrimary(number){
                if(this.hasManyPrimaryNumbers){
                    return false;
                }

                return number.isPrimary || this.loading;
            },

            maxNumberLength(){
                if (this.allowNonUsPhones){
                    return 12;
                }

                return 10;
            },

            phoneTypeIsRequired(){
                if (this.newPhoneType === null){
                    return true;
                }
                return this.newPhoneNumber.length === this.maxNumberLength() && this.newPhoneType.length === 0;
            },

            shouldShowMakePrimary(number){
                if(number.type === "Agent"){
                    return false;
                }

                if(this.hasManyPrimaryNumbers){
                    return true;
                }

                return number.type.toLowerCase() !== agent && number.isPrimary === false;
            },

            filterOutSavedPhoneTypes(){
                let existingPhoneNumbersTypes = [];
                let phoneTypesFiltered = [];
                Object.keys(this.patientPhoneNumbers).forEach(numberKey => {
                    existingPhoneNumbersTypes.push(this.patientPhoneNumbers[numberKey].type);
                });

                Object.keys(this.phoneTypes).forEach(typeKey => {
                    const numberType = this.phoneTypes[typeKey];
                    if(! existingPhoneNumbersTypes.includes(numberType)){
                        phoneTypesFiltered.push(numberType);
                    }
                });

                this.phoneTypesFiltered = [];
                this.phoneTypesFiltered.push(...phoneTypesFiltered);
            },
            emitPrimaryNumber(){
                const primaryNumber =  this.patientPhoneNumbers.filter(n=>n.isPrimary).map(function (phone) {
                    return phone.number;
                });

                if(primaryNumber.length !== 0){
                    this.primaryNumber =  primaryNumber[0];
                    EventBus.$emit("selectedNumber:toCall", this.primaryNumber);
                }

            },

            selectedNumber(number){
                EventBus.$emit("selectedNumber:toCall", number);
            },

            numberIsPrimary(number){
                return number.isPrimary;
            },

            updatePrimaryPhone(phoneNumberId){
                this.loading = true;
                axios.post('/manage-patients/mark/primary-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                }).then((response => {
                    this.getPatientPhoneNumbers();
                })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },

            resetData(){
                this.patientPhoneNumbers = [];
                this.phoneTypes = [];
                this.newInputs = [];
                this.makeNewNumberPrimary = false;
            },

            setHasManyPrimaryNumbers(primaryNumbersCount){
                return primaryNumbersCount > 1;
            },

            getPatientPhoneNumbers(){
                this.loading = true;
                this.resetData();
                axios.post('/manage-patients/get-phones', {
                    patientUserId:this.userId,
                    requestIsFromCallPage:this.callEnabled,
                })
                    .then((response => {
                        this.patientPhoneNumbers.push(...response.data.phoneNumbers);
                        this.phoneTypes.push(...response.data.phoneTypes);
                        this.hasManyPrimaryNumbers = this.setHasManyPrimaryNumbers(response.data.primaryPhoneNumbersCount)
                        if (response.data.agentContactFields.length !== 0){
                            const agentDetails = response.data.agentContactFields;
                            this.agentContactDetails[0].agentEmail = agentDetails.agentEmail;
                            this.agentContactDetails[0].agentName = agentDetails.agentName;
                            this.agentContactDetails[0].agentRelationship = agentDetails.agentRelationship;
                            this.agentContactDetails[0].agentTelephone = agentDetails.agentTelephone;
                            this.initialAgentPhoneSavedInDB = agentDetails.agentTelephone.number;
                            this.initialAgentEmailSavedInDB = agentDetails.agentEmail;
                            this.initialAgentRelationshipSavedInDB = agentDetails.agentRelationship;
                            this.initialAgentNameSavedInDB = agentDetails.agentName;
                        }
                        this.emitPrimaryNumber();
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },

            addPhoneField(){
                if (! this.allowAddingNewNumber){
                    this.warningFlashNotification("Please remove one phone number in order to add a new one");
                    return;
                }

                if (this.newInputs.length > 0) {
                    this.warningFlashNotification("Please save the existing field first");
                    return;
                }
                this.newPhoneNumber = '';
                this.newPhoneType = '';
                this.filterOutSavedPhoneTypes();

                const arr = {
                    placeholder: '1234567890'
                };

                this.newInputs.push(arr);
            },

            addNewNumber(){
                if (this.newNumberIsAgent){
                    this.saveNewAgentNumberAndContactDetails();
                }else{
                    this.saveNewNumber();
                }

            },

            responseErrorMessage(exception){
                if (exception.status === 422) {
                    Object.keys(exception.data).forEach(numberKey => {
                        const array = exception.data[numberKey];
                        array.forEach(error => {
                            return this.warningFlashNotification(error);
                        });
                    });
                }

                console.log(exception);
            },

            warningFlashNotification(error){
                this.addNotification({
                    title: "Warning!",
                    text: error,
                    type: "danger",
                    timeout: true
                });
            },

            saveNewNumber(){
                this.loading = true;
                if (this.newPhoneType.length === 0){
                    this.warningFlashNotification("Please choose phone number type");
                    this.loading = false;
                    return;
                }

                if (this.newPhoneNumber.length === 0){
                    this.warningFlashNotification("Phone number is required.");
                    this.loading = false;
                    return;
                }

                if (this.patientPhoneNumbers.length === 0){
                    this.makeNewNumberPrimary = true;
                }

                axios.post('/manage-patients/new/phone', {
                    phoneType:this.newPhoneType,
                    phoneNumber:this.newPhoneNumber,
                    patientUserId:this.userId,
                    makePrimary:this.makeNewNumberPrimary,
                })
                    .then((response => {
                        this.getPatientPhoneNumbers();
                        if (response.data.hasOwnProperty('message')){
                            this.successFlashNotification(response.data.message);
                        }
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response)
                });
            },

            successFlashNotification(message){
                this.addNotification({
                    title: "Success!",
                    text: message,
                    type: "success",
                    timeout: true
                });
            },
            removeInputField(index){
                this.loading = true;
                this.newPhoneType = '';
                this.newPhoneNumber = '';
                this.newInputs = [];
                this.newInputs.splice(index, 1);
                this.loading = false;

            },

            deletePhone(number){
                this.loading = true;
                if (number.type.toLowerCase() === agent){
                    this.$refs.editPatientAgentContact.deleteAgentContact(true);
                    return;
                }

                if (! confirm("Are you sure you want to delete this phone number")){
                    this.loading = false;
                    return;
                }

                const phoneNumberId = number.hasOwnProperty('phoneNumberId')
                    ? number.phoneNumberId
                    : '';

                this.loading = true;
                axios.post('/manage-patients/delete-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                })
                    .then((response => {
                        this.getPatientPhoneNumbers();
                        if (response.data.hasOwnProperty('message')){
                            this.successFlashNotification(response.data.message);
                        }
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },
        }),

        created() {
            this.getPatientPhoneNumbers();
        },

        mounted(){
            const self = this;
            EventBus.$on("refresh:phoneData", function () {
                self.getPatientPhoneNumbers();
            });
        }
    }
</script>

<style scoped>

#numberType{
       min-width: 91px;
   }

    .borderColor{
     border: #f62056 solid 1px;
    }
    .phone-numbers{
        float: left;
        margin-left: -24px;
    }

    .extraInputs{
        display: inline-flex;
        padding-bottom: 10px;
        white-space: nowrap;
        padding-left: 53px;
    }
    .phone-type{
        min-width:  90px;
        max-width:  90px;
        text-align: center;
        background-color: transparent;
    }
    .remove-phone{
        cursor: pointer;
        height: 29px;
    }

    .remove-input{
        margin-left: 10px;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
    }

   .save-number{
        margin-left: 5px;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
    }

   .add-new-number{
        color: #50b2e2;
        font-size: 15px;
        cursor: pointer;
        padding: 10px;
        margin-bottom: 15px;
       background: transparent;
       border: none;
    }

    .plus-one{
        padding-right: 26px;
        padding-top: 10px;
        background-color: transparent;
    }

    .numbers{
        display: inline-flex;
        padding-bottom: 10px;
    }

    .make-primary{
        display: flex;
        margin-left: 15px;
    }

    .to-call{
        display: flex;
        margin-right: 10px;
    }
    .types{
        padding-right: 6px;
        padding-left: 10px;
    }
    .phone-number{
        background-color: transparent;
        max-width: 110px;
        min-width: 110px;
    }

    .update-primaryNumber{
        height: 29px;
        padding: 5px;
        margin-right: 5px;
        background-color: #5cb85c;
        color: white;
    }

    .bgColor{
        background-color:  #c4ebff;
    }

    .alt-contact-block{
        margin-top: 30px;
        margin-bottom: -15px;
    }

    .shortPadding{
        padding-left: 10px;
    }

    .add-agent{
        margin-left: 10px;
    }
</style>