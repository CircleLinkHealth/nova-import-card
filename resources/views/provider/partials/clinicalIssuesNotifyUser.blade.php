<select v-select2="newUser.forward_alerts_to.user_id" style="width: 100%;">
    <option value="" disabled selected>Select existing Staff Member.</option>

    <option v-for="user in newUsers"
            value="@{{ user.id }}">@{{ user.first_name }} @{{ user.last_name }}</option>
</select>