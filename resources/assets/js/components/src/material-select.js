var Vue = require('vue');

"use strict";

Vue.component("material-select", {
    template: '<select><slot></slot></select>',
    props: ['value'],
    watch: {
        value: function (value) {

            this.relaod(value);
        }
    },
    methods: {
        relaod: function (value) {

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

        this.relaod();
    },
    destroyed: function () {

        $(this.$el).material_select('destroy');
    }
});