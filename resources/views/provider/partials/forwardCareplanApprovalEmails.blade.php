<select v-select2="newUser.forward_careplan_approval_emails_to.user_id" style="width: 100%;">
    <option value="" v-bind:selected="!newUser.forward_careplan_approval_emails_to.user_id">Select existing Staff Member.
    </option>

    <option v-for="user in newUsers" v-if="user.id != newUser.id"
            v-bind:selected="newUser.forward_careplan_approval_emails_to.user_id == user.id"
            value="@{{ user.id }}">@{{ user.first_name }} @{{ user.last_name }}</option>
</select>