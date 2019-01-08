<template>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <p>{{doc.name}}</p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "care-document-box",
        props: {
            type: {
                type: String,
                required: true
            },
            doc: {
                type: Object,
                required: false
            }
        }
    }
</script>

<style>
    .box{
        width: 250px;
        height: 300px;
        border-radius: 5px;
        background-color: #ffffff;
        margin-right: 30px;
        margin-bottom: 30px;
        padding: 0;
    }

    .panel {
        border: 0;
    }

    .panel-primary>.panel-heading {
        background-color: #5cc0dd;
        border-color:  #5cc0dd;
    }

    .box-title {
        width: 250px;
        height: 50px;
        border-radius: 5px;
        background-color: #5cc0dd;
    }

    h4 {
        color: #ffffff;
    }
</style>