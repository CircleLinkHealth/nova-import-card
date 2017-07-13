<style>
    .select2-container {
        z-index: 10000;
    }

    select {
        min-width: 300px;
    }
</style>

<template>
    <select>
        <slot></slot>
    </select>
</template>

<script>
    import select2 from 'select2'

    export default {
        props: ['options', 'value'],

        mounted: function () {
            var vm = this
            $(this.$el)
            // init select2
                .select2({ data: this.options })
                .val(this.value)
                .trigger('change')
                // emit event on change.
                .on('change', function () {
                    vm.$emit('input', this.value)
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