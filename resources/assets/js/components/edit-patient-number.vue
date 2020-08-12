<template>
    <div class="phone-numbers">
        <div class="input-group">
            <span v-if="shouldShowError" class="help-block" style="color: red">{{this.errorMessage}}</span>
            <h5 v-if="!loading && callEnabled" style="padding-left: 4px; color: #50b2e2;">Number<br>to Call</h5>
            <template v-if="true" v-for="(number, index) in patientPhoneNumbers">
                <div class="numbers">
                    <div v-if="callEnabled" style="margin-top: 7px;">
                        <input name="isPrimary"
                               class="to-call"
                               @click="selectedNumber(number.number)"
                               type="radio"
                               :checked="numberIsPrimary(number)">
                    </div>

                    <div v-if="number.number !== null"
                        @mouseover="enableUpdateButton(index)"
                        style="display: inline-flex;">
                        <div class="types">
                            <input name="type"
                                   class="form-control phone-type"
                                   :class="{'bgColor' : numberIsPrimary(number)}"
                                   type="text"
                                   :value="number.type"
                                   :disabled="true"/>
                        </div>

                        <span class="input-group-addon plus-one">+1</span>
                        <input name="number"
                               class="form-control phone-number"
                               :class="{'bgColor' : numberIsPrimary(number)}"
                               type="tel"
                               :value="number.number"
                               :disabled="true"/>
                    </div>
                </div>
                <i v-if="!loading && number.isPrimary === false && number.number !== null"
                   class="glyphicon glyphicon-trash remove-phone"
                   title="Delete Phone Number"
                   @click="deletePhone(number)"></i>

                <button v-if="showMakePrimary(index, number)"
                        class="btn btn-sm update-primaryNumber"
                        type="button"
                        style="display: inline;"
                        @click="updatePrimaryPhone(number.phoneNumberId)"
                        :disabled="number.isPrimary">
                    Make primary
                </button>
                <br>
            </template>
            <div>
                <loader v-if="loading"></loader>
            </div>

            <!--Extra inputs that are requested by user-->
            <div v-for="(input, index) in newInputs" class="extra-inputs">
                <div style="padding-right: 14px; margin-left: -10px;">
                    <div class="numbers">
                        <div class="types">
                       <select2 id="numberType"
                                class="form-control"
                                style="width: 81px;"
                                v-model="newPhoneType">
                           <option v-for="(phoneType, key) in phoneTypes"
                                   :key="key"
                                   :value="phoneType">
                               {{phoneType}}
                           </option>
                       </select2>
                   </div>


                <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                <input name="number"
                       class="form-control phone-number"
                       maxlength="10"
                       minlength="10"
                       type="tel"
                       title="10-digit US Phone Number"
                       :placeholder="input.placeholder"
                       v-model="newPhoneNumber"
                       :disabled="loading"/>

                <i v-if="!loading"
                   class="glyphicon glyphicon-minus remove-input"
                   title="Remove extra field"
                   @click="removeInputField(index)"></i>


                        <button v-if="addNewFieldClicked"
                                class="btn btn-sm save-number"
                                style="display: inline;"
                                type="button"
                                @click="saveNewNumber"
                                :disabled="disableSaveButton">
                            {{setSaveBtnText}}
                        </button>

                       <div v-if="! showAlternateFields && newPhoneNumber.length !== 0" style="display: flex;">
                           <input id="makePrimary"
                                  class="make-primary"
                                  v-model="makeNewNumberPrimary"
                                  type="checkbox">
                           <label for="makePrimary" style="padding-left: 30px;">Make Primary</label>
                       </div>
                </div>
                </div>
            </div>

<!-- this.patientPhoneNumbers.length < 3 is not really dynamic. We only allow 3 phone types for now     -->
<!--            @todo: write a function maybe to count phone types on page load instance?-->
            <a v-if="!loading && this.newInputs.length === 0 && this.patientPhoneNumbers.length < 3"
               class="glyphicon glyphicon-plus-sign add-new-number"
               title="Add Phone Number"
               @click="addPhoneField()">
                Add phone number
            </a>

           <div v-if="!loading"
                class="alternate-fields">
               <input name="alternativeContactName"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateNumberNoDetails}"
                      maxlength="40"
                      minlength="3"
                      type="text"
                      title="Type alternate contact name"
                      placeholder="Alternate contact name"
                      v-model="agentContactDetails[0].agentName"
                      :disabled="loading"/>

               <input name="alternativeEmail"
                      style="margin-left: 10px;"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateNumberNoDetails}"
                      maxlength="20"
                      minlength="3"
                      type="text"
                      title="Type alternate contact email"
                      placeholder="Alternate contact email"
                      v-model="agentContactDetails[0].agentEmail"
                      :disabled="loading"/>
               <br>
               <input name="alternativeRelationship"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateNumberNoDetails}"
                      maxlength="20"
                      minlength="3"
                      type="text"
                      title="Type alternate contact relationship"
                      placeholder="Alternate contact relationship"
                      v-model="agentContactDetails[0].agentRelationship"
                      :disabled="loading"/>

               <button class="btn btn-sm save-number"
                       style="display: inline;"
                       type="button"
                       @click="saveNewNumber"
                       :disabled="disableSaveButton">
                   Save alternate contact
               </button>

           </div>

        </div>
    </div>
