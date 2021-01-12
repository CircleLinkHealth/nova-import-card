import SelectTimesModal from '../select-times.modal'
import { mount } from 'vue-test-utils'
import { today } from '../../../../../util/today'

const CreateSelectTimesModal = (props) => {
    const comp = mount(SelectTimesModal, {
        propsData: props
    })
    return comp.vm
}

describe('SelectTimesModal', () => {
    it('mounts', () => {
        const vm = CreateSelectTimesModal({
            selectedPatients: []
        })
    })

    it('mounts with selected patients', () => {
        const vm = CreateSelectTimesModal({
            selectedPatients: [
                {
                    id: 1,
                    name: 'Mary Cynthia',
                    nextCall: today(),
                    callTimeStart: '09:00',
                    callTimeStart: '17:00'
                }
            ]
        })

        expect(vm.selectTimesModalInfo.okHandler().nextCall).toEqual(today())
    })
})