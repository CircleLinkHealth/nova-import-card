<template>
    <div class="row">
        <div class="col-md-12">
            <notifications :reverse="true"></notifications>
        </div>
    </div>
</template>

<script>
    import Notifications from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/notifications/notifications'
    import EventBus from './comps/event-bus'
    import timeDisplay from '../../util/time-display'

    export default {
        props: {
            wsRootUrl: String
        },
        computed: {
            
        },
        data () {
            return {
                socket: null
            }
        },
        components: {
            Notifications
        },
        methods: {
            createWsRootUrl (url) {
                return this.wsRootUrl + (url || '')
            },
            createSocket() {
                try {
                    const self = this; //a way to keep the context
                    self.socketReloadCount = (self.socketReloadCount || 0) + 1;
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(self.createWsRootUrl('/events'));
        
                        socket.onmessage = (res) => {
                            if (res.data) {
                                const data = JSON.parse(res.data)
                                if (data.message === 'server:logout') {
                                    const totalDuration = data.activities.reduce((a, b) => a + b.duration, 0)
                                    const patientSuffix = ((Number(data.patientId) ? ` when working on patient ${data.patientId}` : ''))
                                    const activities = data.activities.map(activity => activity.name).join(', ')

                                    EventBus.$emit('notifications:create', ({
                                        text: `Logged +${timeDisplay(totalDuration)} for practitioner ${data.providerId}` + patientSuffix + ` in ${activities}`,
                                        noTimeout: true
                                    }))
                                }
                                console.log(data);
                            }
                        }
                
                        socket.onopen = (ev) => {
                            EventBus.$emit('notifications:create', ({
                                text: 'connection opened',
                                type: 'warning',
                                noTimeout: true
                            }))
                            console.log('socket connection opened', ev)
                        }
                
                        socket.onclose = (ev) => {
                            EventBus.$emit('notifications:create', ({
                                text: 'connection closed',
                                type: 'error',
                                noTimeout: true
                            }))
                            console.warn("socket connection has closed", ev)
                            self.socket = null;
                            setTimeout(self.createSocket.bind(self), 3000);
                        }

                        socket.onerror = (err) => {
                            EventBus.$emit('notifications:create', ({
                                text: 'connection error ... see console',
                                type: 'error',
                                noTimeout: true
                            }))
                            console.error('socket-error:', err)
                        }
        
                        return socket;
                    })()
                }
                catch (ex) {
                    console.error(ex);
                }
            }
        },
        mounted () {
            this.createSocket()

            setInterval(() => {
                if (this.socket && this.socket.readyState === this.socket.OPEN) {
                    this.socket.send(JSON.stringify({ message: 'PING' }))
                }
            }, 5000)
        }
    }
</script>

<style>
    
</style>