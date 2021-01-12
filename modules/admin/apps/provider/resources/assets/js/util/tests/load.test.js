import load from '../load'
import DocumentMock from './mocks/document.mock'
import { shallow } from 'vue-test-utils';

describe('Load', () => {
    describe('document.readyState == loading', () => {
        it('should trigger DOMContentLoaded', () => {
            const document = new DocumentMock({
                readyState: 'loading'
            })
    
            let indicator = false
            load(() => {
                indicator = true
            }, document)
    
            setTimeout(() => {
                expect(indicator).toBeTruthy()
            }, 101)
        })
    })

    describe('document.readyState !== loading', () => {
        it('should trigger $nextTick', () => {
            const Component = {
                data () {
                    return {}
                }
            }

            const document = new DocumentMock({
                readyState: 'loaded'
            })

            const wrapper = shallow(Component)
    
            let indicator = false
            load.apply(wrapper.vm, () => {
                indicator = true
            }, document)
    
            setTimeout(() => {
                expect(indicator).toBeTruthy()
            }, 101)
        })
    })
})