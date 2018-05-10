import Vue from 'vue'
import VueAxios from 'vue-axios'
import MockAdapter from 'axios-mock-adapter'
import { rootUrl } from '../../../../app.config'
import axios from 'axios'
import PATIENTS from '../mocks/patients.mock'

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

mock.onGet('/api/patients', {
    params: {
        rows: 'all'
    }
}).reply(200, {
    current_page: 1,
    data: PATIENTS,
    from: 1,
    last_page: 1,
    path: '/api/patients',
    per_page: 3,
    to: 3
})

Vue.use(VueAxios, axios)

export default mock