</template>

<script>
    import LoaderComponent from "./loader";
    import axios from "../bootstrap-axios";
    import EventBus from '../admin/time-tracker/comps/event-bus'
    import CallNumber from "./call-number";
    const alternate = 'alternate';
    export default {
        name: "edit-patient-number",

        components: {
            'loader': LoaderComponent,
            'call-number':CallNumber,
        },

        props: [
            'userId',
            'errorMessage',
            'callEnabled',
        ],

        data(){
            return {
                loading:false,
                patientPhoneNumbers:[],
                newPhoneType:'',
                newPhoneNumber:'',
                newInputs:[],
                phoneTypes:[],
                // saveBtnText:"Save Number",
                markPrimaryEnabledForIndex:'',
                makeNewNumberPrimary:false,
                primaryNumber:'',
                agentContactDetails:[
                    {
                        agentEmail:'',
                        agentName:'',
                        agentRelationship:'',
                        agentTelephone:'',
                    }
                ],
            }
        },
        computed:{
            validEmail(){
                return this.agentContactDetails[0].agentEmail.length !== 0
                    && this.agentContactDetails[0].agentEmail.includes("@")
                    && (this.agentContactDetails[0].agentEmail.includes(".com"));
            },
            disableSaveButton(){
                return this.loading
                    || this.newPhoneType.length === 0
                    || isNaN(this.newPhoneNumber.toString())
                    || this.newPhoneNumber.toString().length !== 10;

            },

            shouldShowError(){
                return this.patientPhoneNumbers.length === 0;
            },

            setSaveBtnText(){
                if(this.makeNewNumberPrimary || this.shouldShowError){
                    return'Save & Make Private';
                }

                if (this.showAlternateFields){
                    return "Save alternate number";
                }

                return "Save Number";
            },

            //Alternate fields = agent phone, email, relationship...
            showAlternateFields(){
                return this.newPhoneType.toLowerCase() === alternate;
            },

            alternateNumberNoDetails(){
                return this.showAlternateFields && this.agentDetailsIsEmpty();
            },
        },

        methods: {
            agentDetailsIsEmpty(){
                return this.agentContactDetails.length !== 0
                    ?? this.agentContactDetails[0].agentTelephone.number.length === 0;
            },

            showMakePrimary(index, number){
                return this.isIndexToUpdate(index)
                    && this.agentDetailsIsEmpty
                    && number.isPrimary === false
                    && number.type.toLowerCase() !== alternate;
            },

            emitPrimaryNumber(){
                const primaryNumber =  this.patientPhoneNumbers.filter(n=>n.isPrimary).map(function (phone) {
                    // Will always be just one primary number with current impl.
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
                confirm("Are you sure you want to mark this number as primary number");
                this.loading = true;
                axios.post('/manage-patients/mark/primary-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                }).then((response => {
                        this.getPhoneNumbers();
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            },

            isIndexToUpdate(index){
            return index === this.markPrimaryEnabledForIndex;
            },

            enableUpdateButton(index){
                this.markPrimaryEnabledForIndex = index;
            },

            resetData(){
                this.patientPhoneNumbers = [];
                this.phoneTypes = [];
                this.newInputs = [];
                this.makeNewNumberPrimary = false;
            },

            getPhoneNumbers(){
                this.loading = true;
                this.resetData();
                axios.post('/manage-patients/get-phones', {
                    userId:this.userId
                })
                    .then((response => {
                        this.patientPhoneNumbers.push(...response.data.phoneNumbers);
                        this.phoneTypes.push(...response.data.phoneTypes);

                        if(response.data.hasOwnProperty('agentContactFields') && response.data.agentContactFields.length !== 0) {
                            const agentDetails = response.data.agentContactFields;
                            this.agentContactDetails[0].agentEmail = agentDetails[0].agentEmail ?? '';
                            this.agentContactDetails[0].agentName = agentDetails[0].agentName ?? '';
                            this.agentContactDetails[0].agentRelationship = agentDetails[0].agentRelationship ?? '';
                            this.agentContactDetails[0].agentTelephone = agentDetails[0].agentTelephone ?? '';
                        }
                        this.emitPrimaryNumber();
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            },

            addNewFieldClicked(){
                return this.newInputs.length > 0;
            },

            addPhoneField(){
                if (this.newInputs.length > 0) {
                    alert('Please save the existing field first');
                    return;
                }
                this.newPhoneNumber = '';
                this.newPhoneType = '';

                const arr = {
                  placeholder: '2345678901'
                };

                this.newInputs.push(arr);
            },

            // validateAlternativeFields(){
            //
            //
            // },

            saveNewNumber(){
                this.loading = true;
                const alternateNewEmail = this.agentContactDetails[0].agentEmail.length > 0
                ? this.agentContactDetails[0].agentEmail
                : null;
                const alternateNewRelationship = this.agentContactDetails[0].agentRelationship.length > 0
                ? this.agentContactDetails[0].agentRelationship
                : null;
                const alternateNewName = this.agentContactDetails[0].agentName.length > 0
                ? this.agentContactDetails[0].agentName
                : null;

                if (this.newPhoneType.length === 0){
                    alert("Please choose phone number type");
                    // this.loading = false;
                    return;
                }

                if (this.newPhoneNumber.length === 0){
                    alert("Phone number is required.");
                    // this.loading = false;
                    return;
                }

                if (this.showAlternateFields) {
                    if(this.agentContactDetails[0].agentRelationship.length === 0){
                        alert("Alternate relationship is required.");
                        this.loading = false;
                        return;
                    }

                    if(this.agentContactDetails[0].agentEmail.length === 0){
                        alert("Alternate email is required.");
                        this.loading = false;
                        return;
                    }

                    if (this.agentContactDetails[0].agentEmail.length > 0 && ! this.validEmail){
                        alert("Alternate email is not a valid email format.");
                        this.loading = false;
                        return;
                    }
                }

                if (this.patientPhoneNumbers.length === 0){
                    this.makeNewNumberPrimary = true;
                }

                axios.post('/manage-patients/new/phone', {
                    phoneType:this.newPhoneType,
                    phoneNumber:this.newPhoneNumber,
                    patientUserId:this.userId,
                    makePrimary:this.makeNewNumberPrimary,
                    agentName:alternateNewName,
                    agentRelationship:alternateNewRelationship,
                    agentEmail:alternateNewEmail,
                    alternateFieldsEnabled:this.showAlternateFields,
                })
                    .then((response => {
                        this.getPhoneNumbers();
                        if (response.data.hasOwnProperty('message')){
                            alert(response.data.message);
                        }
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error);
                });
            },

            removeInputField(index){
                this.loading = true;
                this.newInputs = [];
                this.newInputs.splice(index, 1);
                this.loading = false;

            },

            deletePhone(number){
                confirm("Are you sure you want to delete this phone number");
                this.loading = true;

                let deleteAlternatePhone = false;

                const phoneNumberId = number.hasOwnProperty('phoneNumberId')
                ? number.phoneNumberId
                : '';

                if (number.type.toLowerCase() === alternate){
                    deleteAlternatePhone =true;
                }

                axios.post('/manage-patients/delete-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                    deleteAltPhone:deleteAlternatePhone,
                })
                    .then((response => {
                        this.getPhoneNumbers();
                        this.loading = false;
                        if (response.data.hasOwnProperty('message')){
                            alert(response.data.message);
                        }
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            }
        },

        created() {
            this.getPhoneNumbers();
        }
    }
</script>

<style scoped>
.phone-numbers{
    float: left;
}

.extra-inputs{
    display: inline-flex;
    padding-bottom: 10px;
    padding-left: 10px;
    white-space: nowrap;
}
    .phone-type{
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        background-color: transparent;
    }

.remove-phone{
        top: -7px;
        padding-left: 3px;
        color: red;
        cursor: pointer;
    }
    .remove-input{
        margin-left: 19px;
        padding-top: 5px;
        color: red;
        cursor: pointer;
        background-color: transparent;
    }
    .stop-edit-phone{
        margin-left: 10px;
        padding-top: 5px;
        color: #50b2e2;
        cursor: pointer;
    }

    .save-number{
        margin-left: 15px;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
    }

    .add-new-number{
        word-spacing: -10px;
        color: #50b2e2;
        font-size: 20px;
        cursor: pointer;
        padding: 10px;
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
        /*padding-left: 10px;*/
    }
    .phone-number{
        background-color: transparent;
        max-width: 140px;
        min-width: 140px;
    }

    .alternative-field{
        background-color: transparent;
        max-width: 270px;
        min-width: 270px;
        margin-bottom: 18px;
    }
    .update-primaryNumber{
        height: 29px;
        padding: 5px;
        color: #50b2e2;
        margin-left: 10px;
        margin-top: -20px
    }

.bgColor{
    background-color:  #c4ebff;
}

.borderColor{
    border: green solid;
}

</style>