<template>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <div class="col-md-12 panel-section">
                <button class="btn btn-warning btn-xs">
                    Status
                </button>
                <a>
                    View
                </a>
            </div>
            <div class="col-md-12  panel-section">
                <div v-if="doc.created_at">
                    {{doc.created_at}}
                </div>
            </div>
            <div class="col-md-12  panel-section">
                <p>Send Assessment Link to Provider via:</p>
            </div>
            <div class="col-md-12  panel-section">
                <button class="btn btn-method btn-xs">
                    SMS
                </button>
                <button class="btn btn-method btn-xs">
                    Email
                </button>
            </div>
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
        width: 250px;
        height: 300px;
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
    .panel-section{
        margin-bottom: 20px;
    }

    .btn-method{
        border-color: #5cc0dd;
    }
</style>