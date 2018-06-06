import AddCallModal from '../add-call.modal'
import { mount } from 'vue-test-utils'
import mock from '../../../tests/http/calls.http'
import '../../../../../prototypes/array.prototype'
import sleep from '../../../../../util/sleep'

const createAddCallModal = (props = {}) => {
    const comp = mount(AddCallModal, {
        propsData: props
    })
    return comp.vm
}

describe('AddCallModal', () => {
    it('should mount', () => {
        const vm = createAddCallModal()
    })

    it('should should have patients', async () => {
        const vm = createAddCallModal()

        await sleep(10)

        console.log('vm.patients', vm.patients)

        expect(vm.patients.length > 0).toBeTruthy()
    })

    it('should should have practices', async () => {
        const vm = createAddCallModal()

        await sleep(10)

        expect(vm.practices.length > 0).toBeTruthy()
    })
})