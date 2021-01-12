
import axios from 'axios'
import mock from '../../tests/http/calls.http'
import NURSES from '../../tests/mocks/nurses.mock'
import { 
    onNextCallUpdate,
    onNurseUpdate,
    onCallTimeStartUpdate,
    onCallTimeEndUpdate,
    onGeneralCommentUpdate,
    onAttemptNoteUpdate,
    updateMultiValues
} from '../call-update.fn'


const call = {
    id: 1,
    loaders: {
        nextCall: false
    },
    NurseId: null,
    Nurse: null,
    'Next Call': '2018-05-11',
    'Call Time Start': null,
    nurses: () => NURSES.map(nurse => ({ text: nurse.user.display_name, value: nurse.user_id })),
    onNextCallUpdate,
    onNurseUpdate,
    onCallTimeStartUpdate,
    onCallTimeEndUpdate,
    onGeneralCommentUpdate,
    onAttemptNoteUpdate,
    updateMultiValues
}

describe('Call Update Fns', () => {
    global.axios = axios

    it('on Next Call Update', async () => {
        const NEXT_CALL_DATE = '2018-05-13'

        const response = await call.onNextCallUpdate(NEXT_CALL_DATE)
        
        expect(response.date).toEqual(NEXT_CALL_DATE)

        expect(call['Next Call']).toEqual(NEXT_CALL_DATE)
    })

    it('on Nurse Update', async () => {
        const NURSE_ID = 1920

        const response = await call.onNurseUpdate(NURSE_ID)
        
        expect(response.outbound_cpm_id).toEqual(NURSE_ID)

        expect(call.NurseId).toEqual(NURSE_ID)

        expect(call.Nurse).toEqual('Patricia Koeppel')
    })

    it('on Call Time Start Update', async () => {
        const WINDOW_START = '12:00'

        const response = await call.onCallTimeStartUpdate(WINDOW_START)
        
        expect(response.window_start).toEqual(WINDOW_START)

        expect(call['Call Time Start']).toEqual(WINDOW_START)
    })

    it('on Call Time End Update', async () => {
        const WINDOW_END = '13:00'

        const response = await call.onCallTimeEndUpdate(WINDOW_END)
        
        expect(response.window_end).toEqual(WINDOW_END)

        expect(call['Call Time End']).toEqual(WINDOW_END)
    })

    it('on General Comment Update', async () => {
        const GENERAL_COMMENT = '...'

        const response = await call.onGeneralCommentUpdate(GENERAL_COMMENT)
        
        expect(response.general_comment).toEqual(GENERAL_COMMENT)

        expect(call.Comment).toEqual(GENERAL_COMMENT)
    })

    it('on Attempt Note Update', async () => {
        const ATTEMPT_NOTE = '...'

        const response = await call.onAttemptNoteUpdate(ATTEMPT_NOTE)
        
        expect(response.attempt_note).toEqual(ATTEMPT_NOTE)

        expect(call.AttemptNote).toEqual(ATTEMPT_NOTE)
    })

    it('on Update Multi Values', async () => {
        const NEXT_CALL_DATE = '2018-05-13'
        const WINDOW_START = '12:00'
        const WINDOW_END = '13:00'

        const response = await call.updateMultiValues({
            nextCall: NEXT_CALL_DATE,
            callTimeStart: WINDOW_START,
            callTimeEnd: WINDOW_END
        })
        
        expect(Array.isArray(response)).toBeTruthy()
        
        expect(response[0].date).toEqual(NEXT_CALL_DATE)

        expect(call['Next Call']).toEqual(NEXT_CALL_DATE)
    })
})

