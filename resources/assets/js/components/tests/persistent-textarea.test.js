import PersistentTextArea from '../persistent-textarea'
import { mount } from 'vue-test-utils'
import { sstor } from '../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/stor'

const STORAGE_KEY = 'my-textarea'

describe('Persistent-TextArea', () => {
    const comp = mount(PersistentTextArea, {
        propsData: {
            storageKey: STORAGE_KEY
        }
    })

    it('should mount', () => {
        
    })
    
    it('should have a storage-key prop', () => {
        expect(comp.hasProp('storageKey', STORAGE_KEY)).toBe(true)
    })

    it('should have a textarea', () => {
        const textarea = comp.vm.$el
        expect(textarea.constructor.name).toBe('HTMLTextAreaElement')
    })
    
    it('should be persistent', () => {
        const textarea = comp.vm.$el
        textarea.value = 'Hello World'
        comp.vm.$on('input', (e) => {
            expect(sstor.get(STORAGE_KEY)).toEqual(textarea.value)
        })
        textarea.dispatchEvent(new Event('input')) 
        comp.vm.changeTextArea()
    })
})