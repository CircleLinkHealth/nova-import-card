import DocumentMock from '../document.mock'
import sleep from '../../../sleep'

describe('DocumentMock', () => {

    

    it('should have appropriate functions', () => {
        const document = new DocumentMock()

        expect(typeof(document.addEventListener)).toEqual('function')
        expect(typeof(document.removeListener)).toEqual('function')
        expect(typeof(document.emit)).toEqual('function')
    })

    describe('.emit()', () => {
        let value = false
        it('should change value to true', () => {
            const document = new DocumentMock()

            document.addEventListener('value:toggle', () => {
                value = true
            })

            document.emit('value:toggle')

            expect(value).toBeTruthy()
        })
    })

    describe('.addEventListener()', () => {
        it('should create an array in events', () => {
            const document = new DocumentMock()
            document.addEventListener('custom:name', () => ({}))
            expect(Array.isArray(document.events['custom:name'])).toBeTruthy()
        })
        
        it('should add to array in events', () => {
            const document = new DocumentMock()
            document.addEventListener('custom:name', () => ({}))
            document.addEventListener('custom:name', () => ({}))
            expect(document.events['custom:name'].length).toEqual(2)
        })
    })

    describe('.removeListener()', () => {
        it('should have event count as zero', () => {
            const document = new DocumentMock()

            const listener = () => ({})
            document.addEventListener('custom:name', listener)
            
            expect(document.events['custom:name'].length).toEqual(1)

            document.removeListener('custom:name', listener)
            expect(document.events['custom:name'].length).toEqual(0)
        })
    })

    describe('DOMContentLoaded', () => {
        it('should raise event', async () => {
            const document = new DocumentMock()

            expect(() => {
                expect(document.readyState).toEqual('loaded')
            }).toThrow()

            await sleep(100)

            expect(document.readyState).toEqual('loaded')
        })
    })
})