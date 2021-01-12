<select2 :options="newUsers" :selected="newUser.forward_careplan_approval_emails_to.user_id" v-model="newUser.forward_careplan_approval_emails_to.user_id" style="width: 100%;">
    <option value="" v-bind:selected="!newUser.forward_careplan_approval_emails_to.user_id">Select existing Staff Member.
    </option>
</select2>