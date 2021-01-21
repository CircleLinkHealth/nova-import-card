function TimeTrackerInfo(options = {}) {

    this.patientId = options.patientId || '344'

    this.providerId = options.providerId || '3864'

    this.chargeableServices = options.chargeableServices || [
        {total_time: 300, chargeable_service: {id: 1, code: 'CPT 99490', display_name: 'CCM'}},
        {total_time: 39, chargeable_service: {id: 2, code: 'CPT 99484', display_name: 'BHI'}},
    ];

    this.chargeableServiceId = options.chargeableServiceId || 1;

    this.wsUrl = options.wsUrl || 'ws://localhost:3000/time'

    this.programId = options.programId || '8'

    this.urlFull = options.urlFull || 'https://cpm-web.dev/manage-patients/344/notes'

    this.urlShort = options.urlShort || '/manage-patients/344/notes'

    this.ipAddr = options.ipAddr || '127.0.0.1'

    this.activity = options.activity || 'Notes/Offline Activities Review'

    this.title = options.title || 'patient.note.index'

    this.submitUrl = options.submitUrl || 'https://cpm-web.dev/api/v2.1/pagetimer'

    this.startTime = options.startTime || '2017-11-21 04:01:10'

    this.disabled = options.disabled || false

    this.patientFamilyId = options.patientFamilyId || 101

    this.initSeconds = options.initSeconds || 0

    this.createKey = function () {
        return `${this.patientId}-${this.providerId}`
    }

}


module.exports = TimeTrackerInfo
