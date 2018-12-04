<template>
    <div>
        <two-fa :authy-user="user.authy_user" :global-role="user.global_role"></two-fa>
    </div>
</template>
<script>
    import LoaderComponent from './loader';
    import TwoFASettings from './settings-2-fa';
    import {rootUrl} from "../app.config";

    export default {
        name: 'user-account-settings',
        props: [
            'user',
        ],
        components: {
            'loader': LoaderComponent,
            'twoFa': TwoFASettings,
        },
        data() {
            return {
                loaders: {
                    save: false
                },
            }
        },
        methods: {},
        mounted() {

        }
    }
</script>
<style>
    .loader-right {
        margin-top: -4px;
        float: right;
    }
</style>