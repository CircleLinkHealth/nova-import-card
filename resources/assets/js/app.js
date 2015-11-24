var Vue = require('vue');
Vue.use(require('vue-resource'));

new Vue({
    el: '#app',
    data: {
        ccdRecords: new FormData,
    },
    ready: function () {
        this.watchForFileInput();
    },
    methods: {
        watchForFileInput: function () {
            $('input[type="file"]').change(this.notifyFileInput.bind(this));
        },
        notifyFileInput: function (e) {
            var files = event.target.files;
            var formData =  this.ccdRecords;

            for (var i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
            }
        },
        onSubmitForm: function (e) {
            e.preventDefault();

            this.$http.post('/upload-raw-ccds', this.ccdRecords, function (data, status, request) {
                console.log('success');
            }).error(function (data, status, request) {
                console.log('error');
            });
        }
    },
})