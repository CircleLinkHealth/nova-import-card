<template>
    <div class="time-tracker">
        <time-display :seconds="seconds" />
        <inactivity-tracker />
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import InactivityTracker from './comps/inactivity-tracker'
    import TimeDisplay from './comps/time-display'
    
    export const TimeTracker = {
        name: 'time-tracker',
        props: {
            info: {
                type: Object,
                required: true
                /**
                 * {
                 *  wsUrl: 'https://clh-ws.io/time',
                 *  userId: 0,
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
                this.socket.send(JSON.stringify(Object.assign(this.info, { id: this.info.userId, message: 'update' })));
            },
            createSocket() {
                try {
                    const self = this; //a way to keep the context
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(this.info.wsUrl);
        
                        socket.onmessage = (message) => {
                            if (message.data) {
                                const data = JSON.parse(message.data)
                                if (!!Number(data.seconds))
                                    self.seconds = Number(data.seconds)
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
                            self.$emit("tracker:stop");

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
            this.$on('tracker:tick', () => {
                this.seconds++;
                this.$forceUpdate()
            })

            this.$on('tracker:stop', () => {
                if (this.socket) this.socket.send(JSON.stringify(Object.assign(this.info, { id: this.info.userId, message: 'stop' })))
            })

            this.$on('tracker:start', () => {
                if (this.socket && this.socket.readyState === WebSocket.OPEN) 
                    this.socket.send(JSON.stringify(Object.assign(this.info, { id: this.info.userId, message: 'start' })))
            })

            this.createSocket()
        }
    }
</script>

<style>
    
</style>