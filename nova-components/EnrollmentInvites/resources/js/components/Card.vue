<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-center text-3xl text-80 font-light">Enrollment Invites</h1>
            <div class="py-4">
                    <span class="flex" style="max-width: 70%; margin-bottom: 10px;">
                       <label for="amount">
                           Input number of patients to send enrollment sms/emails to:
                       </label>
                        <input type="number"
                               id="amount"
                               name="amount"
                               v-model="amount"
                               :disabled="sendingInvites"
                               style="border: 1px solid #5cc0dd; max-width: 100px;" required>
                    </span>
            </div>

            <loader v-if="sendingInvites" width="30">
            </loader>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
           <div class="invite-buttons" style="margin-bottom: 10px;">
               <div v-if="! this.card.is_patient" class="button">
                   <a class="btn btn-default btn-primary ml-auto mt-auto"
                      :disabled="sendingInvites"
                      style="cursor: pointer; background-color: #4baf50" @click="sendInvites('#4baf50', amount)">Send SMS/Emails (Green Btn.)</a>

                   <a class="btn btn-default btn-primary ml-auto mt-auto"
                      :disabled="sendingInvites"
                      style="cursor: pointer; background-color: #b1284c" @click="sendInvites('#b1284c', amount)">Send SMS/Emails (Red Btn.)</a>
               </div>

               <div v-if="this.card.is_patient" class="button"
                    :disabled="sendingInvites">
                   <a class="btn btn-default btn-primary ml-auto mt-auto"
                      style="cursor: pointer; background-color: #4baf50" @click="sendInvites('#4baf50', amount)">Send Invite</a>
               </div>
           </div>
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
