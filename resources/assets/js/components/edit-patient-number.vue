<template>
    <div class="phone-numbers">
        <div class="input-group">
            <span v-if="shouldShowError" class="help-block" style="color: red">{{this.errorMessage}}</span>
            <h5 v-if="!loading" style="padding-left: 4px; color: #50b2e2;">Primary<br>Phone</h5>
            <template v-if="true" v-for="(number, index) in patientPhoneNumbers">
                <div class="numbers">
                    <div style="margin-top: 7px;">
                        <input name="isPrimary"
                               class="is-primary"
                               :checked="number.isPrimary"
                               @click="enableUpdateButton(index)"
                               type="radio">
                    </div>


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
                         title="10-digit US Phone Number" placeholder="2345678901"
                         :value="number.number"
                         :disabled="true"/>
              </div>

                <i v-if="!loading && number.isPrimary === false"
                   class="glyphicon glyphicon-trash remove-phone"
                   title="Delete Phone Number"
                   @click="deletePhone(number.phoneNumberId)"></i>

                <button v-if="isIndexToUpdate(index) && number.isPrimary === false"
                        class="btn btn-sm update-primaryNumber"
                        type="button"
                        style="display: inline;"
                        @click="updatePrimaryPhone(number.phoneNumberId)"
                        :disabled="number.isPrimary">
                    Make primary
                </button>
                <br>
            </template>
            <loader v-if="loading"></loader>

            <!--Extra inputs that are requested by user-->
            <div v-for="(input, index) in newInputs" style="display: inline-flex; padding-bottom: 10px; padding-left: 10px;">
                <div style="padding-right: 14px; margin-left: -10px;">

                    <div class="numbers">
                        <input id="isPrimary"
                               class="is-primary"
                               v-model="makeNewNumberPrimary"
                               type="checkbox">

                        <div class="types">
                       <select2 id="numberType" class="form-control" style="width: 81px;"
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

                <button class="btn btn-sm save-number" style="display: inline;"
                        type="button"
                        @click="saveNewNumber"
                        :disabled="disableSaveButton">
                    {{setSaveBtnText}}
                </button>
                </div>
                </div>
            </div>
<br>
            <a v-if="!loading && this.newInputs.length === 0"
               class="glyphicon glyphicon-plus-sign add-new-number"
               title="Add Phone Number"
               @click="addPhoneField()">
                Add phone number
            </a>
        </div>
    </div>
</template>

<script>
    import LoaderComponent from "./loader";
    import axios from "../bootstrap-axios";

    export default {
        name: "edit-patient-number",
        components: {
            loader: LoaderComponent,
        },
        props: [
            'userId',
            'errorMessage'
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
                makeNewNumberPrimary:false
            }
        },
        computed:{
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
                if(this.makeNewNumberPrimary){
                    return'Save & Make Private';
                }
                return "Save Number";
            },
        },

        methods: {
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
                        console.log(response.data);
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
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            },
            addPhoneField(){
                if (this.newInputs.length > 0) {
                    //Should never reach here.
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

            saveNewNumber(){
                this.loading = true;
                if (this.newPhoneType.length === 0){
                    alert("Please choose phone number type");
                }
                if (this.newPhoneNumber.length === 0){
                    // Should not happen.
                    alert("Please type a phone number");
                }
                axios.post('/manage-patients/new/phone', {
                    phoneType:this.newPhoneType,
                    phoneNumber:this.newPhoneNumber,
                    patientUserId:this.userId,
                    makePrimary:this.makeNewNumberPrimary
                })
                    .then((response => {
                        console.log(response.data);
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
              console.log(index);
              console.log(this.newInputs[index]);
              this.newInputs.splice(index, 1);
              this.loading = false;

            },

            deletePhone(phoneNumberId){
                confirm("Are you sure you want to delete this phone number");
                this.loading = true;
                axios.post('/manage-patients/delete-phone', {
                    phoneId:phoneNumberId
                })
                    .then((response => {
                        console.log(response.data);
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
    .phone-type{
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        background-color: transparent;
    }
    .edit-phone{
        margin-left: 10px;
        padding-top: 5px;
        color: #50b2e2;
        cursor: pointer;
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
        padding-right: 5px;
        padding-left: 16px;
        padding-bottom: 10px;
    }
    .is-primary{
        display: flex;
        margin-right: 10px;
    }
    .types{
        padding-right: 10px;
        padding-left: 10px;
    }
    .phone-number{
        background-color: transparent;
        max-width: 140px;
        min-width: 140px;
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

</style>