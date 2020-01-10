<template>
    <div>
        <p style="font-weight: lighter; padding-left: 15px">Check to confirm family member(s):</p>
        <hr>
        <ul class="scrollable-list">
            <li v-for="member in this.suggestedFamilyMembers" class="sidebar-demo-list" style="height: auto !important;">
                <label>
                    <input type="checkbox" :value="member.id" v-model="this.confirmed_family_members" @change="confirmMembers()">
                    <span>{{member.first_name}} {{member.last_name}}</span>
                    <ul style="padding-left: 10px">
                        <li><strong>Address:</strong>{{member.address.value}}</li>
                        <li><strong>Phone:</strong>{{member.phone.value}}</li>
                    </ul>
                </label>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: "suggested-family",
        props: [
            'suggestedFamilyMembers'
        ],
    }
</script>

<style scoped>

</style>