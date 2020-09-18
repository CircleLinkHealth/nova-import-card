import DocumentMock from '../util/tests/mocks/document.mock'
import ElementMock from '../util/tests/mocks/element.mock'
import {
    rootUrl,
    baseValue,
    csrfToken,
    testRootUrl
} from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'

const base = new ElementMock({
    tagName: 'base',
    attributes: {
        name: 'root',
        href: '/prefix/'
    } 
})
global.document = window.document = new DocumentMock({
    querySelectorMap: {

    },
    elements: [
        base
    ]
})

describe('App.Config', () => {
    describe('rootUrl', () => {
        it('should prefix /', () => {
            expect(rootUrl('hello')).toEqual('/hello')
        })
        it('should prefix /custom/', () => {
            expect(rootUrl('hello', 'custom')).toEqual('/custom/hello')
        })
        it('should prefix /', () => {
            expect(rootUrl('hello', 'does-not-exist')).toEqual('/hello')
        })
    })
    describe('baseValue', () => {
        it('should return custom', () => {
            expect(baseValue('custom')).toEqual('custom')
        })
        it('should return empty string', () => {
            expect(baseValue('does-not-exist')).toEqual('')
        })
    })
    describe('csrfToken', () => {
        it('should return SAMPLE-CSRF-TOKEN', () => {
            expect(csrfToken()).toEqual('SAMPLE-CSRF-TOKEN')
        })
    })
})