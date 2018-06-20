export default (App, Event) => {
    const $table = App.$refs.tblCalls;

    const nextPageHandler = (page) => {
        App.next();
    }

    Event.$on('vue-tables.pagination', nextPageHandler)

    Event.$on('vue-tables.filter::Nurse', App.activateFilters)
    
    Event.$on('vue-tables.filter::Patient', App.activateFilters)
    
    Event.$on('vue-tables.filter::Patient ID', App.activateFilters)
    
    Event.$on('vue-tables.filter::Next Call', App.activateFilters)
    
    Event.$on('vue-tables.filter::Last Call', App.activateFilters)
    
    Event.$on('vue-tables.filter::Patient Status', App.activateFilters)
    
    Event.$on('vue-tables.filter::Practice', App.activateFilters)
    
    Event.$on('vue-tables.filter::Billing Provider', App.activateFilters)
    
    Event.$on('vue-tables.filter::Scheduler', App.activateFilters)
    
    Event.$on('vue-tables.filter::DOB', App.activateFilters)
    
    Event.$on('vue-tables.sorted', App.activateFilters)
    
    Event.$on('vue-tables.limit', App.activateFilters)

    function unscheduledPatientsModalFilterHandler (data) {
        Event.$emit('modal-unscheduled-patients:hide')
        Event.$emit('modal-add-call:show')
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                Event.$emit('add-call-modals:set', data)
                resolve(data)
            }, 200)
        })
    }

    Event.$on('unscheduled-patients-modal:filter', unscheduledPatientsModalFilterHandler)

    function selectNurseUpdateHandler ({ callId, nurseId }) {
        const call = App.tableData.find(row => row.id == callId)
        const nurse = App.nurses.find(nurse => nurse.id == nurseId)
        if (call) {
            call.NurseId = nurseId
            call.Nurse = (nurse || {}).display_name
            console.log('calls:row-update', { callId, nurseId }, call.Nurse)
        }
    }

    Event.$on('select-nurse:update', selectNurseUpdateHandler)

    function selectTimesChangeHandler ({ callIDs, nextCall, callTimeStart, callTimeEnd }) {
        console.log('select-times-change-handler', ...arguments)
        if (callIDs && Array.isArray(callIDs)) {
            const id = callIDs[0]
            const $row = App.tableData.find(row => row.id == id)
            console.log(id, $row)
            if ($row && nextCall && callTimeStart && callTimeEnd) {
                return $row.updateMultiValues({ nextCall, callTimeStart, callTimeEnd }).then(() => {
                    callIDs.splice(0, 1)
                    return selectTimesChangeHandler({ callIDs, nextCall, callTimeStart, callTimeEnd })
                })
            }
            else {
                Event.$emit('modal-select-times:hide')
            }
        }
        return new Promise((resolve, reject) => resolve(null))
    }

    Event.$on('select-times-modal:change', selectTimesChangeHandler)

    Event.$on('calls:add', App.activateFilters)

    return {
        nextPageHandler,
        unscheduledPatientsModalFilterHandler,
        selectNurseUpdateHandler,
        selectTimesChangeHandler
    }
}