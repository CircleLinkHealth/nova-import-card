<template>
    <select><slot></slot></select>
</template>

<script>
    export default {
        props: ['value'],
        watch: {
            value: function (value) {

                this.reload(value);
            }
        },
        methods: {
            reload: function (value) {

                var select = $(this.$el);

                select.val(value || this.value);
                select.material_select('destroy');
                select.material_select();
            }
        },
        mounted: function () {

            var vm = this;
            var select = $(this.$el);

            select
                .val(this.value)
                .on('change', function () {

                    vm.$emit('input', this.value);
                });

            select.material_select();
        },
        updated: function () {

            this.reload();
        },
        destroyed: function () {

            $(this.$el).material_select('destroy');
        }
    }
</script>