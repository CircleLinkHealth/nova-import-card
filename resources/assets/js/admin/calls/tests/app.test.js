import Vue from 'vue'
import VueResource from 'vue-resource'
import VueResourceMock from 'vue-resource-mock'
import { mount } from 'vue-test-utils'
import CallMgmtApp from '../app.vue'
import TextEditable from '../comps/text-editable'
import DateEditable from '../comps/date-editable'
import SelectEditable from '../comps/select-editable'
import TimeEditable from '../comps/time-editable'

const adminCallsMock = {
    ['GET /api/admin/calls?page=1'] (pathMatch, query, request) {
        let body = {
            data: {
                data: []
            }
        }
        return {
            body: body,
            status: 200,
            statusText: 'OK',
            headers: {

            },
            delay: 500
        }
    }
}

describe('CallMgmtApp', () => {
    Vue.use(VueResource)
    Vue.use(VueResourceMock, adminCallsMock)
    
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