<template>
    <div>
        <template v-if="maxChars > 0">
            <div>
                <textarea v-model="text" :id="id" :class="className" :rows="rows" :cols="cols"
                          :maxlength="maxChars"
                          :placeholder="placeholder" :name="name" :required="required" @change="changeTextArea">
                </textarea>
                <div class="character-counter">
                    <span>
                        {{charCount}} / {{maxChars}} characters
                    </span>
                </div>
            </div>
        </template>

        <template v-else>
            <textarea v-model="text" :id="id" :class="className" :rows="rows" :cols="cols"
                      :placeholder="placeholder" :name="name" :required="required" @change="changeTextArea">
            </textarea>
        </template>

    </div>
</template>

<script>
    import {sstor} from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/stor'

    export default {
        name: 'persistent-textarea',
        data() {
            return {
                text: this.value
            }
        },
        computed: {
            charCount() {
                return this.text.length;
            }
        },
        props: {
            id: String,
            className: String,
            name: String,
            value: String,
            maxChars: {
                type: Number,
                default: 0
            },
            storageKey: {
                type: String,
                required: true
            },
            required: Boolean,
            rows: {
                type: Number,
                defaultValue: 10
            },
            cols: {
                type: Number,
                defaultValue: 100
            },
            placeholder: String
        },
        methods: {
            changeTextArea() {
                sstor.add(this.storageKey, this.text);
                this.$emit('input', this.text)
            },

            clearFromStorage() {
                sstor.remove(this.storageKey);
            }
        },
        mounted() {

            const localVal = sstor.get(this.storageKey) || '';

            if (this.value && this.value.length > localVal.length) {
                sstor.remove(this.storageKey);
                this.changeTextArea();
            }
            else {
                this.text = localVal;
            }
        }
    }
</script>

<style>
    .character-counter {
        text-align: right;
        margin-top: 3px;
    }

</style>