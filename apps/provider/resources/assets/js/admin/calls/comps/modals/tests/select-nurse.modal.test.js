import SelectNurseModal from '../select-nurse.modal'
import mock from '../../../tests/http/calls.http'
import '../../../../../prototypes/array.prototype'
import sleep from '../../../../../util/sleep'
import { mount } from 'vue-test-utils'
import { today } from '../../../../../util/today'

const CreateSelectNurseModal = (props) => {
    const comp = mount(SelectNurseModal, {
        propsData: props
    })
    return comp.vm
}

describe('SelectNurseModal', () => {
    it('mounts', () => {
        const vm = CreateSelectNurseModal({
            selectedPatients: []
        })
    })

    it('mounts with selected patients', async () => {
        const vm = CreateSelectNurseModal({
            selectedPatients: [
                {
                    id: 1,
                    name: 'Mary Cynthia',
                    nextCall: today(),
                    callTimeStart: '09:00',
                    callTimeStart: '17:00',
                    nurse: {
                        id: 1
                    }
                }
            ]
        })

        expect(Array.isArray(await vm.selectNursesModalInfo.okHandler())).toBeTruthy()
    })

    describe('onChange', () => {
        it('should change patient\'s selectedNurseId', () => {
            const vm = CreateSelectNurseModal({
                selectedPatients: []
            })
            const patient = {
                nurses: [{
                    id: 1
                }],
                selectedNurseId: null
            }
            expect(patient.selectedNurseId).toBeFalsy()
            vm.selectNursesModalInfo.onChange({
                target: {
                    value: 1
                }
            }, patient)
            expect(patient.selectedNurseId).toEqual(1)
        })
    })
})