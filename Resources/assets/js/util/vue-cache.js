export default {
    data() {
        return {
            cacheProvider: {}
        }
    },
    methods: {
        cache() {
            return {
                get: (url) => {
                    if (!this.cacheProvider[url]) {
                        return this.cacheProvider[url] = this.axios.get(url).then(response => {
                            return this.cacheProvider[url] = response.data
                        }).catch(err => {
                            console.error(err)
                            delete this.cacheProvider[url]
                            Promise.reject(err)
                        })
                    }
                    else if (this.cacheProvider[url] instanceof Promise){
                        return this.cacheProvider[url]
                    }
                    else {
                        return new Promise((resolve) => resolve(this.cacheProvider[url]))
                    }
                }
            }
        }
    }
}