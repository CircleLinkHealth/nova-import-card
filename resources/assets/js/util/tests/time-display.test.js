import timeDisplay from '../time-display'
import { hours, minutes, seconds } from '../time-display'

describe('Time Display', () => {
    describe('hours()', () => {
        it('should return 0', () => {
            expect(hours(0)).toEqual('0')
        })
        it('<3600 should return 0', () => {
            expect(hours(3000)).toEqual('0')
        })
        it('should return 1', () => {
            expect(hours(3600)).toEqual('1')
        })
        it('should return 2', () => {
            expect(hours(7200)).toEqual('2')
        })
    })

    describe('minutes()', () => {
        it('should return 00', () => {
            expect(minutes(0)).toEqual('00')
        })
        it('<60 should return 00', () => {
            expect(minutes(50)).toEqual('00')
        })
        it('should return 01', () => {
            expect(minutes(60)).toEqual('01')
        })
        it('should return 02', () => {
            expect(minutes(120)).toEqual('02')
        })
        it('3600 should return 00', () => {
            expect(minutes(3600)).toEqual('00')
        })
    })

    describe('seconds()', () => {
        it('should return 00', () => {
            expect(seconds(0)).toEqual('00')
        })
        it('50 should return 50', () => {
            expect(seconds(50)).toEqual('50')
        })
        it('>60 should return remainder', () => {
            expect(seconds(61)).toEqual('01')
        })
    })

    it('should return 00:00:00', () => {
        expect(timeDisplay(0)).toEqual('0:00:00')
    })

    it('should return 00:00:10', () => {
        expect(timeDisplay(10)).toEqual('0:00:10')
    })

    it('should return 00:01:00', () => {
        expect(timeDisplay(60)).toEqual('0:01:00')
    })

    it('should return 01:00:00', () => {
        expect(timeDisplay(3600)).toEqual('1:00:00')
    })
})
