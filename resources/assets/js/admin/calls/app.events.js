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

    Event.$on('select-nurse:update', (data) => {
        const call = App.tableData.find(row => row.id == data.callId)
        if (call) {
            call.Nurse = data.nurseId
            console.log('calls:row-update', data)
        }
    })
}