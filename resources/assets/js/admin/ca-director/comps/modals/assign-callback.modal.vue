<template>
    <div>
        <modal ref="assignCallbackModal" name="assign-callback" class="modal-assign-callback"
               :info="assignCallbackModalInfo" :no-footer="true">
            <template class="modal-container">
                <template slot="title">
                    <div class="row">
                        <div class="col-sm-6">
                            <h4>Assign Callback to Care Ambassador</h4>
                        </div>
                    </div>
                </template>
                <template class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div style="height: 30px">
                                <span ref="title">Enrollee <span class="required">*</span></span>
                            </div>
                            <div class="suggest-div">
                                <v-suggest pattern="\w+"
                                           v-model="model"
                                           :list="getList"
                                           :max-suggestions="10"
                                           :min-length="1"
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

                                    <div>
                                        <input class="form-control" type="text">
                                    </div>

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

                                    <div slot="suggestion-item" slot-scope="scope"
                                         :title="scope.suggestion.description">
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
                                <p>Care Ambassador<span class="required">*</span></p>
                            </div>
                            <div>
                                <v-select max-height="200px" class="form-control" v-model="selectedAmbassador"
                                          :options="ambassadorList">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <p>Callback Date<span class="required">*</span></p>
                            </div>
                            <div>
                                <input class="form-control" :min="today" v-model="callback_date" type="date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div>
                                <p>Note</p>
                            </div>
                            <div>
                                <input class="form-control" v-model="callback_note"
                                       placeholder="Add note for Care Ambassador..." type="text">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <notifications ref="notificationsComponent" name="assign-callback-modal"></notifications>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12"><loader class="assign-loader" v-if="loading"/></div>
                    </div>
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
    import {Event} from 'vue-tables-2';
    import moment from 'moment';

    import VueSimpleSuggest from 'vue-simple-suggest';
    import 'vue-simple-suggest/dist/styles.css';

    let self;

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
                callback_date: null,
                callback_note: null,
                selectedAmbassador: null,
                ambassadorList: [],
                selected: null,
                model: null,
                mode: 'input',
                loading: false,
                assignCallbackModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-assign-callback-modal:dismissAll');
                        this.assignCallbackToAmbassador();
                    },
                    cancelHandler: () => {
                        this.selected = null;
                        this.selectedAmbassador = null;
                        this.callback_date = moment().format('YYYY-MM-DD');
                        this.callback_note = null;
                        this.model = null;
                        Event.$emit('notifications-assign-callback-modal:dismissAll');
                        Event.$emit("modal-assign-callback:hide");
                    }
                }
            }
        },
        computed: {
            today() {
                return moment().format('YYYY-MM-DD');
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
                    this.$refs.suggestComponent.clearSuggestions()
                    this.axios.get(url).then(response => {
                        if (response.status !== 200) {
                            reject()
                        }

                        let result = [];


                        response.data.forEach(el => {
                            result.push({
                                text: el.hint,
                                description: el.hint,
                                id: el.id
                            })
                        })


                        resolve(result);


                    }).catch(error => {
                        this.loading = false;
                        reject(error)
                    })
                })
            },
            assignCallbackToAmbassador() {

                this.loading = true;

                if (! this.selected){
                    Event.$emit('notifications-assign-callback-modal:create', {
                        noTimeout: true,
                        text: 'Please select a Enrollee to proceed',
                        type: 'error'
                    });
                    this.loading = false;
                    return;
                }

                if (! this.selectedAmbassador){
                    Event.$emit('notifications-assign-callback-modal:create', {
                        noTimeout: true,
                        text: 'Please select a Care Ambassador to proceed',
                        type: 'error'
                    });
                    this.loading = false;
                    return;
                }

                this.axios.post(rootUrl('/admin/ca-director/assign-callback'), {
                    care_ambassador_user_id: this.selectedAmbassador.value,
                    enrollee_id: this.selected.id,
                    callback_date: this.callback_date,
                    callback_note: this.callback_note
                })
                    .then(resp => {
                        this.loading = false;

                        Event.$emit('refresh-table');
                        Event.$emit("modal-assign-callback:hide");
                    })
                    .catch(err => {
                        this.loading = false;

                        Event.$emit('notifications-assign-callback-modal:create', {
                            noTimeout: true,
                            text: err.message,
                            type: 'error'
                        });
                    });
            }
        },
        created() {
            const self = this;
        },
        mounted() {
            this.callback_date = moment().format('YYYY-MM-DD');

            Event.$on('ambassadors-loaded', (ambassadors) => {
                this.ambassadorList = ambassadors;
            })
        }
    }
</script>

<style>

    .modal-assign-callback .modal-container {
        width: 80%;
        height: 40%;
    }

    span.required {
        color: red;
        font-size: 18px;
        position: absolute;
        top: 2px;
    }


    .assign-loader{
        height: 20px !important;
        width: 20px !important;
        padding-top: 10px;
        margin: auto;
    }

    .vue-simple-suggest > ul {
        max-height: 200px !important;
        overflow: scroll !important;
    }

    .modal-container {
        overflow-y: inherit !important;
    }
</style>