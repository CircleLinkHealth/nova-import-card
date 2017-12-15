import PersistentTextArea from '../persistent-textarea'
import { mount } from 'vue-test-utils'
import stor from '../../stor'
import Vue from 'vue'
global.window = {}
import localStorage from 'mock-local-storage'
window.localStorage = global.localStorage

describe('Persistent-TextArea', () => {
    const comp = mount(PersistentTextArea, {
        props: {
            storageKey: 'my-textarea'
        }
    })

    it('should mount', () => {
        console.log(stor.storage())
    })
    
    it('should have a storage-key prop', () => {
        expect(comp.hasProp('storageKey')).toBe(true)
    })

    it('should have a textarea', () => {
        const textarea = comp.vm.$el
        expect(textarea.constructor.name).toBe('HTMLTextAreaElement')
    })
    
    it('should be persistent', () => {
        const textarea = comp.vm.$el
        textarea.onchange = (e) => {
            console.log(localStorage.getItem('my-textarea'))
        }
        textarea.value = 'Hello World'
        textarea.dispatchEvent(new Event('input'))

        Vue.nextTick(() => {
            console.log(stor.get('my-textarea'))
        }) 

        // setImmediate(() => {
        //     expect(stor.get('my-textarea')).toBe('Hello World')
        // })
    })
})