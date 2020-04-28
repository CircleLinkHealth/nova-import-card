/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

const axios = require('axios');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */
if (window && document) {
    let token = document.head.querySelector('meta[name="csrf-token"]');

    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    } else {
        console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    }

    axios.default.interceptors.response.use(response => {
        return response
    }, error => {

        //request was cancelled, ignore
        if (error && error.constructor.name === "Cancel") {
            return;
        }

        if (error && error.response && error.response.status === 419) {
            window.location.href = '/auth/inactivity-logout'
        }
        else {
            return Promise.reject(error);
        }
    })

    window.axios = axios
}

export default axios
