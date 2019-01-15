<template>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <div class="col-md-12  panel-section" style="margin-top: 20px">
                <div class="col-md-6">
                    {{this.docDate}}
                </div>
                <div class="col-md-6">
                    <a style="float: right" :href="viewApi()" target="_blank">View</a>
                </div>
            </div>
            <div class="col-md-12  panel-section"  style="margin-top: 40px">
                <p><strong>Send Assessment Link to Provider via:</strong></p>
            </div>
            <div class="col-md-12  panel-section">
                <button class="col-md-6 btn btn-method btn-s">
                    SMS
                </button>
                <button class="col-md-6 btn btn-method btn-s">
                    Email
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js'

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
        },
        computed: {
            docDate () {
                var date = new Date (this.doc.created_at);
                var year = date.getFullYear();
                var month = (1 + date.getMonth()).toString();
                month = month.length > 1 ? month : '0' + month;
                var day = date.getDate().toString();
                day = day.length > 1 ? day : '0' + day;
                return year + '-' + month + '-' + day;
            }
        },
        methods: {
            viewApi() {
                const query = {
                    file: this.doc
                };
                return rootUrl('/download-care-document/' + this.$parent.patient.id + '/' + this.doc.id);
            }
        }
    }
</script>

<style>

    .panel {
        border: 0;
        width: 250px;
        height: 300px;
        border-radius: 5px;
    }

    .panel-primary>.panel-heading {
        background-color: #5cc0dd;
        border-color:  #5cc0dd;
        font-family: Roboto;
    }


    h4 {
        color: #ffffff;
    }


    .panel-body {
        padding: 5px;
        font-family: Roboto;
    }

    .panel-section{
        margin-bottom: 10px;

    }

    .btn-method{
        border-color: #5cc0dd;
        width: 100px;
        max-height: 30px;
        margin: 2px;
    }
</style>