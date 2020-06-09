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
                        <p ref="title">Select Enrollee:</p>
                    </div>
                    <div ref="test div" class="row">
                        <div class="example">
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
                                       placeholder="Search information..."
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
                    // let url = `https://www.googleapis.com/books/v1/volumes?printType=books&q=${inputValue}`
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

        }
    }
</script>

<style>

    .modal-assign-callback .modal-container {
        width: 80%;
        height: 60%;
    }

</style>