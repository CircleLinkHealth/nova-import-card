export default (App, Event) => {

    const getUnscheduledPatientsModalRef = () => {
        //get the component and we assume first child is the modal
        const compChildren = App.$refs.unscheduledPatientsModal.$children;
        return compChildren.find(x => x.$vnode.componentOptions.tag === "modal");
    };

    const isMainTableVisible = () => {
        // Scenario: Unscheduled Patients Modal is visible.
        //           We assume that the pagination event is triggered from the modal.
        //           So, there is nothing to do for the main calls table.
        const modal = getUnscheduledPatientsModalRef();
        if (modal) {
            return !modal.$data.visible;
        }
        return true;
    };

    const nextPageHandler = (page) => {
        if (!isMainTableVisible()) {
            return;
        }
        App.next();
    };

    Event.$on('vue-tables.pagination', nextPageHandler)

    Event.$on('vue-tables.filter::Type', App.activateFilters)

    Event.$on('vue-tables.filter::Care Coach', App.activateFilters)

    Event.$on('vue-tables.filter::Patient', App.activateFilters)

    Event.$on('vue-tables.filter::Patient ID', App.activateFilters)

    Event.$on('vue-tables.filter::Activity Day', App.activateFilters)

    Event.$on('vue-tables.filter::Last Call', App.activateFilters)

    Event.$on('vue-tables.filter::Practice', App.activateFilters)

    Event.$on('vue-tables.filter::State', App.activateFilters)

    Event.$on('vue-tables.filter::Billing Provider', App.activateFilters)

    Event.$on('vue-tables.filter::Scheduler', App.activateFilters)

    Event.$on('vue-tables.sorted', App.activateFilters)

    Event.$on('vue-tables.limit', App.activateFilters)

    function unscheduledPatientsModalFilterHandler(data) {
        Event.$emit('modal-unscheduled-patients:hide')
        Event.$emit('modal-add-action:show')
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                Event.$emit('add-action-modals:set', data)
                resolve(data)
            }, 200)
        })
    }

    Event.$on('unscheduled-patients-modal:filter', unscheduledPatientsModalFilterHandler)

    function selectNurseUpdateHandler({callId, nurseId}) {
        const call = App.tableData.find(row => row.id == callId)
        const nurse = App.nurses.find(nurse => nurse.id == nurseId)
        if (call) {
            call.NurseId = nurseId
            call.Nurse = (nurse || {}).display_name
            console.log('calls:row-update', {callId, nurseId}, call.Nurse)
        }
    }

    Event.$on('select-nurse:update', selectNurseUpdateHandler)

    function selectTimesChangeHandler({callIDs, nextCall, callTimeStart, callTimeEnd}) {
        for (let i = 0; i < callIDs.length; i++) {
            const row = App.tableData.find(row => row.id === callIDs[i]);
            if (!row) {
                continue;
            }
            if (nextCall) {
                row['Activity Day'] = nextCall;
            }

            if (callTimeStart) {
                row['Activity Start'] = callTimeStart;
            }

            if (callTimeEnd) {
                row['Activity End'] = callTimeEnd;
            }
        }
    }

    Event.$on('select-times-modal:change', selectTimesChangeHandler);

    Event.$on('actions:add', App.activateFilters)

    return {
        nextPageHandler,
        unscheduledPatientsModalFilterHandler,
        selectNurseUpdateHandler,
        selectTimesChangeHandler
    }
}
