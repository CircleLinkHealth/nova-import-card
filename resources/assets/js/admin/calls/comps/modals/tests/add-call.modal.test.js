import AddCallModal from '../add-call.modal'
import { mount } from 'vue-test-utils'
import mock from '../../../tests/http/calls.http'
import '../../../../../prototypes/array.prototype'
import sleep from '../../../../../util/sleep'

const PRACTICE_ID = 8

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

    /**
     * because of UNASSIGNED
     */
    it('should should have patientsForSelect.length == patients.length + 1', async () => {
        const vm = createAddCallModal()

        await sleep(10)

        expect(vm.patients.length + 1).toEqual(vm.patientsForSelect.length)
    })

    /**
     * because of UNASSIGNED
     */
    it('should should have practicesForSelect.length == practices.length + 1', async () => {
        const vm = createAddCallModal()

        await sleep(10)

        expect(vm.practices.length + 1).toEqual(vm.practicesForSelect.length)
    })

    /**
     * because of UNASSIGNED
     */
    it('should should have nursesForSelect.length == nurses.length + 1', async () => {
        const vm = createAddCallModal()

        await sleep(10)

        expect(vm.nurses.length + 1).toEqual(vm.nursesForSelect.length)
    })

    describe('Change Practice', () => {
        it('should load nurses', async () => {
            const vm = createAddCallModal()
    
            await sleep(10)

            await vm.changePractice({ value: PRACTICE_ID })

            await sleep(10)

            expect(vm.formData.practiceId).toEqual(PRACTICE_ID)
    
            expect(vm.nurses.length > 0).toBeTruthy()
        })
    })
})