import UnscheduledPatientsModal from '../unscheduled-patients.modal.vue'
import Vue from 'vue'
import axios from 'axios'
import { shallow } from 'vue-test-utils'
import mock from '../../../tests/http/calls.http'
import '../../../../../prototypes/array.prototype'
import sleep from '../../../../../util/sleep'

describe('UnscheduledPatientsModal', () => {
    const comp = shallow(UnscheduledPatientsModal)

    const $vm = comp.vm;

    it('has a mounted() hook', () => {
        expect(typeof(UnscheduledPatientsModal.mounted)).toEqual('function')
    })

    it('is called "unscheduled-patients-modal"', () => {
        expect(UnscheduledPatientsModal.name).toEqual('unscheduled-patients-modal')
    })

    it('should have patients', async () => {
        expect($vm.patients.length > 0).toBeTruthy()
    })

    it('should have practices', async () => {
        expect($vm.practices.length > 0).toBeTruthy()
    })

    it('should have practiceId as NULL', async () => {
        expect(!$vm.practiceId).toBeTruthy()
    })

    describe('Computed Patient URL', () => {
        const comp = shallow(UnscheduledPatientsModal)
        const $vm = comp.vm;

        it('should NOT include practice', () => {
            $vm.practiceId = null
            expect($vm.patientUrl).toEqual('/api/patients/without-scheduled-activities?autocomplete')
        })

        it('should include practice', () => {
            $vm.practiceId = 1
            expect($vm.patientUrl).toEqual('/api/practices/1/patients/without-scheduled-activities?autocomplete')
        })
    })
})