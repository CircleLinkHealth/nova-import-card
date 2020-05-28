<template>
    <card class="flex flex-col items-left">
        <div class="px-3 py-3">
            <h4 class="text-left text-3xl text-80 font-light">{{this.title()}}</h4>
            <div class="py-2">
                    <span v-if="! this.card.is_redirect"  class="flex">
                       <label for="amount">
                           Input number of patients to <br> send enrollment sms/emails to:
                       </label>
                        <input type="number"
                               id="amount"
                               name="amount"
                               v-model="amount"
                               :disabled="sendingInvites"
                               style="border: 1px solid #5cc0dd; max-width: 100px; margin-left: 10px;" required>
                    </span>
            </div>

            <loader v-if="sendingInvites" width="30">
            </loader>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
            </div>

          <div v-if="! this.card.is_redirect" class="invite-buttons">
              <div v-if="! this.card.is_patient" class="button">
                  <a class="btn btn-default btn-primary ml-auto mt-auto"
                     :disabled="sendingInvites"
                     style="cursor: pointer;
                      background-color: #4baf50;
                      white-space: nowrap;
                      width: 275px;
                      margin-right: 10px;"
                     @click="sendInvites('#4baf50', amount)">
                      Send SMS/Emails (Green Btn.)
                  </a>

                  <a class="btn btn-default btn-primary ml-auto mt-auto"
                     :disabled="sendingInvites"
                     style="cursor: pointer;
                      background-color: #b1284c;
                      white-space: nowrap;
                      width: 275px;"
                     @click="sendInvites('#b1284c', amount)">
                      Send SMS/Emails (Red Btn.)
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
export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],

    data() {
        return {
            amount:'',
            errors:null,
            sendingInvites:false,
        };
    },

    methods: {
        redirectToInvitesDashboard(){
            // tried to redirect using Action::push() or simple redirect in controller, but it doesnt work, no errors / feedback.
            // Keeping this solution temporarily
            window.location.href = this.card.redirect_to
        },

        title(){
          return this.card.is_redirect ? "Select Practice For Invites" : "Enrollment Invites";
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
