<template>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <div class="col-md-12  panel-section" style="margin-top: 20px">
                <div v-if="doc" class="">
                    <button class="btn btn-success  col-md-6 btn-m">Available</button>
                </div>
                <div class="" v-else>
                    <button class="btn col-md-6 btn-default btn-m">Unavailable</button></div>
                <div class="col-md-6">
                    <a  v-bind:class="{'isDisabled': !doc}" style="float: right" :href="viewApi()" target="_blank">View</a>
                </div>
            </div>
            <div>
                <div class="col-md-6" style="margin-top: 5px">
                    {{this.docDate}}
                </div>
            </div>
            <div class="col-md-12  panel-section"  style="margin-top: 15px">
                <p><strong>Send document via:</strong></p>
            </div>
            <div class="col-md-12  panel-section">
                <button class="col-md-6 btn btn-method btn-s" v-bind:class="{'isDisabled': !doc}">
                    DIRECT Msg
                </button>
                <button class="col-md-6 btn btn-method btn-s" v-bind:class="{'isDisabled': !doc}">
                    Secure Link
                </button>
            </div>
            <div class="col-md-12 panel-section">
                <a  v-bind:class="{'isDisabled': !doc}" :href="downloadApi()">Download</a>
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
                required: false
            }
        },
        computed: {
            docDate () {
                if (! this.doc){
                    return null;
                }
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
                if (! this.doc){
                    return null;
                }
                const query = {
                    file: this.doc
                };
                return rootUrl('/view-care-document/' + this.$parent.patient.id + '/' + this.doc.id);
            },
            downloadApi() {
                if (! this.doc){
                    return null;
                }
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
        padding-left: 20px;
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

    .isDisabled {
        color: currentColor;
        cursor: not-allowed;
        opacity: 0.5;
        text-decoration: none;
    }
</style>