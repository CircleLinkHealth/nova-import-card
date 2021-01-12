import Vue from 'vue'
import { mount, shallow } from 'vue-test-utils'
import { Event } from 'vue-tables-2'
import Modal from '../modal'

describe('Modal', () => {
    const wrapper = shallow(Modal)

    it('renders', () => {
        const $vm = wrapper.vm
    })

    describe('Props', () => {
        it('should have props as defaults', () => {
            const $vm = wrapper.vm

            expect($vm.visible).toBeFalsy()
            expect($vm.title).toEqual('')
            expect($vm.body).toEqual('')
            expect($vm.footer).toEqual('')
        })

        it('should have methods as defaults', () => {
            const $vm = wrapper.vm

            expect(typeof($vm.show)).toEqual('function')
            expect(typeof($vm.close)).toEqual('function')
        })

        describe('Elements', () => {
            const wrapper = shallow(Modal, {
                propsData: {
                    isVisible: true
                }
            })
            const $vm = wrapper.vm
            it('.modal-header', () => {
                expect(wrapper.contains('.modal-header')).toBeTruthy()
            })
            it('.modal-body', () => {
                expect(wrapper.contains('.modal-body')).toBeTruthy()
            })
            it('.modal-footer', () => {
                expect(wrapper.contains('.modal-footer')).toBeTruthy()
            })

            describe('no-footer', () => {
                const wrapper = shallow(Modal, {
                    propsData: {
                        noFooter: true,
                        isVisible: true
                    }
                })
                it('.modal-header', () => {
                    expect(wrapper.contains('.modal-header')).toBeTruthy()
                })
                it('.modal-body', () => {
                    expect(wrapper.contains('.modal-body')).toBeTruthy()
                })
                it('.modal-footer', () => {
                    expect(wrapper.contains('.modal-footer.only')).toBeFalsy()
                })
            })

            describe('no-title', () => {
                const wrapper = shallow(Modal, {
                    propsData: {
                        noTitle: true,
                        isVisible: true
                    }
                })
                it('.modal-header', () => {
                    expect(wrapper.contains('.modal-header')).toBeFalsy()
                })
                it('.modal-body', () => {
                    expect(wrapper.contains('.modal-body')).toBeTruthy()
                })
                it('.modal-footer', () => {
                    expect(wrapper.contains('.modal-footer.only')).toBeTruthy()
                })
            })

            describe('no-buttons', () => {
                const wrapper = shallow(Modal, {
                    propsData: {
                        noButtons: true,
                        isVisible: true
                    }
                })
                it('.modal-header', () => {
                    expect(wrapper.contains('.modal-header')).toBeTruthy()
                })
                it('.modal-body', () => {
                    expect(wrapper.contains('.modal-body')).toBeTruthy()
                })
                it('.modal-footer', () => {
                    expect(wrapper.contains('.modal-footer.close-footer')).toBeFalsy()
                })
            })

            describe('no-cancel', () => {
                const wrapper = shallow(Modal, {
                    propsData: {
                        noCancel: true,
                        isVisible: true
                    }
                })
                it('.close-footer is visible', () => {
                    expect(wrapper.contains('.modal-footer.close-footer')).toBeTruthy()
                })
                it('.modal-button.modal-cancel-button is NOT visible', () => {
                    expect(wrapper.contains('.modal-button.modal-cancel-button')).toBeFalsy()
                })
            })
        })

        describe('Methods', () => {
            const wrapper = shallow(Modal)
            const $vm = wrapper.vm

            describe('show()', () => {
                it('should make the modal visible', () => {
                    $vm.show()
                    expect($vm.visible).toBeTruthy()
                })
            })

            describe('close()', () => {
                it('should make the modal NOT visible', () => {
                    $vm.close()
                    expect($vm.visible).toBeFalsy()
                })
            })

            describe('ok()', () => {
                it('should close the modal', () => {
                    $vm.ok()
                    expect($vm.visible).toBeFalsy()
                })

                it('should execute the info.okHandler prop callback', () => {
                    let indicator = false

                    const wrapper = shallow(Modal, {
                        propsData: {
                            info: {
                                okHandler: () => {
                                    indicator = true
                                }
                            },
                            isVisible: true
                        }
                    })

                    const $vm = wrapper.vm

                    $vm.ok()

                    expect(indicator).toBeTruthy()

                    expect($vm.visible).toBeTruthy()
                })
            })

            describe('cancel()', () => {
                it('should close the modal', () => {
                    $vm.cancel()
                    expect($vm.visible).toBeFalsy()
                })

                it('should execute the info.cancelHandler prop callback', () => {
                    let indicator = false

                    const wrapper = shallow(Modal, {
                        propsData: {
                            info: {
                                cancelHandler: () => {
                                    indicator = true
                                }
                            },
                            isVisible: true
                        }
                    })

                    const $vm = wrapper.vm

                    $vm.cancel()

                    expect(indicator).toBeTruthy()

                    expect($vm.visible).toBeTruthy()
                })
            })
        })

        describe('Slots', () => {
            const Component = {
                template: `<div class="hello-world"></div>`,
                data () {
                    return {}
                }
            }

            const wrapper = shallow(Modal, {
                propsData: {
                    noCancel: true,
                    isVisible: true
                },
                slots: {
                    default: [ Component ]
                }
            })

            it('should contain Component', () => {
                expect(wrapper.contains('div.hello-world')).toBeTruthy()
            })
        })
    })
})