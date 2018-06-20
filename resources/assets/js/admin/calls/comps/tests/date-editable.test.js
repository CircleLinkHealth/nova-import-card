import DateEditable from '../date-editable'
import { shallow } from 'vue-test-utils'
import Vue from 'vue'

const CreateDateEditable = (props) => {
    const comp = shallow(DateEditable, {
        propsData: props
    })
    return comp.vm
}

describe('DateEditable', () => {
    const format = 'YYYY-MM-DD'

    it ('isEditMode is true', () => {
        const vm = CreateDateEditable({
            isEdit: true,
            value: '',
            format
        })
        expect(vm.isEditMode).toBe(true)
    })

    it ('isEditMode is false', () => {
        const vm = CreateDateEditable({
            isEdit: false,
            value: '',
            format
        })

        expect(vm.isEditMode).toBe(false)
    })
    
    it ('element class-name is set', () => {
        const vm = CreateDateEditable({
            className: 'my-class-name',
            value: '',
            format
        })

        expect(vm.$el.className).toEqual('my-class-name')
    })
    
    it ('has a form in edit-mode', () => {
        const vm = CreateDateEditable({
            isEdit: true,
            value: '',
            format
        })

        expect(vm.$el.querySelectorAll('form').length).toEqual(1)
    })
    
    it ('onChange is triggered', () => {
        /**
         * will change the date in the input to "2017-01-02"
         * if onChange is triggered, the value of [onChangeHasTriggered] should change to true
         */
        let onChangeHasTriggered = false

        const vm = CreateDateEditable({
            value: '2017-01-01',
            format,
            isEdit: true,
            onChange: (text) => {
                onChangeHasTriggered = true
                expect(text).toEqual('2017-01-02')
            }
        })

        const $input = vm.$el.querySelector('input')
        $input.value = '2017-01-02'
        $input.dispatchEvent(new Event('input'))

        const $form = vm.$el.querySelector('form')
        $form.dispatchEvent(new Event('submit'))
        
        Vue.nextTick(() => {
            expect(onChangeHasTriggered).toBe(true)
            done()
        })
    })
})