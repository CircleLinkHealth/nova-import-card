<template>
    <div :class="className">
        <div id="notifications-wrapper">
            <notifications name="connection-error"></notifications>
        </div>

        <div v-if="showLoader || !visible" v-show="!hideTracker">
            <div class="loader-filler"></div>
            <div class="loader-container">
                <loader></loader>
            </div>
        </div>

        <span v-if="visible" class="time-tracker">
            <time-display-all-chargeable-services-static v-if="noLiveCount" v-show="!showLoader && !hideTracker"
                                                         :route-activities="routeActivities"
                                                         :chargeable-services="info.chargeableServices">
            </time-display-all-chargeable-services-static>

            <template v-if="!disableTimeTracking">

                <template v-if="!noLiveCount && !info.noBhiSwitch && hasChargeableServices">

                    <chargeable-services-switch :chargeable-service-id="info.chargeableServiceId"
                                                :chargeable-services="info.chargeableServices">
                    </chargeable-services-switch>

                    <br><br>

                </template>

                <span :class="{ hidden: showLoader }" v-show="!hideTracker">
                    <time-display v-if="!noLiveCount" ref="timeDisplay" :seconds="totalTime"
                                  :no-live-count="!!noLiveCount"
                                  :redirect-url="routeActivities"/>
                </span>

                <inactivity-tracker :call-mode="callMode" ref="inactivityTracker"/>
                <away ref="away"/>
            </template>
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
    import LoaderComponent from '../../components/loader'
    import AwayComponent from './comps/away'
    import ChargeableServicesSwitch from './comps/chargeable-services-switch';
    import Notifications from '../../components/shared/notifications/notifications-event-based';

    import {registerHandler, sendRequest} from "../../components/bc-job-manager";
    import TimeDisplayAllChargeableServicesStatic from "./comps/time-display-all-chargeable-services-static";

    const CCM = 'CCM';
    const RPM = 'RPM';
    const BHI = 'BHI';

    let self;
    let serverMessageHandlers;
    let socketHandlers;

    export default {
        name: 'time-tracker',
        props: {
            disableTimeTracking: Boolean,
            twilioEnabled: Boolean,
            routeActivities: String,
            noLiveCount: Boolean,
            className: String,
            hideTracker: Boolean,
            overrideTimeout: Boolean,
            info: {
                type: Object,
                required: true
            },
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
                ccmChargeableServiceId: null,
                bhiChargeableServiceId: null,
            }
        },
        components: {
            TimeDisplayAllChargeableServicesStatic,
            ChargeableServicesSwitch,
            'inactivity-tracker': InactivityTracker,
            'time-display': TimeDisplay,
            'loader': LoaderComponent,
            'away': AwayComponent,
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
            },
            hasChargeableServices() {
                return this.info.chargeableServices.filter(cs => cs.chargeable_service.id > -1).length > 0;
            }
        },
        methods: {
            bhiTimeInSeconds() {
                //this makes sure that who ever calls this method gets time dynamically
                if (this.info.chargeableServiceId === this.bhiChargeableServiceId) {
                    return this.seconds;
                }

                if (this.lastUpdatedBhiTime) {
                    return this.lastUpdatedBhiTime;
                }

                const bhiCs = this.info.chargeableServices.find(cs => cs.chargeable_service.id === this.bhiChargeableServiceId);
                return bhiCs ? bhiCs.seconds : 0;
            },
            ccmTimeInSeconds() {
                //this makes sure that who ever calls this method gets time dynamically
                if (!this.info.chargeableServiceId === this.ccmChargeableServiceId) {
                    return this.seconds;
                }

                if (this.lastUpdatedCcmTime) {
                    return this.lastUpdatedCcmTime;
                }

                const ccmCs = this.info.chargeableServices.find(cs => cs.chargeable_service.id === this.ccmChargeableServiceId);
                return ccmCs ? ccmCs.seconds : 0;
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

            parseSocketMessage(message) {
                try {
                    return JSON.parse(message)
                } catch (e) {
                    console.error(e);
                    return null;
                }
            },

            setupBrowserMessageHandlers() {
                registerHandler("logout_event", () => {
                    EventBus.$emit("tracker:stop", true, true);
                    return Promise.resolve({});
                });
            },

            setupSocketMessageHandlers() {
                socketHandlers = {
                    'message': (res) => {
                        if (!res || !res.data) {
                            console.error('missing data from socket message:' + JSON.stringify((res || {})));
                            return;
                        }

                        const data = self.parseSocketMessage(res.data);
                        self.handleServerMessage(data);
                    },
                    'open': (ev) => {
                        Event.$emit('notifications-connection-error:dismissAll');

                        if (EventBus.isInFocus) {
                            self.updateTime()
                            self.callMode = false
                        } else {
                            self.startCount = 0;
                        }
                        // console.log("socket connection opened", ev, self.startCount, EventBus.isInFocus)
                        if (EventBus.isInFocus) EventBus.$emit('tracker:start')
                    },
                    'close': (ev) => {
                        console.warn("socket connection has closed", ev);
                        self.connectionLossTimestamp = new Date();
                        self.socket = null;
                        EventBus.$emit("tracker:stop");
                        self.startCount = 0;
                        self.info.initSeconds = 0;

                        console.log(JSON.stringify(self.info.chargeableServices), self.seconds);

                        //switch url and fail over url and try again
                        if (self.wsUrlFailOver) {
                            const temp = self.wsUrl;
                            self.wsUrl = self.wsUrlFailOver;
                            self.wsUrlFailOver = temp;
                        }

                        setTimeout(self.createSocket.bind(self), 3000);
                    },
                    error: (err) => {
                        Event.$emit('notifications-connection-error:create', {
                            text: `Cannot connect to time tracker. If this note does not go away soon, please contact CLH support.`,
                            type: 'error',
                            noTimeout: true,
                            overwrite: true
                        });

                        console.error('socket-error:', err, self.info);

                        //todo: verify this
                        self.showLoader = true;
                    }
                }
            },

            setupServerMessageHandlers() {
                serverMessageHandlers = {
                    'server:sync': (data) => {
                        const selected = data.seconds_per_chargeable_service.find(cs => self.info.chargeableServiceId === cs.chargeable_service_id);
                        if (selected) {
                            self.seconds = selected.seconds;
                        }
                        if (self.ccmChargeableServiceId) {
                            const ccmSync = data.seconds_per_chargeable_service.find(cs => self.ccmChargeableServiceId === cs.chargeable_service_id);
                            if (ccmSync) {
                                self.lastUpdatedCcmTime = ccmSync.seconds;
                            }
                        }
                        if (self.bhiChargeableServiceId) {
                            const bhiSync = data.seconds_per_chargeable_service.find(cs => self.bhiChargeableServiceId === cs.chargeable_service_id);
                            if (bhiSync) {
                                self.lastUpdatedBhiTime = bhiSync.seconds;
                            }
                        }

                        self.visible = true //display the component when the previousSeconds value has been received from the server to keep the display up-to-date
                        self.showLoader = false;
                    },
                    'server:modal': (data) => {
                        EventBus.$emit('away:trigger-modal');
                    },
                    'server:logout': (data) => {
                        EventBus.$emit("tracker:stop", true);
                    },
                    'server:call-mode:enter': (data) => {
                        self.callMode = true;
                        EventBus.$emit('server:call-mode', self.callMode);
                    },
                    'server:call-mode:exit': (data) => {
                        self.callMode = false;
                        EventBus.$emit('server:call-mode', self.callMode);
                    },
                    'server:inactive-modal:close': (data) => {
                        EventBus.$emit('modal-inactivity:reset', true);
                    },
                    'server:chargeable-service:switch': (data) => {
                        EventBus.$emit('tracker:chargeable-service:switch', data.chargeableServiceId);
                    },
                };
            },

            handleServerMessage(data) {
                const handler = serverMessageHandlers[data.message];
                if (!handler) {
                    console.error('could not handle:' + JSON.stringify(data));
                    return;
                }
                handler(data);
            },

            createSocket() {
                try {
                    self.socketReloadCount = (self.socketReloadCount || 0) + 1;
                    this.socket = this.socket || (function () {
                        const socket = new WebSocket(self.wsUrl);
                        for (let handlerName in socketHandlers) {
                            if (!socketHandlers.hasOwnProperty(handlerName)) {
                                continue;
                            }
                            socket.addEventListener(handlerName, socketHandlers[handlerName]);
                        }

                        return socket;
                    })()
                } catch (ex) {
                    console.error(ex);
                }
            },

            setChargeableServiceId(chargeableServices = null) {
                if (chargeableServices) {
                    this.info.chargeableServices = chargeableServices;
                } else {
                    this.info.chargeableServices = this.info.chargeableServices.data ? this.info.chargeableServices.data : this.info.chargeableServices;
                }

                const allIds = this.info.chargeableServices.map(cs => cs.chargeable_service.id);
                if (this.info.chargeableServiceId && allIds.indexOf(this.info.chargeableServiceId) > -1) {
                    return false;
                }

                const ccmCs = this.info.chargeableServices.find(cs => cs.chargeable_service.display_name === CCM);
                const rpmCs = this.info.chargeableServices.find(cs => cs.chargeable_service.display_name === RPM);
                const bhiCs = this.info.chargeableServices.find(cs => cs.chargeable_service.display_name === BHI);

                if (ccmCs) {
                    this.ccmChargeableServiceId = ccmCs.chargeable_service.id;
                }
                if (bhiCs) {
                    this.bhiChargeableServiceId = bhiCs.chargeable_service.id;
                }

                if (ccmCs) {
                    this.info.chargeableServiceId = ccmCs.chargeable_service.id;
                } else if (rpmCs) {
                    this.info.chargeableServiceId = rpmCs.chargeable_service.id
                } else if (bhiCs) {
                    this.info.chargeableServiceId = bhiCs.chargeable_service.id
                } else if (this.info.chargeableServices.length) {
                    this.info.chargeableServiceId = this.info.chargeableServices[0].chargeable_service.id;
                } else {
                    this.info.chargeableServiceId = -1;
                }

                return true;
            }
        },
        created() {
            self = this;
            window.TimeTracker = this;

            if (this.disableTimeTracking) {
                return;
            }

            this.setChargeableServiceId();
            this.setupServerMessageHandlers();
            this.setupBrowserMessageHandlers();
            this.setupSocketMessageHandlers();
        },
        mounted() {

            if (this.disableTimeTracking) {
                this.showLoader = false;
                this.visible = true;
                return;
            }

            this.wsUrl = this.info.wsUrl;
            this.wsUrlFailOver = this.info.wsUrlFailOver;

            this.previousSeconds = this.info.totalTime || 0;
            this.info.initSeconds = 0;
            this.info.isCcm = this.info.chargeableServices.filter(cs => cs.chargeable_service.display_name === CCM).length === 1;
            this.info.isBehavioral = this.info.chargeableServices.filter(cs => cs.chargeable_service.display_name === BHI).length === 1;

            if (!this.info.wsUrl) {
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
                    CHARGEABLE_SERVICE_CHANGE: 'client:chargeable-service-change',
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
                            sendRequest("logout_event", {}, 1000)
                                .catch(err => console.warn(err));
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

                EventBus.$on('tracker:chargeable-service:switch', (csId) => {
                    const shouldUpdateNetwork = this.info.chargeableServiceId !== csId;
                    const hasCsId = this.info.chargeableServices.some(cs => cs.chargeable_service.id === csId);
                    if (hasCsId && csId !== -1) {
                        this.info.chargeableServiceId = csId;
                    }
                    if (this.info.chargeableServiceId === -1) {
                        this.setChargeableServiceId();
                    }
                    if (this.socket && this.socket.readyState === WebSocket.OPEN && shouldUpdateNetwork) {
                        this.socket.send(JSON.stringify({message: STATE.CHARGEABLE_SERVICE_CHANGE, info: this.info}));
                    }
                    console.log('tracker:chargeable-service:switch', csId);
                });

                EventBus.$on('tracker:activity', (newInfo) => {
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        this.socket.send(JSON.stringify({message: STATE.ACTIVITY, info: newInfo}))
                    }
                });

                Event.$on('careplan:ccd-problems-update', (chargeableServices) => {
                    const shouldUpdateNetwork = this.setChargeableServiceId(chargeableServices);
                    if (this.socket && this.socket.readyState === WebSocket.OPEN && shouldUpdateNetwork) {
                        this.socket.send(JSON.stringify({message: STATE.CHARGEABLE_SERVICE_CHANGE, info: this.info}));
                    }
                    console.log('careplan:ccd-problems-update, should update network:', shouldUpdateNetwork);
                });

                this.createSocket();

                setInterval(() => {
                    if (this.socket && this.socket.readyState === this.socket.OPEN) {
                        this.socket.send(JSON.stringify({message: 'PING'}))
                    }
                }, 5000);
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

    .color-grey {
        color: #7b7d81;
    }

    .color-green {
        color: #47beab;
    }

</style>
