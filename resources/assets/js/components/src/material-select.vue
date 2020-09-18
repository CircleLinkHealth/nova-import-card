<template>
    <select v-model="model" v-bind:multiple="multiple">
        <option value=""
                disabled
        >{{ selectText }}</option>
        <option v-for="(item, index) in items"
                v-bind:value="item.id"
                v-text="item[textField]"
                :key="item.id || item[textField] || index"
        ></option>
        <slot></slot>
    </select>
</template>

<script type="text/babel">
    import IsLoadable from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/mixins/is-loadable'

    export default {
        props: {
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
            }
        },

        watch: {
            items () {
                this.$el.removeAttribute('onchange');
                this.$nextTick(this.init);
            },
            value () {
                this.$el.removeAttribute('onchange');
                this.$nextTick(this.init);
            }
        },

        computed: {
            model () {
                if (this.multiple && !this.value) {
                    return []
                }

                return this.value
            }
        },

        mixins: [
            IsLoadable
        ],

        date: function () {
            return {
                isInit: false
            }
        },

        methods: {
            init () {

                //added this check so dropdown does not re-initialise at all times
                //otherwise, when multiple is enabled, the dropdown closes on every item check
                if (!this.isInit) {
                    $(this.$el).material_select();
                    this.isInit = true;
                }

                const vm = this
                this.$el.onchange = function () {
                    if (!this.multiple) {
                        vm.$emit('input', this.value)
                    } else {
                        vm.multi(this, vm)
                    }
                }
            },

            multi (context, vm) {
                const siblings = [...vm.$el.previousSibling.getElementsByClassName('active')].map(i => {
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
        }
    }
</script>
