import { pad } from '../pad'

describe('pad()', () => {
    it('should return exact input', () => {
        expect(pad(0)).toEqual('0')
    })
    it('should return string', () => {
        expect(typeof(pad(0))).toEqual('string')
    })
    it('should return 01', () => {
        expect(pad(1, 2)).toEqual('01')
    })
    it('should return 001', () => {
        expect(pad(1, 3)).toEqual('001')
    })
})