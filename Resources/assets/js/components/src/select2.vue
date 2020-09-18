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

        //wrong naming here, options is in fact data
        //so i added settings, which basically is options
        props: ['options', 'value', 'settings'],

        mounted: function () {
            const self = this

            // init select2
            const el = $(this.$el);

            if (this.settings) {
                el.select2(this.settings);
            }
            else {
                el.select2({data: this.options});
            }

            el
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
                $(this.$el).select2({data: options})
            },
            settings: function (settings) {
                $(this.$el).select2(settings);
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>