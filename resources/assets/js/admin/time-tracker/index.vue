<template>
    <div>
        <div v-if="showLoader || !visible" :class="{ 'hide-tracker': hideTracker }">
            <div class="loader-filler"></div>
            <div class="loader-container">
                <loader></loader>
            </div>
        </div>
        <span v-if="visible" class="time-tracker">
            <div v-if="noLiveCount" :class="{ hidden: showLoader }">{{info.monthlyTime}}</div>
            <span :class="{ hidden: showLoader, 'hide-tracker': hideTracker }">
                <time-display v-if="!noLiveCount" ref="timeDisplay" :seconds="totalTime" :no-live-count="!!noLiveCount" :redirect-url="'manage-patients/' + info.patientId + '/activities'" />
            </span>
            <inactivity-tracker :call-mode="callMode" ref="inactivityTracker" />
            <away ref="away" />
        </span>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import startupTime from '../../startup-time'
    import InactivityTracker from './comps/inactivity-tracker'
    import TimeDisplay from './comps/time-display'
    import EventBus from './comps/event-bus'
    import LoaderComponent from '../../components/loader'
    import AwayComponent from './comps/away'
    
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
            'class-name': String,
            'hide-tracker': Boolean,
            'override-timeout': Boolean
        },
        data() {
            return {
                seconds: 0,/** from when page loads, till the page ends */
                visible: false,
                socket: null,
                startCount: 0,
                showTimer: true,
                showLoader: true,
                callMode: false
            }
        },
        components: { 
            'inactivity-tracker': InactivityTracker,
            'time-display': TimeDisplay,
            'loader': LoaderComponent,
            'away': AwayComponent
        },
        computed: {
            totalTime() {
                return this.seconds
            }
        },
        methods: {
            updateTime() {
                if (this.info.initSeconds == 0) this.info.initSeconds = Math.ceil(startupTime() / 1000)
                else this.info.initSeconds = -1
                this.startCount += 1;
                console.log('tracker:init-seconds', this.info.initSeconds)
                if (this.socket.readyState === this.socket.OPEN) {
                    this.socket.send(
                        JSON.stringify({ 
                            message: 'client:start', 
                            info: this.info
                        })
                    );
                    if (this.overrideTimeout) {
                        setTimeout(() => {
                            EventBus.$emit('modal-inactivity:timeouts:override', {
                                alertTimeout: 30, 
                                logoutTimeout: 120,
                                alertTimeoutCallMode: 60, 
                                logoutTimeoutCallMode: 150
                            })
                        }, 1000)
                    }
                }
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
                                if (data.message === 'server:sync') {
                                    self.seconds = data.seconds
                                    self.visible = true //display the component when the previousSeconds value has been received from the server to keep the display up-to-date
                                    self.showLoader = false
                                }
                                else if (data.message === 'server:modal') {
                                    EventBus.$emit('away:trigger-modal')
                                }
                                else if (data.message === 'server:logout') {
                                    EventBus.$emit("tracker:stop")
                                    location.href = rootUrl('auth/logout')
                                }
                                else if (data.message === 'server:call-mode:enter') {
                                    self.callMode = true
                                    EventBus.$emit('server:call-mode', self.callMode)
                                }
                                else if (data.message === 'server:call-mode:exit') {
                                    self.callMode = false
                                    EventBus.$emit('server:call-mode', self.callMode)
                                }
                                else if (data.message === 'server:inactive-modal:close') {
                                    EventBus.$emit('modal-inactivity:reset', true)
                                }
                                console.log(data);
                            }
                        }
                
                        socket.onopen = (ev) => {
                            if (EventBus.isInFocus) {
                                self.updateTime()
                                self.callMode = false
                            }
                            else {
                                self.startCount = 0;
                            }
                            console.log("socket connection opened", ev, self.startCount, EventBus.isInFocus)
                            if (EventBus.isInFocus) EventBus.$emit('tracker:start')
                        }
                
                        socket.onclose = (ev) => {
                            console.warn("socket connection has closed", ev)
                            self.socket = null;
                            EventBus.$emit("tracker:stop");
                            self.startCount = 0;
                            self.info.initSeconds = self.seconds
                            console.log(self.info.totalTime, self.seconds)

                            setTimeout(self.createSocket.bind(self), 3000);
                        }

                        socket.onerror = (err) => {
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
        mounted() {
            this.previousSeconds = this.info.totalTime || 0;
            this.info.initSeconds = 0

            if (this.info.disabled || !this.info.wsUrl) {
                this.visible = false;
            }
            else {
                EventBus.isInFocus = true;

                EventBus.$on('tracker:tick', () => {
                    this.seconds++;
                    this.$forceUpdate()
                })

                const STATE = {
                    LEAVE: 'client:leave',
                    ENTER: 'client:enter',
                    INACTIVITY_CANCEL: 'inactivity-cancel',
                    MODAL_RESPONSE: 'client:modal',
                    SHOW_INACTIVE_MODAL: 'client:inactive-modal:show',
                    CLOSE_INACTIVE_MODAL: 'client:inactive-modal:close',
                    ENTER_CALL_MODE: 'client:call-mode:enter',
                    EXIT_CALL_MODE: 'client:call-mode:exit',
                    TIMEOUTS_OVERRIDE: 'client:timeouts:override',
                    LOGOUT: 'client:logout'
                }

                EventBus.$on('tracker:start', () => {
                    if (this.state !== STATE.SHOW_INACTIVE_MODAL) {
                        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                            if (this.startCount === 0) this.updateTime();
                            this.state = STATE.ENTER
                            this.socket.send(JSON.stringify({ message: STATE.ENTER, info: this.info }))
                        }
                    }
                })

                EventBus.$on('tracker:stop', () => {
                    if (this.state !== STATE.SHOW_INACTIVE_MODAL) {
                        if (this.socket) {
                            this.showTimer = false
                            this.state = STATE.LEAVE;
                            this.socket.send(JSON.stringify({ message: STATE.LEAVE, info: this.info }))
                        }
                        this.showLoader = true
                    }
                })

                EventBus.$on('tracker:hide-inactive-modal', () => {
                    /** does the same as tracker:start without the conditional */
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        if (this.startCount === 0) this.updateTime();
                        this.state = STATE.ENTER
                        this.socket.send(JSON.stringify({ message: STATE.ENTER, info: this.info }))
                    }
                })

                EventBus.$on('tracker:show-inactive-modal', () => {
                    if (this.socket) {
                        this.showTimer = false
                        this.state = STATE.SHOW_INACTIVE_MODAL;
                        this.socket.send(JSON.stringify({ message: STATE.SHOW_INACTIVE_MODAL, info: this.info }))
                    }
                    this.showLoader = true
                })

                EventBus.$on('tracker:modal:reply', (response) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.MODAL_RESPONSE, info: this.info, response }))
                    }
                })

                EventBus.$on('tracker:inactive-modal:close', (response) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.CLOSE_INACTIVE_MODAL, info: this.info, response }))
                    }
                })

                EventBus.$on('tracker:call-mode:enter', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.ENTER_CALL_MODE, info: this.info }))
                    }
                })

                EventBus.$on('tracker:call-mode:exit', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.EXIT_CALL_MODE, info: this.info }))
                    }
                })

                EventBus.$on('tracker:timeouts:override', (timeouts = {}) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.TIMEOUTS_OVERRIDE, info: this.info, timeouts }))
                    }
                })

                EventBus.$on('tracker:logout', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({ message: STATE.LOGOUT, info: this.info }))
                    }
                })

                this.createSocket()

                setInterval(() => {
                    if (this.socket.readyState === this.socket.OPEN) {
                        this.socket.send(JSON.stringify({ message: 'PING' }))
                    }
                }, 5000)
            }
        }
    }
</script>

<style>
    span.tt-hidden {
        visibility: hidden;
    }

    div.loader-container {
        width: 84px;
        position: absolute;
        right: -18px;
        top: 0px;
    }

    div.loader-filler {
        height: 30px;
    }

    .hide-tracker {
        display: none;
    }

    .top-20 {
        margin-top: 20px;
    }
</style>