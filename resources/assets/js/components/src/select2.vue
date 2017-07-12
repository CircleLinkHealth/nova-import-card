<template>
    <select>
        <slot></slot>
        <option v-for="option in options" :value="option.id" :selected="option.id == selected">{{option.value}}</option>
    </select>
</template>

<script>
    export default {
        props: ['options', 'selected'],
        template: '#select2-template',
        mounted: function () {
            var vm = this
            $(this.$el)
            // init select2
                .select2({data: this.options})
                .val(this.selected)
                .trigger('change')
                // emit event on change.
                .on('change', function () {
                    vm.$emit('input', this.selected)
                })
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value).trigger('change');
            },
            options: function (options) {
                // update options
                $(this.$el).select2({data: options})
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>