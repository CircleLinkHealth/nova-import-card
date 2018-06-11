import { shallow } from 'vue-test-utils'
import CallMgmtApp from '../app.vue'
import mock from './http/calls.http'
import '../../../prototypes/array.prototype'
import sleep from '../../../util/sleep'
import { Event } from 'vue-tables-2'

const createCallMgmtModal = (props = {}) => {
    const comp = shallow(CallMgmtApp, {
        propsData: props
    })
    return comp.vm
}

describe('CallMgmtApp', () => {
    const vm = createCallMgmtModal()

    it('renders successfully', () => {
    })

    it('has a mounted() hook', () => {
        expect(typeof(CallMgmtApp.mounted)).toEqual('function')
    })

    it('is called "CallMgmtApp"', () => {
        expect(CallMgmtApp.name).toEqual('CallMgmtApp')
    })

    it('should contain data', () => {
        expect(vm.tableData.length > 0).toBeTruthy()
        expect(vm.tableData.length).toBe(500)
    })

    describe('columnMapping', () => {
        it('should map "CCM Time" to "ccmTime"', () => {
            expect(vm.columnMapping('CCM Time')).toEqual('ccmTime')
        })

        it('should map "Hello World" to "helloWorld"', () => {
            expect(vm.columnMapping('Hello World')).toEqual('helloWorld')
        })
    })

    describe('Filters', () => {
        it('should be empty initially', async () => {
            expect(Object.keys(vm.getFilters()).length).toEqual(0)
        })

        describe('urlFilterSuffix', () => {
            it('should be empty initially', () => {
                expect(vm.urlFilterSuffix()).toEqual('')
            })
        })
    })

    describe('toggleSelect', () => {
        const vm = createCallMgmtModal()

        it('should select 1st item', () => {
            vm.toggleSelect(1)
            const row = vm.tableData.find(row => row.id === 1)
            expect(row).toBeTruthy()
            expect(row.selected).toBeTruthy()
        })
    })

    describe('deleteSelected', () => {
        it('should reject with "no selected items"', async () => {
            const vm = createCallMgmtModal()
            expect(vm.tableData.filter(row => !!row.selected).length).toEqual(0)
            expect(vm.deleteSelected()).rejects.toEqual('no selected items')
        })

        it('should reject with "no confirmation"', async () => {
            vm.toggleSelect(2)
            expect(vm.deleteSelected()).rejects.toEqual('no confirmation')
        })

        it('should reject with "no confirmation"', async () => {
            vm.toggleSelect(3)
            const selectedIDs = vm.tableData.filter(row => row.selected).map(row => row.id)
            expect(await vm.deleteSelected({}, true)).toEqual(selectedIDs)
        })
    })

    describe('Events', () => {
        it('should assign-selected-to-nurse', () => {
            let modalIsVisible = false
            Event.$on('modal-select-nurse:show', () => {
                modalIsVisible = true
            })
            expect(modalIsVisible).toBeFalsy()
            vm.assignSelectedToNurse()
            expect(modalIsVisible).toBeTruthy()
        })
        it('should assign-times-for-selected', () => {
            let modalSelectTimesShow = false
            Event.$on('modal-select-times:show', () => {
                modalSelectTimesShow = true
            })
            expect(modalSelectTimesShow).toBeFalsy()
            vm.assignTimesForSelected()
            expect(modalSelectTimesShow).toBeTruthy()
        })
        it('should add-call', () => {
            let modalAddCallShow = false
            Event.$on('modal-add-call:show', () => {
                modalAddCallShow = true
            })
            expect(modalAddCallShow).toBeFalsy()
            vm.addCall()
            expect(modalAddCallShow).toBeTruthy()
        })
        it('should show-unscheduled-patients-modal', () => {
            let modalUnscheduledPatientShow = false
            Event.$on('modal-unscheduled-patients:show', () => {
                modalUnscheduledPatientShow = true
            })
            expect(modalUnscheduledPatientShow).toBeFalsy()
            vm.showUnscheduledPatientsModal()
            expect(modalUnscheduledPatientShow).toBeTruthy()
        })
    })
})