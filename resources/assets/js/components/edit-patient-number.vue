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

            <div v-for="(input, index) in newInputs" style="display: inline-flex; padding-bottom: 10px; padding-left: 10px;">
                <div style="padding-right: 14px; margin-left: -10px;">
                    <select2 id="numberType" class="form-control" style="width: 81.566px;"
                             v-model="dropdownPhoneType">
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
                        @click="editOrSaveNumber"
                        :disabled="disableSaveButton">
                    Save
                </button>
            </div>
<br>
            <a v-if="!loading"
               class="glyphicon glyphicon-plus-sign add-new-number"
               title="Add Phone Number"
               @click="addPhoneField()">
                Add new phone number
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
            'phoneNumbers',
            'phoneTypes'
        ],

        data(){
            return {
                loading:false,
                patientPhoneNumbers:this.phoneNumbers,
                editingIsOn:false,
                dropdownPhoneType:'',
                newPhoneNumber:'',
                newInputs:[]
            }
        },
        computed:{
            disableSaveButton(){
                return this.loading
                         || isNaN(this.newPhoneNumber.toString())
                        || this.newPhoneNumber.toString().length !== 10;

            },
        },

        methods:{
            addPhoneField(){
                if (this.newInputs.length > 0) {
                    alert('Please save the existing field first');
                    return;
                }

                const arr = {
                  placeholder: '2345678901'
                };

                this.newInputs.push(arr);
            },
            editOrSaveNumber(){

            },

            removeInputField(index){
                this.loading = true;
              console.log(index);
              console.log(this.newInputs[index]);
              this.newInputs.splice(index, 1);
              this.loading = false;

            },

            deletePhone(phoneNumberId){
                this.loading = true;
                axios.post('/manage-patients/demographics/edit', {
                    phoneId:phoneNumberId
                })
                    .then((response => {
                        console.log(response.data);
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            }
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