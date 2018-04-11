import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import MockAdapter from 'axios-mock-adapter'
import { mount } from 'vue-test-utils'
import CallMgmtApp from '../app.vue'
import TextEditable from '../comps/text-editable'
import DateEditable from '../comps/date-editable'
import SelectEditable from '../comps/select-editable'
import TimeEditable from '../comps/time-editable'

Vue.use(VueAxios, axios)

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

describe('CallMgmtApp', () => {
    
    const comp = mount(CallMgmtApp)

    it('has a mounted() hook', () => {
        expect(typeof(CallMgmtApp.mounted)).toEqual('function')
    })

    it('is called "CallMgmtApp"', () => {
        expect(CallMgmtApp.name).toEqual('CallMgmtApp')
    })

    it('contains a TextEditable component', () => {
        expect(comp.contains(TextEditable)).toBe(true)
    })
})

describe('CallMgmtApp-Main', () => {
    it('builds successfully', () => {
        const MainApp = require('../main')
    })
})