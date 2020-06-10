<template>
    <div>
        <modal ref="assignCallbackModal" name="assign-callback" class="modal-assign-callback" :no-footer="true">
            <template class="modal-container">
                <template slot="title">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3>Assign Callback to Care Ambassador</h3>
                        </div>
                    </div>
                </template>
                <template class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div>
                                <p>Select Care Ambassador:</p>
                            </div>
                            <div>
                                <v-select max-height="200px" class="form-control" v-model="selectedAmbassador"
                                          :options="ambassadorList">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <p ref="title">Select Enrollee:</p>
                            </div>
                            <div class="suggest-div">
                                <v-suggest class="asdad" pattern="\w+"
                                           v-model="model"
                                           :list="getList"
                                           :max-suggestions="10"
                                           :min-length="3"
                                           :debounce="200"
                                           :filter-by-query="false"
                                           :controls="{
          selectionUp: [38, 33],
          selectionDown: [40, 34],
          select: [13, 36],
          showList: [40],
          hideList: [27, 35]
        }"
                                           mode="input"
                                           :nullable-select="true"
                                           ref="suggestComponent"
                                           placeholder="Search Patient..."
                                           value-attribute="id"
                                           display-attribute="text"
                                           @select="onSuggestSelect"
                                           @request-start="onRequestStart"
                                           @request-done="onRequestDone"
                                           @request-failed="onRequestFailed"
                                >
                                    <!-- <input type="text"> -->

                                    <div class="g">
                                        <input type="text">
                                    </div>

                                    <!-- <test-input placeholder="Search information..." /> -->

                                    <template slot="misc-item-above" slot-scope="{ suggestions, query }">
                                        <div class="misc-item">
                                            <span>You're searching for '{{ query }}'.</span>
                                        </div>

                                        <template v-if="suggestions.length > 0">
                                            <div class="misc-item">
                                                <span>{{ suggestions.length }} suggestions are shown...</span>
                                            </div>
                                            <hr>
                                        </template>

                                        <div class="misc-item" v-else-if="!loading">
                                            <span>No results</span>
                                        </div>
                                    </template>

                                    <div slot="suggestion-item" slot-scope="scope" :title="scope.suggestion.description">
                                        <div class="text">
                                            <span v-html="boldenSuggestion(scope)"></span>
                                        </div>
                                    </div>

                                    <div class="misc-item" slot="misc-item-below" slot-scope="{ suggestions }"
                                         v-if="loading">
                                        <span>Loading...</span>
                                    </div>
                                </v-suggest>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <p>Select Date</p>
                            </div>
                            <div>
                                <input  class="form-control" type="date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <notifications ref="notificationsComponent" name="assign-callback-modal"></notifications>
                        </div>
                    </div>
<!--                    <loader v-if="loading"/>-->
                </template>
            </template>
        </modal>
    </div>

</template>

<script>

    import {rootUrl} from '../../../../app.config.js';
    import Modal from '../../../common/modal';
    import Notifications from '../../../../components/notifications';
    import Loader from '../../../../components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    import VueSimpleSuggest from 'vue-simple-suggest'
    import 'vue-simple-suggest/dist/styles.css' //


    export default {
        name: "assign-callback",
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect,
            'v-suggest': VueSimpleSuggest
        },
        data() {
            return {
                selectedAmbassador: null,
                ambassadorList: [],
                selected: null,
                model: null,
                mode: 'input',
                loading: false,
                log: []
            }
        },
        methods: {
            boldenSuggestion(scope) {
                if (!scope) return scope;
                const {suggestion, query} = scope;
                let result = this.$refs.suggestComponent.displayProperty(suggestion);
                if (!query) return result;
                const texts = query.split(/[\s-_/\\|\.]/gm).filter(t => !!t) || [''];
                return result.replace(new RegExp('(.*?)(' + texts.join('|') + ')(.*?)', 'gi'), '$1<b>$2</b>$3');
            },
            goto(url) {
                window.open(url, '_blank').focus()
            },
            onSuggestSelect(suggest) {
                this.selected = suggest
            },
            onRequestStart(value) {
                this.loading = true

            },
            onRequestDone(e) {
                this.loading = false
            },
            onRequestFailed(e) {
                this.loading = false
            },
            getList(inputValue) {
                return new Promise((resolve, reject) => {
                    let url = rootUrl('admin/ca-director/queryEnrollable') + `?enrollables=${inputValue}`
                    // this.$refs.suggestComponent.clearSuggestions()
                    this.axios.get(url).then(response => {
                        if (response.status !== 200) {
                            reject()
                        }

                        let result = []


                        response.data.forEach(el => {
                            result.push({
                                text: el.hint,
                                description: el.hint,
                                id: el.id
                            })
                        })


                        resolve(result)


                    }).catch(error => {
                        this.loading = false
                        reject(error)
                    })
                })
            }
        },
        mounted() {
            const self = this;

            Event.$on('ambassadors-loaded', (ambassadors) =>{
                this.ambassadorList = ambassadors
            })
        }
    }
</script>

<style>

    .modal-assign-callback .modal-container {
        width: 80%;
        height: 60%;
    }

    #app .example {
        width: 506px;
    }

    #app pre.selected {
        margin: 8px 0;
        height: 295px;
        overflow-x: scroll;
        overflow-y: scroll;
        border: 1px solid #cde;
        border-radius: 4px;
    }
    #app .v-model-event {
        background-color: white;
        border: 1px solid #cde;
        border-radius: 4px;
    }
    #app .v-model-event.selected {
        color: red;
    }
    #app .v-model-event:hover {
        border: 1px solid #2874D5;
        background-color: #2874D5;
        color: white;
    }
    #app .vue-simple-suggest .suggest-item .text {
        display: inline-block;
        line-height: 1;
        vertical-align: text-bottom;
        overflow: hidden;
        max-width: 72%;
        text-overflow: ellipsis;
    }
    #app .vue-simple-suggest .suggest-item .text span {
        white-space: nowrap;
    }
    #app .vue-simple-suggest .suggest-item button {
        float: right;
        line-height: 1;
        margin-left: 4px;
    }
    .vue-simple-suggest-enter-active.suggestions,
    .vue-simple-suggest-leave-active.suggestions {
        transition: opacity .2s;
    }
    .vue-simple-suggest-enter.suggestions,
    .vue-simple-suggest-leave-to.suggestions {
        opacity: 0 !important;
    }

</style>