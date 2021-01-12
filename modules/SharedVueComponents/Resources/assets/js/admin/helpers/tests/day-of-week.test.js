import { DayOfWeek, ShortDayOfWeek } from '../day-of-week'

describe('DayOfWeek', () => {
    it('should have 1 equal monday', () => {
        expect(DayOfWeek[1]).toEqual('Monday')
    })

    it('should have 7 equal sunday', () => {
        expect(DayOfWeek[7]).toEqual('Sunday')
    })
})

describe('ShortDayOfWeek', () => {
    it('should have 1 equal M', () => {
        expect(ShortDayOfWeek(1)).toEqual('M')
    })
    
    it('should have 2 equal Tu', () => {
        expect(ShortDayOfWeek(2)).toEqual('Tu')
    })

    it('should have 7 equal Su', () => {
        expect(ShortDayOfWeek(7)).toEqual('Su')
    })
    
    it('should have 6 equal Sa', () => {
        expect(ShortDayOfWeek(6)).toEqual('Sa')
    })
})