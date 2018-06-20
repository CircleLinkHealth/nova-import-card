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

    describe('Get Unscheduled Patients', () => {
        it('should have patients', async () => {
            const vm = createAddCallModal()

            await sleep(10)

            await vm.changeUnscheduledPatients({ target: { checked: true } })

            expect(vm.patients.length > 0).toBeTruthy()
        })
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

    describe('Change Patient', () => {
        it('should set selectedPatient()', async () => {
            const vm = createAddCallModal()
    
            await sleep(10)

            await vm.changePractice({ value: PRACTICE_ID })

            await sleep(10)

            const patient = vm.patients[0]

            vm.changePatient({ value: patient.id })

            expect(vm.formData.patientId).toEqual(patient.id)

            expect(vm.selectedPatient()).toBeTruthy()

            expect(vm.selectedPatient().id).toEqual(patient.id)
        })

        it('should set selectedPatientIsInDraftMode', async () => {
            const vm = createAddCallModal()
    
            await sleep(10)

            await vm.changePractice({ value: PRACTICE_ID })

            await sleep(10)

            const patient = vm.patients.find(patient => patient.status == 'draft')

            expect(patient).toBeTruthy()

            vm.changePatient({ value: patient.id })

            expect(vm.selectedPatientIsInDraftMode).toBeTruthy()
        })
    })

    describe('Submit Form', () => {
        it('should return a callupdate value', async () => {
            const vm = createAddCallModal()
    
            await sleep(10)

            await vm.changePractice({ value: PRACTICE_ID })

            await sleep(10)

            const patient = vm.patients[0]

            vm.changePatient({ value: patient.id })

            const nurse = vm.nurses[0]

            expect(nurse).toBeTruthy()

            vm.changeNurse({ value: nurse.user_id })

            vm.formData.text = '...'

            const call = (await vm.submitForm({ preventDefault: () => ({}) }))

            expect(call).toBeTruthy()
        })
    })
})