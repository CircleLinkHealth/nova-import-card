import {rootUrl} from '../../../app.config'
import {Event} from 'vue-tables-2'

export const onNextCallUpdate = function (call, date, familyOverride, oldValue, revertCallback) {
    /** update the next call column */
    call.loaders.nextCall = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'scheduled_date',
        value: date,
        familyOverride
    }).then(response => {
        console.log('calls:row:update', response.data)
        call['Next Call'] = date
        call.loaders.nextCall = false
        return response.data
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        call['Next Call'] = oldValue;
        call.loaders.nextCall = false;
        throw err;
    });
};

export const onNurseUpdate = function (call, nurseId, familyOverride, oldValue, revertCallback) {
    /** update the next call column */
    call.loaders.nurse = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'outbound_cpm_id',
        value: nurseId,
        familyOverride
    }).then(response => {
        const nurse = (call.nurses().find(nurse => nurse.value == nurseId) || {})
        call.NurseId = nurse.value
        call['Care Coach'] = (nurse.text || 'unassigned')
        call.loaders.nurse = false
        if (response) console.log('calls:row:update', nurse)
        if (nurseId) Event.$emit('select-nurse:update', {nurseId: call.NurseId, callId: call.id})
        return response.data
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        const nurse = (call.nurses().find(nurse => nurse.value == oldValue) || {})
        call.NurseId = nurse.value
        call['Care Coach']= (nurse.text || 'unassigned')
        call.loaders.nurse = false;
        throw err;
    });
};

export const onCallTimeStartUpdate = function (call, time, familyOverride, oldValue, revertCallback) {
    /** update the call_time_start column */
    call.loaders.callTimeStart = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'window_start',
        value: time,
        familyOverride
    }).then(response => {
        call['Call Time Start'] = time;
        call.loaders.callTimeStart = false;
        if (response) console.log('calls:row:update', call);
        return response.data;
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        call['Call Time Start'] = oldValue;
        call.loaders.callTimeStart = false;
        throw err;
    });
};

export const onCallTimeEndUpdate = function (call, time, familyOverride, oldValue, revertCallback) {
    /** update the call_time_end column */
    call.loaders.callEndStart = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'window_end',
        value: time,
        familyOverride
    }).then(response => {
        call['Call Time End'] = time;
        call.loaders.callEndStart = false;
        if (response) console.log('calls:row:update', call);
        return response.data
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        call['Call Time End'] = oldValue;
        call.loaders.callEndStart = false;
        throw err;
    });
};

export const onGeneralCommentUpdate = function (call, comment, familyOverride, oldValue, revertCallback) {
    /** update the call_time_end column */
    call.loaders.generalComment = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'general_comment',
        value: comment,
        familyOverride
    }).then(response => {
        call.Comment = comment;
        call.loaders.generalComment = false;
        if (response) console.log('calls:row:update', call);
        return response.data
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        call.Comment = oldValue;
        call.loaders.generalComment = false;
        throw err;
    });
};

export const onAttemptNoteUpdate = function (call, note, familyOverride, oldValue, revertCallback) {
    /** update the call_time_end column */
    call.loaders.attemptNote = true
    return axios.post(rootUrl('callupdate'), {
        callId: call.id,
        columnName: 'attempt_note',
        value: note,
        familyOverride
    }).then(response => {
        call.AttemptNote = note;
        call.loaders.attemptNote = false;
        if (response) console.log('calls:row:update', call);
        return response.data;
    }).catch(err => {
        console.error('calls:row:update', err);
        revertCallback();
        call.AttemptNote = oldValue;
        call.loaders.attemptNote = false;
        throw err;
    });
};

export const updateMultiValues = function (call, {nextCall, callTimeStart, callTimeEnd}, familyOverride, oldValue, revertCallback) {
    if (nextCall, callTimeStart, callTimeEnd) {
        return Promise.all([
            onNextCallUpdate(call, nextCall, familyOverride, oldValue, revertCallback)
            // onCallTimeStartUpdate.call(this, callTimeStart),
            // onCallTimeEndUpdate.call(this, callTimeEnd)
        ]);
    }
    else Promise.resolve({});
};