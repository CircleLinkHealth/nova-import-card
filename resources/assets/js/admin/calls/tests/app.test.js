import Vue from 'vue'
import axios from 'axios'
import { mount } from 'vue-test-utils'
import CallMgmtApp from '../app.vue'
import TextEditable from '../comps/text-editable'
import DateEditable from '../comps/date-editable'
import SelectEditable from '../comps/select-editable'
import TimeEditable from '../comps/time-editable'
import mock from './http/calls.http'
import '../../../prototypes/array.prototype'
import { ClientTable } from 'vue-tables-2'

Vue.use(ClientTable, {}, false)

describe('CallMgmtApp', () => {
    
    const comp = mount(CallMgmtApp)

    it('has a mounted() hook', () => {
        expect(typeof(CallMgmtApp.mounted)).toEqual('function')
    })

    it('is called "CallMgmtApp"', () => {
        expect(CallMgmtApp.name).toEqual('CallMgmtApp')
    })
})

describe('CallMgmtApp-Main', () => {
    it('builds successfully', () => {
        const MainApp = require('../main')
    })
})