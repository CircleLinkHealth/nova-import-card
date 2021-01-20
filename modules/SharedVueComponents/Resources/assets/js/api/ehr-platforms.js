export default {
    get(cb, ecb = null) {
        window.axios.get('ehrs?onlySso=1').then(
            (resp) => cb(resp.data),
            (error) => {
                if (typeof ecb === 'function' && error && error.response && error.response.data) {
                    if (error.response.data.errors) {
                        ecb(error.response.data.errors);
                    }
                    else {
                        ecb(error.response.data.message);
                    }
                }
            }
        );
    },
}
