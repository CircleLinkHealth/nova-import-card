import { distinct, includes, find, findIndex } from '../array.prototype'

describe('Array.prototype', () => {
    describe('distinct', () => {
        it('should return empty array', () => {
            expect(distinct.call([]).length).toEqual(0)
        })
        it('should return distinct items', () => {
            expect(distinct.call([1,1,2,2,3,3,3]).length).toEqual(3)
        })
        describe('complex', () => {
            it('should return distinct items', () => {
                const arr = [
                    { a: 1 }, { a: 1 }, { a: 2 }, { a: 2 }, { a: 3 }, { a: 3 }
                ]

                expect(distinct.call(arr, ((item) => item.a)).length).toEqual(3)
            })

            describe('string key prop', () => {
                it('should return distinct items', () => {
                    const arr = [
                        { a: 1 }, { a: 1 }, { a: 2 }, { a: 2 }, { a: 3 }, { a: 3 }
                    ]
    
                    expect(distinct.call(arr, 'a').length).toEqual(3)
                })
            })
        })
    })

    describe('includes', () => {
        it('should return false', () => {
            expect(includes.call([], 2)).toEqual(false)
        })
        it('should return true', () => {
            expect(includes.call([2], 2)).toEqual(true)
        })
    })

    describe('find', () => {
        it('should return null', () => {
            expect(find.call([], ((item) => item === 2))).toEqual(null)
        })
        it('should return 2', () => {
            expect(find.call([2], ((item) => item === 2))).toEqual(2)
        })
    })

    describe('findIndex', () => {
        it('should return -1', () => {
            expect(findIndex.call([], ((item) => item === 2))).toEqual(-1)
        })
        it('should return 0', () => {
            expect(findIndex.call([2], ((item) => item === 2))).toEqual(0)
        })
    })
})