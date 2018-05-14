import Vue from 'vue'
import VueAxios from 'vue-axios'
import MockAdapter from 'axios-mock-adapter'
import { rootUrl } from '../../../../app.config'
import axios from 'axios'
import PATIENTS from '../mocks/patients.mock'
import NURSES from '../mocks/nurses.mock'
import CALLS from '../mocks/calls.mock'

const mock = new MockAdapter(axios)

const callsResponse = {
    data: CALLS,
    links: {
        first: '/api/admin/calls?page=1',
        last: '/api/admin/calls?page=2038',
        prev: null,
        next: '/api/admin/calls?page=2'
    },
    meta: {
        current_page: 1,
        from: 1,
        last_page: 2038,
        path: '/api/admin/calls',
        per_page: 15,
        to: 15,
        total: 30561
    }
}

mock.onGet('/api/admin/calls?scheduled&rows=undefined').reply(200, callsResponse)
mock.onGet('/api/admin/calls?scheduled&rows=100').reply(200, callsResponse)
mock.onGet('/api/admin/calls?page=1').reply(200, callsResponse)

mock.onGet('/api/practices').reply(200, [
    { id:2, display_name:'No Access', locations:0 },
    { id:7, display_name:'Crisfield', locations:0 },
    { id:8, display_name:'Demo', locations:2 }
])

mock.onGet('/api/patients?rows=all').reply(200, {
    current_page: 1,
    data: PATIENTS,
    from: 1,
    last_page: 1,
    path: '/api/patients',
    per_page: 3,
    to: 3
})

mock.onGet('/api/patients/without-scheduled-calls').reply(200, {
    current_page: 1,
    data: PATIENTS,
    from: 1,
    last_page: 1,
    path: '/api/patients/without-scheduled-calls',
    per_page: 3,
    to: 3
})

mock.onGet('/api/nurses?compressed').reply(200, {
    data: NURSES
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'scheduled_date',
    value: '2018-05-13'
}).reply(200, {
    date: '2018-05-13'
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'outbound_cpm_id',
    value: 1920
}).reply(200, {
    outbound_cpm_id: 1920
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'window_start',
    value: '12:00'
}).reply(200, {
    window_start: '12:00'
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'window_end',
    value: '13:00'
}).reply(200, {
    window_end: '13:00'
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'general_comment',
    value: '...'
}).reply(200, {
    general_comment: '...'
})

mock.onPost('/callupdate', {
    callId: 1,
    columnName: 'attempt_note',
    value: '...'
}).reply(200, {
    attempt_note: '...'
})

Vue.use(VueAxios, axios)

export default mock