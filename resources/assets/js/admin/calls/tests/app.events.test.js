import createAppEvents from '../app.events'
import { today } from '../../../util/today'
import { Event } from 'vue-tables-2'
import Vue from 'vue'
import { exec } from 'child_process';

describe('App Events', () => {
    it('should bind', () => {
        createAppEvents({
            activateFilters: () => ({}),
            next: () => ({}),
            $refs: {
                tblCalls: {}
            }
        },
        Event)
    })

    describe('selectTimesChangeHandler', () => {
        let executionsCount = 0
        const updateMultiValues = () => {
            return new Promise((resolve, reject) => {
                executionsCount++;
                resolve ({})
            })
        }
        const { 
            selectTimesChangeHandler
        } = createAppEvents({
            activateFilters: () => ({}),
            next: () => ({}),
            $refs: {
                tblCalls: {}
            },
            tableData: [
                {
                    id: 1,
                    updateMultiValues
                },
                {
                    id: 2,
                    updateMultiValues
                },
                {
                    id: 3,
                    updateMultiValues
                }
            ]
        },
        Event)

        it('should execute 3 times', async () => {
            await selectTimesChangeHandler({
                callIDs: [ 1, 2, 3 ],
                nextCall: today(),
                callTimeStart: '09:00',
                callTimeEnd: '17:00'
            })

            expect(executionsCount).toEqual(3)
        })
    })

    describe('modal-select-times:hide', () => {
        const updateMultiValues = () => {
            return new Promise((resolve, reject) => {
                resolve ({})
            })
        }
        it('should hide Modal', () => {
            Event.$off('select-times-modal:change')
            createAppEvents({
                activateFilters: () => ({}),
                next: () => ({}),
                $refs: {
                    tblCalls: {}
                },
                tableData: [
                    {
                        id: 1,
                        updateMultiValues
                    }
                ]
            },
            Event)
            let modalVisible = true
            Event.$on('modal-select-times:hide', () => {
                modalVisible = false
            })
            Event.$emit('select-times-modal:change', {
                callIDs: [ 1 ]
            })
            Vue.nextTick(() => {
                expect(modalVisible).toBeFalsy()
            })
        })
    })

    describe('selectNurseUpdateHandler', () => {
        const tableData = [
            {
                id: 1,
                Nurse: null,
                NurseId: null
            }
        ]
        const { 
            selectNurseUpdateHandler
        } = createAppEvents({
            activateFilters: () => ({}),
            next: () => ({}),
            $refs: {
                tblCalls: {}
            },
            tableData,
            nurses: [
                {
                    id: 1,
                    display_name: 'Maria Rose'
                }
            ]
        },
        Event)

        it('', () => {
            expect(tableData.length).toEqual(1)

            const call = tableData[0]

            expect(call.Nurse).toBeFalsy()
            expect(call.NurseId).toBeFalsy()

            selectNurseUpdateHandler({
                callId: 1,
                nurseId: 1
            })

            expect(call.Nurse).toBeTruthy()
            expect(call.NurseId).toBeTruthy()
        })
    })

    describe('nextPageHandler', () => {
        let indicator = false
        const { 
            nextPageHandler
        } = createAppEvents({
            activateFilters: () => ({}),
            next: () => {
                indicator = true
            },
            $refs: {
                tblCalls: {}
            },
            tableData: [],
            nurses: [
                {
                    id: 1,
                    display_name: 'Maria Rose'
                }
            ]
        },
        Event)

        it('should work', () => {
            expect(indicator).toBeFalsy()
            nextPageHandler()
            expect(indicator).toBeTruthy()
        })
    })

    describe('unscheduledPatientsModalFilterHandler', () => {
        const { 
            unscheduledPatientsModalFilterHandler
        } = createAppEvents({
            activateFilters: () => ({}),
            next: () => {
                indicator = true
            },
            $refs: {
                tblCalls: {}
            },
            tableData: [],
            nurses: [
                {
                    id: 1,
                    display_name: 'Maria Rose'
                }
            ]
        },
        Event)
        it('should hide unscheduled-patients modal', async () => {
            let modalVisible = true
            Event.$on('modal-unscheduled-patients:hide', () => {
                modalVisible = false
            })
            expect(modalVisible).toBeTruthy()
            await unscheduledPatientsModalFilterHandler({})
            expect(modalVisible).toBeFalsy()
        })
    })
})