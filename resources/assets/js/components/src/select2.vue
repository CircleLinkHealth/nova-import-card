<template>
    <select>
        <slot></slot>
    </select>
</template>

<script>
    import $ from 'jquery'
    import select2 from 'select2'

    export default {
        name: 'select2',

        props: ['options', 'value'],

        mounted: function () {
            const self = this
            $(this.$el)
            // init select2
                .select2({ data: this.options })
                .val(this.value)
                .trigger('change')
                // emit event on change.
                .on('change', function () {
                    self.$emit('input', this.value)
                    self.$emit('change', this.value)
                })
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value).trigger('change');
            },
            options: function (options) {
                // update options
                $(this.$el).select2({ data: options })
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>