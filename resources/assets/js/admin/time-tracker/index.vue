<template>
    <span v-if="visible" class="time-tracker" :class="className">
        <div v-if="noLiveCount">{{info.monthlyTime}}</div>
        <time-display v-if="!noLiveCount" ref="timeDisplay" :seconds="totalTime" :no-live-count="!!noLiveCount" :redirect-url="'manage-patients/' + info.patientId + '/activities'" />
        <inactivity-tracker ref="inactivityTracker" />
    </span>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import startupTime from '../../startup-time'
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
            },
            'no-live-count': Number,
            'class-name': String
        },
        data() {
            return {
                seconds: 0,/** from when page loads, till the page ends */
                previousSeconds: 0,/**from the DB, ccm total time */
                visible: false,
                socket: null,
                startCount: 0
            }
        },
        components: { 
            'inactivity-tracker': InactivityTracker,
            'time-display': TimeDisplay
        },
        computed: {
            totalTime() {
                return this.seconds + this.previousSeconds
            }
        },
        methods: {
            updateTime() {
                this.info.initSeconds = Math.ceil(startupTime() / 1000)
                this.startCount += 1;
                console.log('tracker:init-seconds', this.info.initSeconds)
                this.socket.send(
                    JSON.stringify({ 
                            id: this.info.providerId, 
                            patientId: this.info.patientId, 
                            message: 'start', 
                            info: this.info
                        })
                    );
            },
            createSocket() {
                try {
                    const self = this; //a way to keep the context
                    self.socketReloadCount = (self.socketReloadCount || 0) + 1;
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(self.info.wsUrl);
        
                        socket.onmessage = (res) => {
                            if (res.data) {
                                const data = JSON.parse(res.data)
                                if (data.message === 'tt:update-previous-seconds' && !!Number(data.previousSeconds)) {
                                    self.previousSeconds = Math.max(data.previousSeconds, self.previousSeconds)
                                    self.visible = true //display the component when the previousSeconds value has been received from the server to keep the display up-to-date
                                }
                                else if (data.message === 'tt:resume') {
                                    if (!self.noLiveCount && !!Number(data.seconds)) {
                                        self.seconds = Number(data.seconds)
                                    }
                                }
                                console.log(data);
                            }
                        }
                
                        socket.onopen = (ev) => {
                            if (EventBus.isInFocus) {
                                EventBus.$emit("tracker:start")
                            }
                            else {
                                self.startCount = 0;
                            }
                            console.log("socket connection opened", ev, self.startCount, EventBus.isInFocus)
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
            this.previousSeconds = this.info.totalTime || 0;

            if (this.info.disabled) {
                this.visible = false;
            }
            else {
                EventBus.isInFocus = true;

                EventBus.$on('tracker:tick', () => {
                    this.seconds++;
                    this.$forceUpdate()
                })

                const STATE = {
                    STOP: 'stop',
                    START: 'resume',
                    INACTIVITY_CANCEL: 'inactivity-cancel'
                }

                EventBus.$on('tracker:stop', () => {
                    if (this.socket) {
                        this.state = STATE.STOP;
                        this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: STATE.STOP, info: this.info }))
                    }
                })

                EventBus.$on('tracker:start', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        if (this.startCount === 0) this.updateTime();
                        this.state = STATE.START
                        this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: STATE.START, info: this.info }))
                    }
                })

                EventBus.$on('tracker:inactivity-cancel', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ id: this.info.providerId, patientId: this.info.patientId, message: STATE.INACTIVITY_CANCEL }))
                        setTimeout(() => {
                            document.location.href = rootUrl('manage-patients/dashboard')
                        }, 700)
                    }
                })

                this.createSocket()
            }
        }
    }
</script>

<style>
    
</style>