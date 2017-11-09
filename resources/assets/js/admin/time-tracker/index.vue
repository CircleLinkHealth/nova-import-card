<template>
    <span class="time-tracker">
        <time-display :seconds="seconds" />
        <inactivity-tracker />
    </span>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import InactivityTracker from './comps/inactivity-tracker'
    import TimeDisplay from './comps/time-display'
    import EventBus from './comps/event-bus'
    
    export default {
        name: 'time-tracker',
        props: {
            info: {
                type: Object,
                required: true
                /**
                 * {
                 *  wsUrl: 'https://clh-ws.io/time',
                 *  providerId: 0,
                 *  patientId: 0
                 * }
                 */
            }
        },
        data() {
            return {
                seconds: 0,
                socket: null
            }
        },
        components: { 
            'inactivity-tracker': InactivityTracker,
            'time-display': TimeDisplay
        },
        methods: {
            updateTime() {
                this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: 'update', info: this.info }));
            },
            createSocket() {
                try {
                    const self = this; //a way to keep the context
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(self.info.wsUrl);
        
                        socket.onmessage = (message) => {
                            if (message.data) {
                                const data = JSON.parse(message.data)
                                if (!!Number(data.seconds))
                                    self.seconds = self.previousSeconds + Number(data.seconds)
                                console.log(data);
                            }
                        }
                
                        socket.onopen = (ev) => {
                            console.log("socket connection opened", ev)
                            self.updateTime()
                        }
                
                        socket.onclose = (ev) => {
                            console.warn("socket connection has closed", ev)
                            self.socket = null;
                            EventBus.$emit("tracker:stop");

                            setTimeout(self.createSocket.bind(self), 3000);
                        }
        
                        return socket;
                    })()
                }
                catch (ex) {
                    console.error(ex);
                }
            }
        },
        mounted() {
            this.seconds = this.info.totalTime;
            this.previousSeconds = this.info.totalTime || 0;

            EventBus.$on('tracker:tick', () => {
                this.seconds++;
                this.$forceUpdate()
            })

            EventBus.$on('tracker:stop', () => {
                if (this.socket) this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: 'stop', info: this.info }))
            })

            EventBus.$on('tracker:start', () => {
                if (this.socket && this.socket.readyState === WebSocket.OPEN) 
                    this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: 'start', info: this.info }))
            })

            this.createSocket()
        }
    }
</script>

<style>
    
</style>