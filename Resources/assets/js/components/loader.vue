<template>
    <div class="loader"></div>
</template>

<script>
    export default {
        name: 'loader'
    }
</script>

<style>
    .loader {
            border: 5px solid #31C6F9;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
            border-top: 5px solid #555;
            border-radius: 50%;
            width: 30px;
            height: 30px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>