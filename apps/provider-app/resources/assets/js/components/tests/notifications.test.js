import Notifications from '../notifications'
import { mount, shallow } from 'vue-test-utils'
import { wrap } from 'module';
import sleep from '../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/util/sleep'

describe('Notifications', () => {
    const wrapper = shallow(Notifications, {
        propsData: {
            
        }
    })

    const $vm = wrapper.vm

    it('should mount', () => {
        
    })

    it('should have a create method', () => {
        expect(typeof($vm.create)).toEqual('function')
    })

    it('should have element with class .notifications', () => {
        expect($vm.$el.classList[1]).toEqual('notifications')
    })

    describe('Props', () => {
        it('should have default props', () => {
            expect($vm.name).toEqual(undefined)
            expect($vm.reverse).toBeFalsy()
        })

        describe('name', () => {
            const wrapper = shallow(Notifications, {
                propsData: {
                    name: 'test'
                }
            })
            const $vm = wrapper.vm

            it('should have element with class .notifications-test', () => {
                expect($vm.$el.classList[1]).toEqual('notifications-test')
            })

            it('should have componentName equal to notifications-test', () => {
                expect($vm.componentName).toEqual('notifications-test')
            })
        })
    })

    describe('Methods', () => {
        it('should have default methods', () => {
            expect(typeof($vm.create)).toEqual('function')
            expect(typeof($vm.remove)).toEqual('function')
        })
    })

    describe('Create', () => {
        const wrapper = shallow(Notifications)
        const $vm = wrapper.vm

        describe('Timeout', () => {
            it('should be defined', () => {
                const wrapper = shallow(Notifications)
                const $vm = wrapper.vm

                $vm.create('Hello World')
        
                expect($vm.notes[0].timeout).toBeDefined()
            })

            it('should remove note at appropriate time', async () => {
                const wrapper = shallow(Notifications)
                const $vm = wrapper.vm

                $vm.create({
                    text: 'Hello World',
                    interval: 500
                })
        
                expect($vm.notes.length).toEqual(1)

                
                await sleep(200)

                expect(() => {
                    expect($vm.notes.length).toEqual(0)
                }).toThrow()

                await sleep(301)

                expect($vm.notes.length).toEqual(0)
            })

            describe('noTimeout', () => {
                it('should NOT be defined', () => {
                    const wrapper = shallow(Notifications)
                    const $vm = wrapper.vm
    
                    $vm.create({
                        text: 'Hello World',
                        noTimeout: true
                    })
            
                    expect($vm.notes[0].timeout).toBeUndefined()
                })
            })
        })

        describe('Reverse', () => {
            it('should be in reverse', () => {
                const wrapper = shallow(Notifications, {
                    propsData: {
                        reverse: true
                    }
                })
                const $vm = wrapper.vm

                $vm.create({
                    text: 'Hello World 1',
                    noTimeout: true
                })

                $vm.create({
                    text: 'Hello World 2',
                    noTimeout: true
                })
        
                expect($vm.notes[0].text).toEqual('Hello World 2')
                expect($vm.notes[1].text).toEqual('Hello World 1')
            })
        })

        describe('Argument Type', () => {
            describe('Null', () => {
                const wrapper = shallow(Notifications)
                const $vm = wrapper.vm

                it('throws', () => {
                    expect(() => {
                        $vm.create()
                    }).toThrow()
                })
            })

            describe('String', () => {
                const wrapper = shallow(Notifications)
                const $vm = wrapper.vm

                it('should add note', () => {
                    $vm.create('Hello World')
        
                    expect($vm.notes.length).toEqual(1)
                    expect($vm.notes[0].text).toEqual('Hello World')
                    expect($vm.notes[0].type).toEqual('success')
                })
            })

            describe('Object', () => {
                it('should add note', () => {
                    const wrapper = shallow(Notifications)
                    const $vm = wrapper.vm

                    $vm.create({
                        text: 'Hello World'
                    })
        
                    expect($vm.notes.length).toEqual(1)
                    expect($vm.notes[0].text).toEqual('Hello World')
                    expect($vm.notes[0].type).toEqual('success')
                })

                it('should add note with type', () => {
                    const wrapper = shallow(Notifications)
                    const $vm = wrapper.vm

                    $vm.create({
                        text: 'Hello World',
                        type: 'warning'
                    })
        
                    expect($vm.notes.length).toEqual(1)
                    expect($vm.notes[0].text).toEqual('Hello World')
                    expect($vm.notes[0].type).toEqual('warning')
                })
            })
        })
    })

    describe('Remove', () => {
        it('should remove a note', () => {
            const wrapper = shallow(Notifications, {
                propsData: {
                    reverse: true
                }
            })
            const $vm = wrapper.vm

            const note1 = $vm.create({
                text: 'Hello World 1',
                noTimeout: true
            })
    
            expect($vm.notes.length).toEqual(1)

            const note2 = $vm.create({
                text: 'Hello World 2',
                noTimeout: true
            })
    
            expect($vm.notes.length).toEqual(2)

            $vm.remove(note1.id)
    
            expect($vm.notes.length).toEqual(1)

            $vm.remove(note2.id)
    
            expect($vm.notes.length).toEqual(0)
        })
    })

    describe('note.close()', () => {
        it('should remove note', () => {
            const wrapper = shallow(Notifications, {
                propsData: {
                    reverse: true
                }
            })
            const $vm = wrapper.vm

            const note1 = $vm.create({
                text: 'Hello World 1',
                noTimeout: true
            })
    
            expect($vm.notes.length).toEqual(1)

            const note2 = $vm.create({
                text: 'Hello World 2',
                noTimeout: true
            })
    
            expect($vm.notes.length).toEqual(2)

            note1.close()
    
            expect($vm.notes.length).toEqual(1)

            note2.close()
    
            expect($vm.notes.length).toEqual(0)
        })
    })

    describe('Mounts', () => {
        it('should mount', () => {
            const wrapper = mount(Notifications, {
                attachToDocument: true
            })
            const $vm = wrapper.vm
        })
    })
})