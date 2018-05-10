import Vue from 'vue'
import VueAxios from 'vue-axios'
import MockAdapter from 'axios-mock-adapter'
import { rootUrl } from '../../../app.config'
import axios from 'axios'

const mock = new MockAdapter(axios)

mock.onGet('/api/admin/calls', { 
    params: {
        page: 1
    }
 }).reply(200, {
    data: {
        data: []
    }
})

mock.onGet('/api/practices').reply(200, [
    { id:2, display_name:'No Access', locations:0 },
    { id:7, display_name:'Crisfield', locations:0 },
    { id:8, display_name:'Demo', locations:2 }
])

Vue.use(VueAxios, axios)

export default mock