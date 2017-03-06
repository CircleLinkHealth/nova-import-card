var Vue = require('vue');

Vue.directive("select2", {
    "twoWay": true,

    "bind": function () {
        $(this.el).select2();

        var self = this;

        $(this.el).on('change', function () {
            self.set($(self.el).val());
        });
    },

    update: function (newValue, oldValue) {
        $(this.el).val(newValue);
    },

    "unbind": function () {
        $(this.el).select2('destroy');
    }
});