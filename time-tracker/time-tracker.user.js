"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const events_1 = require("events");
const utils_fn_1 = require("./utils.fn");
const axios_1 = __importDefault(require("axios"));
const axios_retry_1 = __importStar(require("axios-retry"));
const logger_1 = require("../logger");
const user_time_1 = require("../cache/user-time");
const sync_with_cpm_1 = require("../sockets/sync.with.cpm");
axios_retry_1.default(axios_1.default, { retries: 3, retryDelay: axios_retry_1.exponentialDelay });
const errorLogger = logger_1.getErrorLogger();
class TimeTrackerUser {
    constructor(info, $emitter = new events_1.EventEmitter()) {
        this.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS = 30;
        this.ALERT_TIMEOUT = 2 * 60;
        this.LOGOUT_TIMEOUT = 10 * 60;
        this.ALERT_TIMEOUT_CALL_MODE = 30 * 60;
        this.LOGOUT_TIMEOUT_CALL_MODE = 60 * 60;
        this.$emitter = $emitter;
        this.setup(info);
    }
    setup(info) {
        utils_fn_1.validateInfo(info);
        this.key = user_time_1.getCacheKey(info);
        this.inactiveSeconds = 0;
        this.activities = [];
        this.patientId = info.patientId;
        this.providerId = info.providerId;
        this.url = info.submitUrl;
        this.timeSyncUrl = info.timeSyncUrl;
        this.programId = info.programId;
        this.ipAddr = info.ipAddr;
        this.chargeableServices = info.chargeableServices || [];
        this.chargeableServiceId = info.chargeableServiceId;
        this.noLiveCount = info.noLiveCount;
        this.patientFamilyId = info.patientFamilyId;
        this.isLoggingOut = null;
    }
    validateWebSocket(ws) {
        if (!ws)
            throw new Error('[ws] must be a valid WebSocket instance');
    }
    serverEnterHandler(patientId, patientFamilyId, enrolleeId) {
        if (!Number(enrolleeId)) {
            //i don't understand this logic
            if (!Number(patientId) || (Number(patientId) && (Number(patientFamilyId) || Number(this.patientFamilyId)) && (patientFamilyId != this.patientFamilyId))) {
                this.exitCallMode();
            }
        }
    }
    ;
    getDurationForCsId(csId) {
        return this.activities.filter(a => a.chargeableServiceId === csId).reduce((a, b) => a + b.duration, 0);
    }
    getTotalTimeForCsId(csId) {
        const cs = this.chargeableServices.filter(cs => cs.chargeable_service.id === csId)[0];
        return cs ? cs.total_time : 0;
    }
    getTotalSecondsForCsId(csId) {
        return this.getDurationForCsId(csId) + this.getTotalTimeForCsId(csId);
    }
    getTotalTimeForCsIdFromCache(csId) {
        return user_time_1.getTimeForCsId(this.key, csId);
    }
    getTotalSecondsForCsIdFromCache(csId) {
        return this.getDurationForCsId(csId) + this.getTotalTimeForCsIdFromCache(csId);
    }
    /**
     * @returns {Number} initial-total-time in seconds (excludes current duration of activities)
     */
    get totalTime() {
        return this.chargeableServices.reduce((a, b) => a + b.total_time, 0);
    }
    get totalTimeFromCache() {
        return user_time_1.getTime(this.key).reduce((a, b) => a + b.time, 0);
    }
    /**
     * @returns {Number} total duration in seconds of activities excluding initial-total-time
     */
    get totalDuration() {
        return this.activities.reduce((a, b) => a + b.duration, 0);
    }
    /**
     * @returns {Number} total duration in seconds of activities plus initial-total-time
     */
    get totalSeconds() {
        return this.totalTime + this.totalDuration;
    }
    /**
     * @returns {Array} list of all sockets in all activities belongs to this user
     */
    get allSockets() {
        return this.activities.map(activity => activity.sockets).reduce((a, b) => a.concat(b), []);
    }
    /**
     * @returns {Boolean} whether or not a call is being made
     */
    get callMode() {
        return this.activities.reduce((a, b) => (a || b.callMode), false);
    }
    /**
     *
     * @param {any} data JSON or string you want to send via web sockets
     */
    broadcast(data) {
        this.allSockets.forEach(ws => {
            if (ws.readyState === ws.OPEN) {
                ws.send(JSON.stringify(data));
            }
        });
    }
    inactivityRequiresNoModal() {
        return this.inactiveSeconds < (!this.callMode ? this.ALERT_TIMEOUT : this.ALERT_TIMEOUT_CALL_MODE); // 2 minutes if !call-mode and 15 minutes if in call-mode (120, 900)
    }
    inactivityRequiresModal() {
        return !this.inactivityRequiresNoModal() && this.inactiveSeconds < (!this.callMode ? this.LOGOUT_TIMEOUT : this.LOGOUT_TIMEOUT_CALL_MODE); // 10 minutes if !call-mode and 20 minutes if in call-mode (600, 1200)
    }
    inactivityRequiresLogout() {
        return !this.inactivityRequiresModal() && !this.inactivityRequiresNoModal();
    }
    overrideTimeouts(options = {}) {
        this.ALERT_TIMEOUT = Math.ceil(options.alertTimeout) || this.ALERT_TIMEOUT;
        this.LOGOUT_TIMEOUT = Math.ceil(options.logoutTimeout) || this.LOGOUT_TIMEOUT;
        this.ALERT_TIMEOUT_CALL_MODE = Math.ceil(options.alertTimeoutCallMode) || this.ALERT_TIMEOUT_CALL_MODE;
        this.LOGOUT_TIMEOUT_CALL_MODE = Math.ceil(options.logoutTimeoutCallMode) || this.LOGOUT_TIMEOUT_CALL_MODE;
    }
    findActivity(info) {
        if (info.isFromCaPanel) {
            return this.activities.find(item => item.name == info.activity && item.enrolleeId == info.enrolleeId);
        }
        const result = this.activities.find(item => {
            return (item.name == info.activity)
                && (item.chargeableServiceId === info.chargeableServiceId);
        });
        if (result && !result.start_time) {
            result.start_time = info.startTime;
        }
        return result;
    }
    changeChargeableService(info) {
        this.broadcast({
            message: 'server:chargeable-service:switch',
            chargeableServiceId: info.chargeableServiceId
        });
        this.chargeableServiceId = info.chargeableServiceId;
    }
    closeOtherSameActivityWithOtherChargeableServiceId(info, ws) {
        const activities = this.activities.filter(item => (item.name == info.activity) && (item.chargeableServiceId !== info.chargeableServiceId));
        for (let activity of activities) {
            console.log(`Removing socket from ${activity.name}-${activity.chargeableServiceId}`);
            activity.sockets.splice(activity.sockets.indexOf(ws), 1);
        }
    }
    addToChargeableService(id, code, name, durationSeconds, sync = true) {
        if (!this.chargeableServices) {
            this.chargeableServices = [];
        }
        const existing = this.chargeableServices.find(item => item.chargeable_service.id === id);
        if (existing) {
            existing.total_time += durationSeconds;
        }
        else {
            this.chargeableServices.push({
                patient_user_id: Number(this.patientId),
                total_time: durationSeconds,
                chargeable_service: {
                    display_name: name,
                    id: id,
                    code: code
                }
            });
        }
        if (sync) {
            this.sync();
        }
    }
    setChargeableServices(info) {
        if (!info || !info.chargeableServices) {
            return;
        }
        if (!this.chargeableServices.length) {
            this.chargeableServices = info.chargeableServices || [];
        }
        for (let i = 0; i < info.chargeableServices.length; i++) {
            const cs = info.chargeableServices[i];
            const existing = this.chargeableServices.find(item => item.chargeable_service.id === cs.chargeable_service.id);
            if (existing) {
                existing.total_time = Math.max(Number(existing.total_time), Number(cs.total_time));
            }
            else {
                this.chargeableServices.push(cs);
            }
        }
    }
    resetTimeForChargeableServices() {
        for (let i = 0; i < this.chargeableServices.length; i++) {
            const cs = this.chargeableServices[i];
            cs.total_time = 0;
        }
    }
    addActivitiesDurationToTotalTime() {
        for (let i = 0; i < this.activities.length; i++) {
            const activity = this.activities[i];
            let found = false;
            for (let j = 0; j < this.chargeableServices.length; j++) {
                const cs = this.chargeableServices[j];
                if (!activity.chargeableServiceId) {
                    activity.chargeableServiceId = -1;
                }
                if (activity.chargeableServiceId === cs.chargeable_service.id) {
                    cs.total_time += activity.duration;
                    found = true;
                    break;
                }
            }
            if (!found) {
                this.chargeableServices.push({
                    chargeable_service: {
                        id: -1,
                        code: 'NONE',
                        display_name: 'NONE'
                    },
                    total_time: activity.duration
                });
            }
        }
    }
    start(info, ws) {
        /**
         * to be executed when a page is opened
         */
        utils_fn_1.validateInfo(info);
        this.validateWebSocket(ws);
        this.setChargeableServices(info);
        this.enter(info, ws);
        ws.providerId = info.providerId;
        ws.patientId = info.patientId;
        ws.isFromCaPanel = info.isFromCaPanel;
        let activity = this.findActivity(info);
        if (!!Number(info.initSeconds) && this.allSockets.length <= 1 && activity) {
            /**
             * make sure the page load time is taken into account
             */
            activity.duration += info.initSeconds;
        }
        if (this.callMode) {
            ws.send(JSON.stringify({ message: 'server:call-mode:enter' }));
        }
        else {
            ws.send(JSON.stringify({ message: 'server:call-mode:exit' }));
        }
        if (this.chargeableServiceId !== info.chargeableServiceId) {
            ws.send(JSON.stringify({
                message: 'server:chargeable-service:switch',
                chargeableServiceId: this.chargeableServiceId
            }));
        }
    }
    enter(info, ws) {
        /*
         * to be executed on client:enter when the client focuses on a page
         */
        utils_fn_1.validateInfo(info);
        this.validateWebSocket(ws);
        let activity = this.findActivity(info);
        if (!activity) {
            activity = utils_fn_1.createActivity(info);
            activity.sockets.push(ws);
            this.activities.push(activity);
        }
        else if (activity) {
            if (activity.sockets.indexOf(ws) < 0) {
                activity.sockets.push(ws);
            }
        }
        this.closeAllModals();
        /**
         * check inactive seconds
         */
        if (this.inactiveSeconds) {
            if (this.inactivityRequiresNoModal()) {
                this.handleNoModal(info);
            }
            else if (this.inactivityRequiresModal()) {
                if (ws.readyState === ws.OPEN)
                    ws.send(JSON.stringify({ message: 'server:modal' }));
            }
            else {
                this.respondToModal(false, info);
                this.logout();
            }
        }
        ws.active = true;
        let enrolleeId = null;
        const activeActivity = this.activities.filter(activity => activity.isActive);
        if (activeActivity.length) {
            enrolleeId = activeActivity[0].enrolleeId;
        }
        this.$emitter.emit(`server:enter:${this.providerId}`, this.patientId, this.patientFamilyId, enrolleeId);
    }
    /**
     * general logout
     */
    logout() {
        this.isLoggingOut = true;
        this.allSockets.forEach(socket => {
            if (socket.readyState === socket.OPEN) {
                socket.send(JSON.stringify({ message: 'server:logout' }));
            }
        });
    }
    /**
     * logout because of mouse and keyboard inactivity while on client page
     * removes about 90 seconds from the duration
     */
    clientInactivityLogout(info) {
        if (!this.isLoggingOut) {
            this.removeInactiveDuration(info);
        }
        this.logout();
    }
    /**
     * inform all clients to close their open inactivity-modal
     */
    closeAllModals() {
        this.allSockets.forEach(socket => {
            if (socket.readyState === socket.OPEN) {
                socket.send(JSON.stringify({ message: 'server:inactive-modal:close' }));
            }
        });
    }
    /**
     * to be executed on client:leave when the client page loses focus
     */
    leave(ws) {
        this.validateWebSocket(ws);
        ws.active = false;
    }
    /**
     * to be executed on ws:close when a WebSocket connection closes
     */
    exit(ws) {
        this.activities.forEach(activity => {
            const index = activity.sockets.findIndex(socket => socket === ws);
            if (index >= 0) {
                activity.sockets.splice(index, 1);
            }
        });
    }
    resetStartTimeAndDurationOnActivities() {
        this.activities.forEach(activity => {
            activity.duration = 0;
            activity.start_time = null;
        });
    }
    sync() {
        this.allSockets.forEach(ws => {
            let totalSeconds = 0;
            const secondsPerChargeableService = [];
            for (let i = 0; i < this.chargeableServices.length; i++) {
                const csId = this.chargeableServices[i].chargeable_service.id;
                const cached = this.getTotalSecondsForCsIdFromCache(csId);
                const notCached = this.getTotalSecondsForCsId(csId);
                const entry = {
                    chargeable_service_id: csId,
                    seconds: cached > notCached ? cached : notCached
                };
                secondsPerChargeableService.push(entry);
                totalSeconds += entry.seconds;
            }
            if (ws.readyState === ws.OPEN) {
                ws.send(JSON.stringify({
                    message: 'server:sync',
                    seconds: totalSeconds,
                    seconds_per_chargeable_service: secondsPerChargeableService,
                }));
            }
        });
    }
    handleNoModal(info) {
        let activity = this.findActivity(info);
        if (activity) {
            activity.duration += this.inactiveSeconds;
        }
        this.inactiveSeconds = 0;
    }
    /**
     *
     * @param {boolean} response yes/no on whether the practitioner was busy on a patient during calculated inactive-time
     * @param info
     */
    respondToModal(response, info) {
        let activity = this.findActivity(info);
        if (activity) {
            if (response) {
                activity.duration += this.inactiveSeconds;
            }
            else {
                activity.duration += 30;
            }
        }
        this.inactiveSeconds = 0;
    }
    showInactiveModal(info, now = () => (new Date())) {
        let activity = this.findActivity(info);
        if (activity) {
            activity.isInActiveModalShown = true;
            activity.inactiveModalShowTime = now().getTime();
        }
    }
    closeInactiveModal(info, response, now = () => (new Date())) {
        let activity = this.findActivity(info);
        if (activity && activity.inactiveModalShowTime) {
            activity.isInActiveModalShown = false;
            const elapsedSeconds = (new Date(now().getTime() - activity.inactiveModalShowTime)).getSeconds();
            if (response) {
                activity.duration += elapsedSeconds;
            }
            else {
                this.removeInactiveDuration(info);
            }
            activity.inactiveModalShowTime = null;
        }
    }
    removeInactiveDuration(info) {
        let activity = this.findActivity(info);
        if (activity) {
            //the user has been inactive,
            //therefore remove a default of seconds (depending whether call mode or not)
            //or assign 30 seconds. whichever is max.
            //so minimum is 30 seconds.
            const alertTimeout = this.callMode ? this.ALERT_TIMEOUT_CALL_MODE : this.ALERT_TIMEOUT;
            const removedAlertTimeout = activity.duration - (alertTimeout - this.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS);
            activity.duration = Math.max(removedAlertTimeout, this.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS);
            //activity.duration = Math.max((activity.duration - ((!this.callMode ? this.ALERT_TIMEOUT : this.ALERT_TIMEOUT_CALL_MODE) - this.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS)), this.MINIMUM_DURATION_AFTER_INACTIVITY_SECONDS)
        }
    }
    enterCallMode(info) {
        let activity = this.findActivity(info);
        if (activity) {
            activity.callMode = true;
        }
        this.broadcast({ message: 'server:call-mode:enter' });
        this.$emitter.on(`server:enter:${this.providerId}`, this.serverEnterHandler.bind(this));
    }
    exitCallMode() {
        this.activities.forEach(activity => {
            activity.callMode = false;
        });
        this.broadcast({ message: 'server:call-mode:exit' });
        this.$emitter.removeListener(`server:enter:${this.providerId}`, this.serverEnterHandler.bind(this));
    }
    close() {
        /**
         * to be executed when all sockets have closed
         * reset everything related to user.
         * see CPM-111 - CCM time isn't showing correctly for the nurse
         */
        this.inactiveSeconds = 0;
        this.resetTimeForChargeableServices();
        this.resetStartTimeAndDurationOnActivities();
        this.isLoggingOut = null;
    }
    report() {
        return {
            seconds: this.totalSeconds,
            startTime: this.totalTime,
            callMode: this.callMode,
            activities: this.activities.map(activity => ({
                name: activity.name,
                title: activity.title,
                duration: activity.duration,
                url: activity.url,
                url_short: activity.url_short,
                start_time: activity.start_time,
                chargeable_service_id: activity.chargeableServiceId
            })),
            key: this.key
        };
    }
    changeActivity(info, ws) {
        if (info.modify) {
            // we allow changing activity name using a filter
            const activity = this.findActivity({
                activity: info.modifyFilter,
                enrolleeId: info.enrolleeId,
                chargeableServiceId: info.chargeableServiceId,
                startTime: info.startTime
            });
            if (activity) {
                activity.name = info.activity;
                activity.enrolleeId = info.enrolleeId;
                activity.forceSkip = info.forceSkip;
            }
            return;
        }
        this.sendToCpm(false);
        this.addActivitiesDurationToTotalTime();
        this.activities = [];
        this.enter(info, ws);
    }
    sendToCpm(emitLogout) {
        const url = this.url;
        if (this.timeSyncUrl) {
            sync_with_cpm_1.ignorePatientTimeSync(this.timeSyncUrl, this.patientId);
        }
        const requestData = {
            patientId: this.patientId,
            providerId: this.providerId,
            ipAddr: this.ipAddr,
            programId: this.programId,
            activities: this.activities
                .filter(activity => activity.duration > 0)
                .map(activity => ({
                name: activity.name,
                title: activity.title,
                duration: activity.duration,
                enrolleeId: activity.enrolleeId,
                url: activity.url,
                url_short: activity.url_short,
                start_time: activity.start_time,
                end_time: utils_fn_1.formatTimeForServer(new Date()),
                force_skip: activity.forceSkip,
                chargeable_service_id: activity.chargeableServiceId
            }))
        };
        if (this.totalSeconds === 0) {
            console.log('will not cache ccm because time is 0');
        }
        else {
            const toCache = [];
            for (let i = 0; i < requestData.activities.length; i++) {
                const a = requestData.activities[i];
                if (a.chargeable_service_id === -1) {
                    continue;
                }
                const currentSeconds = this.getTotalSecondsForCsId(a.chargeable_service_id);
                const cachedSeconds = this.getTotalSecondsForCsIdFromCache(a.chargeable_service_id);
                if (cachedSeconds > currentSeconds) {
                    console.debug(`will not cache cs[${a.chargeable_service_id}] because cache is higher`);
                    continue;
                }
                toCache.push({
                    chargeable_service_id: a.chargeable_service_id,
                    time: currentSeconds
                });
            }
            if (toCache.length) {
                console.log('caching time', JSON.stringify(toCache));
                user_time_1.storeTime(this.key, requestData.activities, toCache);
            }
        }
        axios_1.default
            .post(url, requestData)
            .then((response) => {
            console.log(response.status, response.data, requestData.patientId, requestData.activities.map(activity => activity.duration).join(', '));
        })
            .catch((err) => {
            errorLogger.report(err);
            console.error(err);
        });
        if (emitLogout) {
            this.$emitter.emit('socket:server:logout', requestData);
        }
    }
}
exports.default = TimeTrackerUser;
//# sourceMappingURL=time-tracker.user.js.map