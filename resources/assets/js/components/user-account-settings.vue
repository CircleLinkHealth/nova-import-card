<template>
    <div>
        <two-fa :authy-user="user.authy_user" :global-role="user.global_role" :force-enable="forceEnable"></two-fa>
    </div>
</template>
<script>
    import TwoFASettings from './settings-2-fa';

    export default {
        name: 'user-account-settings',
        props: [
            'user',
            'forceEnable'
        ],
        components: {
            'twoFa': TwoFASettings,
        },
        data() {
            return {};
        },
        methods: {},
        mounted() {

        }
    }
</script>
<style>
</style>