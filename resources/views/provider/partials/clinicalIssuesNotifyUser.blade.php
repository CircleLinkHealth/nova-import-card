<select v-select2="newUser.clinical_issues_notify.user_id" style="width: 100%;">
    <option value="" disabled selected>Select User to notify.</option>

    <option v-for="user in newUsers"
            value="@{{ user.id }}">@{{ user.first_name }} @{{ user.last_name }}</option>
</select>