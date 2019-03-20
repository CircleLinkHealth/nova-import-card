import Vue from 'vue'
import VueAxios from 'vue-axios'
import MockAdapter from 'axios-mock-adapter'
import { rootUrl } from '../../../../app.config'
import { today } from '../../../../util/today'
import axios from 'axios'
import PATIENTS from '../mocks/patients.mock'
import NURSES from '../mocks/nurses.mock'
import CALLS from '../mocks/calls.mock'
import PRACTICES from '../mocks/practices.mock';

const mock = new MockAdapter(axios)

const callsResponse = {
    data: CALLS,
    links: {
        first: '/api/admin/calls?page=1',
        last: '/api/admin/calls?page=34',
        prev: null,
        next: '/api/admin/calls?page=2'
    },
    meta: {
        current_page: 1,
        from: 1,
        last_page: 34,
        path: '/api/admin/calls',
        per_page: 15,
        to: 15,
        total: 500
    }
}

mock.onGet('/api/admin/calls?scheduled&rows=undefined').reply(200, callsResponse)
mock.onGet(`/api/admin/calls?scheduled&rows=100&minScheduledDate=${today()}`).reply(200, callsResponse)
mock.onGet(`/api/admin/calls?scheduled&minScheduledDate=${today()}`).reply(200, callsResponse)
mock.onGet('/api/admin/calls?page=1').reply(200, callsResponse)

mock.onDelete('/api/admin/calls/2,3').reply(200, [2, 3])

/** end patient calls */

mock.onGet('/api/practices').reply(200, PRACTICES)

/** patient data */

const patientUrls = [
    '/api/patients?rows=all',
    '/api/patients/without-scheduled-activities',
    '/api/practices/8/patients/without-scheduled-activities'
]

patientUrls.map((url) => {
    return mock.onGet(url).reply(200, {
        current_page: 1,
        data: PATIENTS,
        from: 1,
        last_page: 1,
        path: url.split('?')[0],
        per_page: 3,
        to: 3
    })
})

mock.onGet('/api/practices/8/patients').reply(200, PATIENTS)

/** end patient data */

/** patient autocomplete */

const autocompletePatientUrls = [
    '/api/patients/without-scheduled-activities?autocomplete'
]

const AUTOCOMPLETE_PATIENTS = PATIENTS.map(patient => ({ id: patient.id, 
                                                        name: patient.name, 
                                                        program_id: patient.program_id }))

autocompletePatientUrls.map((url) => {
    return mock.onGet(url).reply(200, AUTOCOMPLETE_PATIENTS)
})

mock.onGet('/api/patients?rows=all&autocomplete').reply(200, {
    current_page: 1,
    data: AUTOCOMPLETE_PATIENTS,
    from: 1,
    last_page: 1,
    path: '/api/patients',
    per_page: 3,
    to: 3
})

/** end patient autocomplete */

/** begin nurses */

const nurseUrls = [
    '/api/nurses?compressed',
    '/api/nurses?canCallPatient=1'
]

nurseUrls.map((url) => mock.onGet(url).reply(200, {
    data: NURSES
}))

mock.onGet('/api/practices/8/nurses').reply(200, NURSES)

/** end nurses */

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

/** begin callcreate */

mock.onPost('/callcreate', {
    inbound_cpm_id: 334,
    outbound_cpm_id: 1920,
    scheduled_date: today(),
    window_start: '09:00',
    window_end: '17:00',
    attempt_note: '...'
}).reply(201, {
    id: 34404,
    note_id: null,
    service: 'phone',
    status: 'scheduled',
    inbound_phone_number: null,
    outbound_phone_number: null,
    inbound_cpm_id: 334,
    outbound_cpm_id: 1920,
    call_time: null,
    created_at: `${today()}T05:26:29-04:00`,
    updated_at: `${today()}T05:26:29-04:00`,
    is_cpm_outbound: 1,
    window_start: '09:00',
    window_end: '17:00',
    scheduled_date: today(),
    called_date: null,
    attempt_note: '...',
    scheduler: 'CLH',
    sort_day: null
})

/** end callcreate */

Vue.use(VueAxios, axios)

export default mock