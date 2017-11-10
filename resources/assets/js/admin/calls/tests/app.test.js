import Vue from 'vue'
import App from '../app.vue'

describe('App', () => {
    it('has a mounted() hook', () => {
        expect(typeof(App.mounted)).toEqual('function')
    })
})