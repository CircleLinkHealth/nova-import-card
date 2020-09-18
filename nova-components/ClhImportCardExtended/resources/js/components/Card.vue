<template>
    <card class="flex flex-col h-auto">
        <div class="px-3 py-3">
            <h1 class="text-xl font-light">Import {{this.card.resourceLabel}}</h1>
            <form @submit.prevent="processImport" ref="form">
                <div class="py-4">
                    <div v-for="field, key in this.fields">
                        <span class="flex py-6">
                        <label for="input-field">
                            {{__(getFieldTitle(field))}}
                        </label>
                        <div style="width: 100%" v-if="field.component == 'select-field'">
                            <v-select class="field-input" v-model="fields[key].value" id="input-field"  :options="field.options"></v-select>
                        </div>
                            <div v-if="field.component == 'text-field'">
                           <input   type="text" v-model="fields[key].value" class="field-input form-input"/>
                            </div>
                        </span>
                    </div>
                    <span class="form-file mr-4 py-6">
                        <input
                                ref="fileField"
                                class="form-file-input"
                                type="file"
                                :id="inputName"
                                :name="inputName"
                                @change="fileChange"
                        />
                        <label :for="inputName" class="form-file-btn btn btn-default btn-primary">
                            {{__('Choose File')}}
                        </label>
                    </span>
                    <span class="text-gray-50">
                        {{ currentLabel }}
                    </span>

                </div>

                <div class="flex">
                    <div v-if="errors">
                        <p class="text-danger mb-1" v-for="error in errors">{{error[0]}}</p>
                    </div>
                    <button
                            :disabled="working"
                            type="submit"
                            class="btn btn-default btn-primary ml-auto mt-auto"
                    >
                        <loader v-if="working" width="30"></loader>
                        <span v-else>{{__('Import')}}</span>
                    </button>
                </div>
            </form>
        </div>
    </card>
</template>

<script>
    import VueSelect from 'vue-select';

    let self;

    export default {
        props: ['card'],

        data() {
            return {
                fileName: '',
                file: null,
                label: 'no file selected',
                working: false,
                errors: null,
                fields: this.card.fields
            };
        },

        components: {
            'v-select': VueSelect
        },

        mounted() {
            self = this;
            console.log(this.fields);
        },

        methods: {
            getFieldTitle(field){
                return field.name.charAt(0).toUpperCase() + field.name.slice(1);
            },
            fileChange(event) {
                let path = event.target.value;
                let fileName = path.match(/[^\\/]*$/)[0];
                this.fileName = fileName;
                this.file = this.$refs.fileField.files[0];
            },
            processImport() {
                if (!this.file) {
                    return;
                }
                this.working = true;
                let formData = new FormData();
                formData.append('file', this.file);

                formData.append('fields', JSON.stringify(this.fields));

                Nova.request()
                    .post(
                        '/nova-vendor/clh-import-card-extended/import-csv-to-resource/' + this.card.resource,
                        formData
                    )
                    .then(({data}) => {
                        this.$toasted.success(data.message);
                        this.$parent.$parent.$parent.$parent.getResources();
                        this.errors = null;
                    })
                    .catch(({response}) => {
                        if (response.data.danger) {
                            this.$toasted.error(response.data.danger);
                            this.errors = null;
                        } else {
                            this.errors = response.data.errors;
                        }
                    })
                    .finally(() => {
                        this.working = false;
                        this.file = null;
                        this.fileName = '';
                        this.$refs.form.reset();
                    });
            },
        },
        computed: {
            /**
             * The current label of the file field
             */
            currentLabel() {
                return this.fileName || this.label;
            },

            firstError() {
                return this.errors ? this.errors[Object.keys(this.errors)[0]][0] : null;
            },

            inputName() {
                return 'file-import-input-' + this.card.resource;
            },
            inputFields() {
                return this.card ? this.card.fields : [];
            }
        },
    };
</script>
<style>
    .field-input {
        margin-left: 1rem;
        border-radius: 1rem;
    }
</style>
