import { rootUrl } from '../../../app.config'
import { Event } from 'vue-tables-2'

export const onNextCallUpdate = function (date) {
    /** update the next call column */
    const call = this
    this.loaders.nextCall = true
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'scheduled_date',
      value: date
    }).then(response => {
      console.log('calls:row:update', response.data)
      call['Next Call'] = date
      this.loaders.nextCall = false
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.nextCall = false
    })
}

export const onNurseUpdate = function (nurseId) {
    /** update the next call column */
    const call = this
    this.loaders.nurse = true
    console.log('on-nurse-update', nurseId)
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'outbound_cpm_id',
      value: nurseId
    }).then(response => {
      const nurse = (this.nurses().find(nurse => nurse.value == nurseId) || {})
      call.NurseId = nurse.value
      call.Nurse = (nurse.text || 'unassigned')
      this.loaders.nurse = false
      if (response) console.log('calls:row:update', nurse)
      if (nurseId) Event.$emit('select-nurse:update', { nurseId: call.NurseId, callId: call.id })
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.nurse = false
    })
}

export const onCallTimeStartUpdate = function  (time) {
    /** update the call_time_start column */
    const call = this
    this.loaders.callTimeStart = true
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'window_start',
      value: time
    }).then(response => {
      call['Call Time Start'] = time
      this.loaders.callTimeStart = false
      if (response) console.log('calls:row:update', call)
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.callTimeStart = false
    })
}

export const onCallTimeEndUpdate = function (time) {
    /** update the call_time_end column */
    const call = this
    this.loaders.callEndStart = true
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'window_end',
      value: time
    }).then(response => {
      call['Call Time End'] = time
      this.loaders.callEndStart = false
      if (response) console.log('calls:row:update', call)
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.callEndStart = false
    })
}

export const onGeneralCommentUpdate = function (comment) {
    /** update the call_time_end column */
    const call = this
    this.loaders.generalComment = true
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'general_comment',
      value: comment
    }).then(response => {
      call.Comment = comment
      this.loaders.generalComment = false
      if (response) console.log('calls:row:update', call)
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.generalComment = false
    })
}

export const onAttemptNoteUpdate = function (note) {
    /** update the call_time_end column */
    const call = this
    this.loaders.attemptNote = true
    return axios.post(rootUrl('callupdate'), {
      callId: this.id,
      columnName: 'attempt_note',
      value: note
    }).then(response => {
      call.AttemptNote = note
      this.loaders.attemptNote = false
      if (response) console.log('calls:row:update', call)
    }).catch(err => {
      console.error('calls:row:update', err)
      this.loaders.attemptNote = false
    })
}

export const updateMultiValues = function  ({ nextCall, callTimeStart, callTimeEnd }) {
    if (nextCall, callTimeStart, callTimeEnd) {
      return Promise.all([
        onNextCallUpdate.call(this, nextCall)
        // onCallTimeStartUpdate.call(this, callTimeStart),
        // onCallTimeEndUpdate.call(this, callTimeEnd)
      ])
    }
    else Promise.resolve({})
}