import Vue from 'vue'
import VueAxios from 'vue-axios'
import axios from 'axios'
import VueCache from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/util/vue-cache'
import MockAdapter from 'axios-mock-adapter'
import { shallow, mount } from 'vue-test-utils';

const mock = new MockAdapter(axios)

mock.onGet('/api').reply(200, {
    message: 'hello world'
})

Vue.use(VueAxios, axios)

describe('VueCache', () => {
    const comp = mount(VueCache)

    const $vm = comp.vm

    it('has cacheProvider', () => {
        expect($vm.cacheProvider).toEqual({})
    })

    it('has cache() function', () => {
        expect(typeof($vm.cache)).toEqual('function')
    })

    describe('Cache', () => {
        it('should have get() method', () => {
            const $cache = $vm.cache()

            expect(typeof($cache.get)).toEqual('function')
        })
    })

    describe('Request', () => {
        it('should return "hello world"', async () => {
            const response = await $vm.cache().get('/api')

            expect(response.message).toEqual('hello world')
        })

        describe('Caches Requests', () => {
            it('should return "hello world" even after API response changes', async () => {
                const response = await $vm.cache().get('/api')

                expect(response.message).toEqual('hello world')
                
                mock.onGet('/api').reply(200, {
                    message: 'hello africa'
                })

                const response2 = await $vm.cache().get('/api')

                expect(response.message).toEqual('hello world')
            })

            it('should return "hello world" even if previous similar request is active', async () => {
                const delayedMock = new MockAdapter(axios, { delayResponse: 1000 })

                delayedMock.onGet('/api').reply(200, {
                    message: 'hello world'
                })

                const firstPromise = $vm.cache().get('/api')

                const secondPromise = $vm.cache().get('/api')

                expect(firstPromise).toEqual(secondPromise)
            })
        })
    })
})