<template>
    <p>- {{appointment.type}}<span v-if="appointment.provider().specialty">,</span> <strong v-if="appointment.provider().specialty">({{appointment.provider().specialty}})</strong> on {{appointment.datetime}}
        <span v-if="appointment.provider().user.display_name">with <strong>{{appointment.provider().user.display_name}};</strong> </span>
            <span v-if="appointment.provider().user.address">A: {{appointment.provider().user.address}}</span> 
            <span v-if="appointment.provider().location().phone">P: {{appointment.provider().location().phone}}</span>
        
    </p>
</template>

<script>
    export default {
        props: ['appointment']
    }
</script>

<style>

</style>