<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-center text-3xl text-80 font-light">Enrollment Invites</h1>
            <div class="py-4">
                    <span class="flex ">
                       <label for="number">Input number of patients to send enrollment sms/emails to:</label>
                        <input type="text"
                               id="number"
                               name="number"
                               v-model="number"
                               style="border: 1px solid #5cc0dd">
                    </span>
            </div>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
                <div v-if="! this.card.is_patient" class="button">
                    <a class="btn btn-default btn-primary ml-auto mt-auto"
                       style="cursor: pointer; background-color: #4baf50" @click="sendInvites('#4baf50', number)">Send SMS/Emails (Green But.)</a>

                    <a class="btn btn-default btn-primary ml-auto mt-auto"
                       style="cursor: pointer; background-color: #b1284c" @click="sendInvites('#b1284c', number)">Send SMS/Emails (Red But.)</a>
                </div>

                <div v-if="this.card.is_patient" class="button">
                    <a class="btn btn-default btn-primary ml-auto mt-auto"
                       style="cursor: pointer; background-color: #4baf50" @click="sendInvites('#4baf50', number)">Send Invite</a>
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
            number:'',
            errors:null,
        };
    },

    methods: {
        sendInvites(color, number){
            if (this.number === ''){
                alert('Invitations number to be send is required');
                return;
            }

            Nova.request().post('/nova-vendor/enrollment-invites/enrollment-invites', {
                color:color,
                number:number,
                practice_id:this.card.practice_id,
                is_patient:this.card.is_patient
            }).then(response => {
                this.number = '';
                this.$toasted.success(response.data.message);
            }).catch(error => {
                this.number = '';
                this.$toasted.error(error.response.data);

            });
        }
    },
    mounted() {

    },
}
</script>
