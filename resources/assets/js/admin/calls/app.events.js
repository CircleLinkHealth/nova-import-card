export default (App, Event) => {
    const $table = App.$refs.tblCalls;

    Event.$on('vue-tables.pagination', (page) => {
        App.next();
    })

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

    Event.$on('unscheduled-patients-modal:filter', (value) => {
        App.$refs.tblCalls.setFilter({ Patient: value })
        App.activateFilters()
        Event.$emit('modal-unscheduled-patients:hide')
    })

    Event.$on('select-nurse:update', (data) => {
        const call = App.tableData.find(row => row.id == data.callId)
        if (call) {
            call.Nurse = data.nurseId
            console.log('calls:row-update', data)
        }
    })

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
    }
    Event.$on('select-times-modal:change', selectTimesChangeHandler)

    Event.$on('calls:add', (call) => {
        const tableCall = App.setupCall(call)
        App.tableData.unshift(tableCall)
    })
}