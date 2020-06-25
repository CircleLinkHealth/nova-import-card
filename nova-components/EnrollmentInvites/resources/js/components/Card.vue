<template>
    <card class="flex flex-col items-left">
        <div class="px-3 py-3">
            <h4 class="text-left text-3xl text-80 font-light">{{this.title()}}</h4>
            <div class="py-2">
                    <span v-if="! this.card.use_redirect_button"  class="flex">
                       <label for="amount">
                           Input number of patients to <br> send enrollment sms/emails to:
                       </label>
                        <input type="number"
                               id="amount"
                               name="amount"
                               v-model="amount"
                               :disabled="sendingInvites"
                               style="border: 1px solid #5cc0dd; max-width: 100px; max-height: 40px; margin-left: 10px;" required>

                         <label for="color" style="padding-left: 30px; padding-right: 15px;">
                             Choose invitation <br> button color:
                         </label>

                        <vue-select name="color"
                                    id="color"
                                    v-model="selectedButtonColor"
                                    :options="buttonColors"
                                    @change="setButtonBackgroundColor">
                        </vue-select>

                    </span>
            </div>

            <loader v-if="sendingInvites" width="30">
            </loader>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
            </div>

          <div v-if="! this.card.use_redirect_button" class="invite-buttons">
              <div v-if="! this.card.is_patient && this.selectedButtonColor.length !== 0" class="button">
                  <a class="btn btn-default btn-primary ml-auto mt-auto"
                     :disabled="sendingInvites"
                     :style="bgc"
                     style="cursor: pointer;
                     white-space: nowrap;
                     width: 275px;
                     margin-right: 10px;"
                     @click="sendInvites(bgc.backgroundColor, amount)">
                      Send SMS/Emails
                  </a>
              </div>

              <div v-if="this.card.is_patient" class="button"
                   :disabled="sendingInvites">
                  <a class="btn btn-default btn-primary ml-auto mt-auto"
                     style="cursor: pointer; background-color: #4baf50" @click="sendInvites('#4baf50', amount)">Send Invite</a>
              </div>
          </div>

                <div v-else class="button">
                    <a class="btn btn-default btn-primary ml-auto mt-auto"
                       style="cursor: pointer; background-color: #4baf50" @click="redirectToInvitesDashboard()">Select Practice</a>
                </div>
        </div>
    </card>
</template>

<script>
    import VueSelect from 'vue-select';
export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],

    components: {
        'vue-select': VueSelect,
    },

    data() {
        return {
            amount:'',
            errors:null,
            sendingInvites:false,
            selectedButtonColor:[],
            buttonColors:[
                {
                    label:'Green',
                    value:'#4baf50'
                },

                {
                    label:'Red',
                    value:'#b1284c'
                },

                {
                    label:'Blue',
                    value:'#12a2c4'
                }
            ],

            bgc:{
               backgroundColor:'',
           }
        };
    },

    methods: {
        setButtonBackgroundColor(){
          this.bgc.backgroundColor = this.selectedButtonColor.value;
        },
        redirectToInvitesDashboard(){
            // tried to redirect using Action::push() or simple redirect in controller, but it doesnt work, no errors / feedback.
            // Keeping this solution temporarily
            window.location.href = this.card.redirect_url
        },

        title(){
          return this.card.use_redirect_button ? "Select Practice For Invites" : "Enrollment Invites";
        },

        sendInvites(color, amount){
            this.sendingInvites = true;

            if (this.amount === ''){
                this.sendingInvites = false;
                alert('Invitations number to be send is required');
                return;
            }

            Nova.request().post('/nova-vendor/enrollment-invites/enrollment-invites', {
                color:color,
                amount:amount,
                practice_id:this.card.practice_id,
                is_patient:this.card.is_patient
            }).then(response => {
                this.amount = '';
                this.sendingInvites = false;
                this.$toasted.success(response.data.message);
            }).catch(error => {
                this.sendingInvites = false;
                this.amount = '';
                this.$toasted.error(error.response.data);

            });
        }
    },
    mounted() {

    },
}
</script>
