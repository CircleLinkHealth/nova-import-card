<template>
    <div>
        <div id="notifications-wrapper">
            <notifications name="connection-error"></notifications>
        </div>

        <div v-if="showLoader || !visible" :class="{ 'hide-tracker': hideTracker }">
            <div class="loader-filler"></div>
            <div class="loader-container">
                <loader></loader>
            </div>
        </div>
        <span v-if="visible" class="time-tracker">
            <div v-if="noLiveCount" class="no-live-count" :class="{ hidden: showLoader }">
                <div v-if="shouldShowCcmTime()" :class="[ hasBhiTime() ? 'col-md-6' : '' ]">
                    <div>
                        <small>CCM</small>
                    </div>
                    <div>
                        {{info.monthlyTime}}
                    </div>
                </div>
                <div v-if="hasBhiTime()" class="col-md-6">
                    <div>
                        <small>BHI</small>
                    </div>
                    <div>
                        {{info.monthlyBhiTime}}
                    </div>
                </div>
            </div>
            <bhi-switch ref="bhiSwitch" :is-manual-behavioral="info.isManualBehavioral"
                        :user-id="info.providerId" :is-bhi="info.isBehavioral" :is-ccm="info.isCcm"
                        v-if="!noLiveCount && !info.noBhiSwitch && (info.isCcm || info.isBehavioral)"></bhi-switch>

            <br><br>
            <span :class="{ hidden: showLoader, 'hide-tracker': hideTracker }">
                <time-display v-if="!noLiveCount" ref="timeDisplay" :seconds="totalTime" :no-live-count="!!noLiveCount"
                              :redirect-url="'manage-patients/' + info.patientId + '/activities'"/>
            </span>

            <inactivity-tracker :call-mode="callMode" ref="inactivityTracker"/>
            <away ref="away"/>
        </span>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config'
    import startupTime from '../../startup-time'
    import InactivityTracker from './comps/inactivity-tracker'
    import TimeDisplay from './comps/time-display'
    import EventBus from './comps/event-bus'
    import {Event} from 'vue-tables-2'
    import LoaderComponent from '../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader'
    import AwayComponent from './comps/away'
    import BhiComponent from './comps/bhi-switch'
    import Notifications from '../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/notifications/notifications';

    import {registerHandler, sendRequest} from "../../components/bc-job-manager";

    export default {
        name: 'time-tracker',
        props: {
            twilioEnabled: Boolean,
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
            'no-live-count': Boolean,
            'class-name': String,
            'hide-tracker': Boolean,
            'override-timeout': Boolean
        },
        data() {
            return {
                seconds: 0, /** from when page loads, till the page ends */
                visible: false,
                socket: null,
                startCount: 0,
                showTimer: true,
                showLoader: true,
                callMode: false,
                wsUrl: null,
                wsUrlFailOver: null,
                connectedOnce: false,
                connectionLossTimestamp: null,
                lastUpdatedBhiTime: 0,
                lastUpdatedCcmTime: 0,
            }
        },
        components: {
            'inactivity-tracker': InactivityTracker,
            'time-display': TimeDisplay,
            'loader': LoaderComponent,
            'away': AwayComponent,
            'bhi-switch': BhiComponent,
            'notifications': Notifications
        },
        computed: {
            formattedTime() {
                if (this.showLoader || !this.$refs.timeDisplay) {
                    return undefined;
                }
                return this.$refs.timeDisplay.getTime();
            },
            totalTime() {
                return this.seconds
            }
        },
        methods: {
            bhiTimeInSeconds(){
                //this makes sure that who ever calls this method gets time dynamically
                if(this.info.isManualBehavioral){
                    return this.seconds;
                }
                return this.lastUpdatedBhiTime ||  this.info.totalBHITime || 0;
            },
            ccmTimeInSeconds(){
                //this makes sure that who ever calls this method gets time dynamically
                if(!this.info.isManualBehavioral){
                    return this.seconds;
                }
                return this.lastUpdatedCcmTime || this.info.totalCCMTime || 0;
            },
            shouldShowCcmTime() {
                //we show ccm time, even if zero time. we do not show when empty string
                return this.info.monthlyBhiTime && this.info.monthlyBhiTime.length > 0;
            },
            hasBhiTime() {
                const zeroTime = "00:00:00";
                return this.info.monthlyBhiTime && this.info.monthlyBhiTime.length > 0 && this.info.monthlyBhiTime !== zeroTime;
            },
            timeSinceLastConnection() {
                return Math.round((new Date() - this.connectionLossTimestamp) / 1000);
            },
            updateTime() {
                if (this.info.initSeconds === 0) {

                    //console.log("last connection time", this.timeSinceLastConnection());
                    //console.log("startup time", startupTime() / 1000);

                    if (!this.connectedOnce) {
                        this.connectedOnce = true;
                        this.info.initSeconds = Math.round(startupTime() / 1000);
                    } else if (this.connectionLossTimestamp != null) {
                        this.info.initSeconds = this.timeSinceLastConnection();
                    }

                }
                //else {
                // why -1 ?
                // see socket.onclose
                //this.info.initSeconds = -1;
                //}
                this.startCount += 1;
                console.log('tracker:init-seconds', this.info.initSeconds);
                if (this.socket.readyState === this.socket.OPEN) {
                    this.socket.send(
                        JSON.stringify({
                            message: 'client:start',
                            info: this.info
                        })
                    );
                    if (this.overrideTimeout) {
                        // setTimeout(() => {
                        //     EventBus.$emit('modal-inactivity:timeouts:override', {
                        //         alertTimeout: 30,
                        //         logoutTimeout: 120,
                        //         alertTimeoutCallMode: 60,
                        //         logoutTimeoutCallMode: 150
                        //     })
                        // }, 1000)
                    }
                }
            },
            createSocket() {
                try {
                    const self = this; //a way to keep the context
                    self.socketReloadCount = (self.socketReloadCount || 0) + 1;
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(self.wsUrl);

                        socket.onmessage = (res) => {
                            if (res.data) {
                                const data = JSON.parse(res.data)
                                if (data.message === 'server:sync') {
                                    self.seconds = self.info.isManualBehavioral ? data.bhiSeconds : data.ccmSeconds
                                    self.lastUpdatedBhiTime = data.bhiSeconds;
                                    self.lastUpdatedCcmTime = data.ccmSeconds;
                                    self.visible = true //display the component when the previousSeconds value has been received from the server to keep the display up-to-date
                                    self.showLoader = false;
                                } else if (data.message === 'server:modal') {
                                    EventBus.$emit('away:trigger-modal')
                                } else if (data.message === 'server:logout') {
                                    EventBus.$emit("tracker:stop", true);
                                } else if (data.message === 'server:call-mode:enter') {
                                    self.callMode = true
                                    EventBus.$emit('server:call-mode', self.callMode)
                                } else if (data.message === 'server:call-mode:exit') {
                                    self.callMode = false
                                    EventBus.$emit('server:call-mode', self.callMode)
                                } else if (data.message === 'server:inactive-modal:close') {
                                    EventBus.$emit('modal-inactivity:reset', true)
                                } else if (data.message === 'server:bhi:switch') {
                                    EventBus.$emit('tracker:bhi:switch', data.mode)
                                    self.info.isCcm = data.hasOwnProperty('isCcm') ? data.isCcm : self.info.isCcm
                                    self.info.isBehavioral = data.hasOwnProperty('isBehavioral') ? data.isBehavioral : self.info.isBehavioral
                                }
                                //console.log(data);
                            }
                        }

                        socket.onopen = (ev) => {

                            Event.$emit('notifications-connection-error:dismissAll');

                            if (EventBus.isInFocus) {
                                self.updateTime()
                                self.callMode = false
                            } else {
                                self.startCount = 0;
                            }
                            // console.log("socket connection opened", ev, self.startCount, EventBus.isInFocus)
                            if (EventBus.isInFocus) EventBus.$emit('tracker:start')
                        }

                        socket.onclose = (ev) => {
                            console.warn("socket connection has closed", ev);
                            self.connectionLossTimestamp = new Date();
                            self.socket = null;
                            EventBus.$emit("tracker:stop");
                            self.startCount = 0;
                            self.info.initSeconds = 0;

                            //this used to set initSeconds to anything other than 0, which resulted to -1 in updateTime()
                            //cannot think a reason why.
                            //self.info.initSeconds = self.seconds;

                            console.log(self.info.totalTime, self.seconds);

                            //switch url and fail over url and try again
                            if (self.wsUrlFailOver) {
                                const temp = self.wsUrl;
                                self.wsUrl = self.wsUrlFailOver;
                                self.wsUrlFailOver = temp;
                            }

                            setTimeout(self.createSocket.bind(self), 3000);
                        }

                        socket.onerror = (err) => {

                            Event.$emit('notifications-connection-error:create', {
                                text: `Cannot connect to time tracker. If this note does not go away soon, please contact CLH support.`,
                                type: 'error',
                                noTimeout: true,
                                overwrite: true
                            });

                            console.error('socket-error:', err, self.info)
                        };

                        return socket;
                    })()
                } catch (ex) {
                    console.error(ex);
                }
            }
        },
        created() {
            registerHandler("logout_event", () => {
                EventBus.$emit("tracker:stop", true, true);
                return Promise.resolve({});
            });

            window.TimeTracker = this;
        },
        mounted() {

            // window.addEventListener("unload", () => {
            //     console.log('window is unloading', this.info.totalTime, this.seconds);
            // });

            this.wsUrl = this.info.wsUrl;
            this.wsUrlFailOver = this.info.wsUrlFailOver;

            this.previousSeconds = this.info.totalTime || 0;
            this.info.initSeconds = 0
            this.info.isManualBehavioral = (this.info.isBehavioral && !this.info.isCcm) || false

            if (this.info.disabled || !this.info.wsUrl) {
                this.visible = false;
            } else {

                EventBus.isInFocus = !document.hidden;
                console.log('document is ', EventBus.isInFocus ? 'focused' : 'not focused');

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
                    ACTIVITY: 'client:activity',
                    LOGOUT: 'client:logout',
                    BHI: 'client:bhi',
                    TIMEOUTS_OVERRIDE: 'client:timeouts:override'
                }

                EventBus.$on('tracker:start', () => {

                    if (this.state !== STATE.SHOW_INACTIVE_MODAL) {
                        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                            if (this.startCount === 0) this.updateTime();
                            this.state = STATE.ENTER
                            //this.info.startTime = getCarbonDateTimeStringInServerTimezone(new Date());
                            this.socket.send(JSON.stringify({message: STATE.ENTER, info: this.info}))
                        }
                    }
                })

                EventBus.$on('tracker:stop', (isLoggingOut, isFromLogoutEvent) => {
                    EventBus.$emit("inactivity:stop");

                    if (this.state !== STATE.SHOW_INACTIVE_MODAL) {
                        this.showTimer = false;
                        this.state = STATE.LEAVE;

                        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                            this.socket.send(JSON.stringify({message: STATE.LEAVE, info: this.info}))
                        }

                        // this.showLoader = true
                    }

                    if (isLoggingOut) {
                        //if we reached here from a logout_event, no need to broadcast again
                        isFromLogoutEvent = !!isFromLogoutEvent;
                        if (!isFromLogoutEvent) {
                            sendRequest("logout_event", {}, 1000);
                        }
                        location.href = rootUrl('auth/logout');
                    }
                });

                EventBus.$on('tracker:hide-inactive-modal', () => {
                    /** does the same as tracker:start without the conditional */
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        if (this.startCount === 0) this.updateTime();
                        this.state = STATE.ENTER
                        this.socket.send(JSON.stringify({message: STATE.ENTER, info: this.info}))
                    }
                })

                EventBus.$on('tracker:show-inactive-modal', () => {
                    if (this.socket) {
                        this.showTimer = false
                        this.state = STATE.SHOW_INACTIVE_MODAL;
                        this.socket.send(JSON.stringify({message: STATE.SHOW_INACTIVE_MODAL, info: this.info}))
                    }
                    // this.showLoader = true
                })

                EventBus.$on('tracker:modal:reply', (response) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.MODAL_RESPONSE, info: this.info, response}))
                    }
                })

                EventBus.$on('tracker:inactive-modal:close', (response) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({
                            message: STATE.CLOSE_INACTIVE_MODAL,
                            info: this.info,
                            response
                        }))
                    }
                })

                EventBus.$on('tracker:call-mode:enter', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.ENTER_CALL_MODE, info: this.info}))
                    }
                })

                EventBus.$on('tracker:call-mode:exit', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.EXIT_CALL_MODE, info: this.info}))
                    }
                })

                EventBus.$on('tracker:timeouts:override', (timeouts = {}) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.TIMEOUTS_OVERRIDE, info: this.info, timeouts}))
                    }
                })

                EventBus.$on('tracker:logout', () => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.LOGOUT, info: this.info}))
                    }
                })

                EventBus.$on('tracker:bhi:switch', (mode = false) => {
                    let shouldUpdateNetwork = false
                    if (this.info.isBehavioral && this.info.isCcm) {
                        shouldUpdateNetwork = (this.info.isManualBehavioral !== mode)
                        this.info.isManualBehavioral = mode
                    } else {
                        shouldUpdateNetwork = (this.info.isManualBehavioral !== mode)
                        this.info.isManualBehavioral = (this.info.isBehavioral && !this.info.isCcm) || false
                    }
                    if (this.socket && this.socket.readyState === WebSocket.OPEN && shouldUpdateNetwork) {
                        this.socket.send(JSON.stringify({message: STATE.BHI, info: this.info}))
                    }
                    console.log('tracker:bhi:switch', mode)
                })

                EventBus.$on('tracker:activity', (newInfo) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.ACTIVITY, info: newInfo}))
                    }
                });

                Event.$on('careplan:bhi', ({hasCcm, hasBehavioral}) => {
                    const shouldUpdateNetwork = (this.info.isBehavioral && this.info.isCcm) !== (hasCcm && hasBehavioral)
                    this.info.isBehavioral = hasBehavioral
                    this.info.isCcm = hasCcm
                    if (shouldUpdateNetwork) {
                        this.info.isManualBehavioral = (this.info.isBehavioral && !this.info.isCcm) || false
                        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                            this.socket.send(JSON.stringify({message: STATE.BHI, info: this.info}))
                        }
                    }
                    console.log('careplan:bhi:network-update', shouldUpdateNetwork, hasCcm, hasBehavioral)

                })

                this.createSocket()

                setInterval(() => {
                    if (this.socket && this.socket.readyState === this.socket.OPEN) {
                        this.socket.send(JSON.stringify({message: 'PING'}))
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

    #notifications-wrapper {
        z-index: 100;
        position: fixed;
        top: 65px;
        right: 15px;
        width: 300px;
        font-size: small;
        text-align: left;
    }

    .notifications-connection-error {
        position: absolute;
    }

    .no-live-count {
        max-width: 350px;
        margin: auto;
    }

</style>
