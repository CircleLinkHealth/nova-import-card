<template>
    <div class="phone-numbers">
        <div class="input-group">
            <template v-if="true" v-for="(number, index) in patientPhoneNumbers">
                <div style="display: inline-flex; padding-right: 5px;">
                    <input name="type"
                           class="form-control phone-type" type="text"
                           :value="number.type"
                           :disabled="true"/>
                </div>

              <div style="display: inline-flex; padding-bottom: 10px; padding-left: 10px;">
                  <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                  <input name="number"
                         class="form-control phone-number" type="tel"
                         title="10-digit US Phone Number" placeholder="2345678901"
                         :value="number.number"
                         :disabled="true"/>
              </div>

                <i v-if="!loading"
                   class="glyphicon glyphicon-trash remove-phone"
                   title="Delete Phone Number"
                   @click="deletePhone(number.phoneNumberId)"></i>
                <br>
            </template>
            <loader v-if="loading"></loader>

            <!--Extra inputs that are requested by user-->
            <div v-for="(input, index) in newInputs" style="display: inline-flex; padding-bottom: 10px; padding-left: 10px;">
                <div style="padding-right: 14px; margin-left: -10px;">
                    <select2 id="numberType" class="form-control" style="width: 81.566px;"
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
                   class="form-control phone-number" type="tel"
                   title="10-digit US Phone Number" :placeholder="input.placeholder"
                   v-model="newPhoneNumber"
                   :disabled="loading"/>

                <i v-if="!loading"
                   class="glyphicon glyphicon-minus remove-input"
                   title="Remove extra field"
                   @click="removeInputField(index)"></i>

                <button class="btn btn-sm save-number"
                        @click="saveNewNumber"
                        :disabled="disableSaveButton">
                    Save phone number
                </button>
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
        ],

        data(){
            return {
                loading:false,
                patientPhoneNumbers:[],
                editingIsOn:false,
                newPhoneType:'',
                newPhoneNumber:'',
                newInputs:[],
                phoneTypes:[]
            }
        },
        computed:{
            disableSaveButton(){
                return this.loading
                    || this.newPhoneType.length === 0
                    || isNaN(this.newPhoneNumber.toString())
                    || this.newPhoneNumber.toString().length !== 10;

            },
        },

        methods: {
            resetData(){
                this.patientPhoneNumbers = [];
                this.phoneTypes = [];
                this.newInputs = [];
            },
            getPhoneNumbers(){
                this.loading = true;
                this.resetData();
                axios.post('/manage-patients/demographics/get-phones', {
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
                })
                    .then((response => {
                        console.log(response.data);
                        this.getPhoneNumbers();
                        if (response.data.hasOwnProperty('messages')){
                            alert(response.data.messages);
                        }
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
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
                axios.post('/manage-patients/demographics/delete-phone', {
                    phoneId:phoneNumberId
                })
                    .then((response => {
                        console.log(response.data);
                        this.getPhoneNumbers();
                        this.loading = false;
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
        max-width: 80px;
        text-align: center;
    }
    .edit-phone{
        margin-left: 10px;
        padding-top: 5px;
        color: #50b2e2;
        cursor: pointer;
    }
    .remove-phone{
        margin-left: 19px;
        padding-top: 5px;
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

</style>