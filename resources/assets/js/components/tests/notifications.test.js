import Notifications from '../notifications'
import { mount } from 'vue-test-utils'

describe('Notifications', () => {
    const comp = mount(Notifications, {
        propsData: {
            
        }
    })

    const $vm = comp.vm

    it('should mount', () => {
        
    })

    it('should have a create method', () => {
        expect(typeof($vm.create)).toEqual('function')
    })
})