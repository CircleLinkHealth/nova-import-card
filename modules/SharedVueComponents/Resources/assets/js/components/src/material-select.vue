<template>
    <div>
        <select ref="selectRef" v-model="model" v-bind:multiple="multiple" :class="{invalid: errors.get(name)}" :name="name" :id="id">
            <option value="" :disabled="!allowNoSelect">{{ selectText }}
            </option>
            <option v-for="(item, index) in items"
                    v-bind:value="item.id"
                    v-text="item[textField]"
                    :key="item.id || item[textField] || index"
            ></option>
            <slot></slot>
        </select>
        <p class="validation-error">{{errors.get(name)}}</p>
        <label v-if="labelText.length" :for="name" class="active">{{labelText}}</label>
    </div>
</template>

<script type="text/babel">
    import IsLoadable from '../../mixins/is-loadable'
    import {mapActions, mapGetters} from "vuex";

    export default {
        props: {
            id: {
                type: String,
                default: ''
            },
            name: {
                type: String,
                default: ''
            },
            labelText: {
                type: String,
                default: ''
            },
            items: {
                type: Array,
                default: () => []
            },
            multiple: {
                type: Boolean,
                default: false
            },
            selectText: {
                type: String,
                default: 'Choose your option'
            },
            value: {
                default: '',
                required: false
            },
            textField: {
                type: String,
                default: 'text'
            },
            className: {
                default: '',
                required: false
            },
            allowNoSelect: {
                type: Boolean,
                default: false
            }
        },

        watch: {
            items () {
                this.$refs.selectRef.removeAttribute('onchange');
                this.$nextTick(this.init);
            },
            value () {
                this.$refs.selectRef.removeAttribute('onchange');
                this.$nextTick(this.init);
            }
        },

        computed: Object.assign(
            mapGetters({
                errors: 'errors'
            }),{

                model () {
                    if (this.multiple && !this.value) {
                        return []
                    }

                    return this.value
                }
            }),

        mixins: [
            IsLoadable
        ],

        date: function () {
            return {
                isInit: false
            }
        },

        methods: Object.assign(
            mapActions(['clearErrors']), {
                init () {

                    if (!this.$refs.selectRef) {
                        return;
                    }

                    //added this check so dropdown does not re-initialise at all times
                    //otherwise, when multiple is enabled, the dropdown closes on every item check
                    if (!this.isInit) {
                        $(this.$refs.selectRef).material_select();
                        this.isInit = true;
                    }

                    const vm = this
                    this.$refs.selectRef.onchange = function () {
                        if (!this.multiple) {
                            vm.$emit('input', this.value)
                        } else {
                            vm.multi(this, vm)
                        }
                    }
                },

                multi (context, vm) {
                    const siblings = [...vm.$refs.selectRef.previousSibling.getElementsByClassName('active')].map(i => {
                        return i.getElementsByTagName('label')[0].nextSibling.nodeValue
                    })

                    const options = [...context.getElementsByTagName('option')]
                    let array = []

                    siblings.forEach(i => {
                        const option = options.find(j => j.textContent == i)

                        if (option) {
                            array.push(option.value)
                        }
                    })

                    vm.$emit('input', array)
                }
            })
    }
</script>
