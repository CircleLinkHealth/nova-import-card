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
<!--                <div v-else style="display: inline-flex; padding-right: 5px;">-->
<!--                <select2 id="numberType" class="form-control"-->
<!--                         v-model="dropdownPhoneType">-->
<!--                    <option v-for="(phoneType, key) in phoneTypes"-->
<!--                            :key="key"-->
<!--                            :value="phoneType">-->
<!--                        {{phoneType}}-->
<!--                    </option>-->
<!--                </select2>-->
<!--                </div>-->
              <div style="display: inline-flex; padding-bottom: 10px; padding-left: 10px;">
                  <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                  <input name="number"
                         class="form-control phone-number" type="tel"
                         title="10-digit US Phone Number" placeholder="2345678901"
                         :value="number.number"
                         :disabled="number.inputDisabled || loading"/>

                  <i v-if="!loading"
                     class="glyphicon glyphicon-trash remove-phone"
                     title="Delete Phone Number"
                     @click="deletePhone(number.phoneNumberId)"></i>

                  <button v-if="shouldShowDuringEdit(index)"
                          class="btn btn-sm save-number"
                          @click="editOrSaveNumber">
                      Save
                  </button>
              </div>
                <br>
            </template>
            <loader v-if="loading"></loader>
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
                phoneNumber:'',
                loading:false,
                patientPhoneNumbers:this.phoneNumbers,
                editingIsOn:false,
                dropdownPhoneType:''
            }
        },
        computed:{

        },

        methods:{
            editOrSaveNumber(){

            },
            shouldShowDuringEdit(index){
                return this.patientPhoneNumbers[index].inputDisabled === false;
            },
            // disableInput(index){
            //     this.loading = true;
            //     this.editingIsOn = false;
            //     this.patientPhoneNumbers[index].inputDisabled = true;
            //     this.loading = false;
            // },
            //
            // enableInput(index){
            //     this.loading = true;
            //     this.editingIsOn = true;
            //     this.patientPhoneNumbers[index].inputDisabled = false;
            //     this.loading = false;
            // },

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

</style>