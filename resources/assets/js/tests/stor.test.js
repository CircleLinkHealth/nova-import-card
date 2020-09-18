import stor, { sstor } from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/stor'

describe('Stor', () => {
    describe('stor.get()', () => {
        it('should be undefined', () => {
            expect(stor.get('stor:name')).toBeUndefined()
        })
    })

    describe('stor.add()', () => {
        beforeEach(() => {
            stor.add('stor:name', 'mykeels')
        })

        describe('stor.contains("stor:name")', () => {
            it('should be true', () => {
                expect(stor.contains('stor:name')).toBe(true)
            })
        })

        describe('stor.get("stor:name")', () => {
            it('should be "mykeels"', () => {
                expect(stor.get('stor:name')).toBe('mykeels')
            })
        })
    })
    
    describe('stor.add([])', () => {
        beforeEach(() => {
            stor.add('stor:alphabet', ['a', 'b', 'c'])
        })

        describe('stor.get("stor:alphabet")', () => {
            it('should be Array', () => {
                const value = stor.get('stor:alphabet')
                expect(value).toBeTruthy()
                expect(value.constructor.name).toBe('Array')
            })
        })
    })

    describe('stor.remove("stor:name")', () => {
        it('should remove the item', () => {
          stor.remove('stor:name')
          expect(stor.get('stor:name')).toBeUndefined()
        })
    })
})

describe('Session Stor', () => {
    describe('sstor.add()', () => {
        beforeEach(() => {
            sstor.add('stor:name', 'mykeels')
        })
        
        describe('sstor.contains("stor:name")', () => {
            it('should be true', () => {
                expect(sstor.contains('stor:name')).toBe(true)
            })
        })

        describe('sstor.get("stor:name")', () => {
            it('should be "mykeels"', () => {
                expect(sstor.get('stor:name')).toBe('mykeels')
            })
        })
    })
    
    describe('sstor.add([])', () => {
        beforeEach(() => {
            sstor.add('stor:alphabet', ['a', 'b', 'c'])
        })

        describe('sstor.get("stor:alphabet")', () => {
            it('should be Array', () => {
                const value = sstor.get('stor:alphabet')
                expect(value).toBeTruthy()
                expect(value.constructor.name).toBe('Array')
            })
        })
    })
    
    describe('sstor.remove("stor:name")', () => {
        it('should remove the item', () => {
            sstor.remove('stor:name')
            expect(sstor.get('stor:name')).toBeUndefined()
        })
    })
})