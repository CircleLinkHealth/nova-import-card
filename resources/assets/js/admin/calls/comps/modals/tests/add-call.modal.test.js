import AddCallModal from '../add-call.modal'
import { mount } from 'vue-test-utils'
import Vue from 'vue'
import axios from 'axios'
import mock from '../../../tests/http/calls.http'
import '../../../../../prototypes/array.prototype'
import sleep from '../../../../../util/sleep'

const createAddCallModal = (props = {}) => {
    const comp = mount(AddCallModal, {
        propsData: props
    })
    return comp.vm
}

describe('AddCallModal', () => {
    it('should mount', () => {
        const vm = createAddCallModal()
    })
})