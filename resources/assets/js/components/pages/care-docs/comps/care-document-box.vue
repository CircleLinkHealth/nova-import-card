<template>
    <div class="col-xs-3 box">
        <div class="box-title">
            <h4>{{ type }}</h4>
        </div>
        <div class="">
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
                required: true
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