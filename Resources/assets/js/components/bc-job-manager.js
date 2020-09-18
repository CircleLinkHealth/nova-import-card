let jobTimer;
const jobs = [];
let jobSeed = 1;
const handlers = [];

const bc = new BroadcastChannel("cpm");
bc.onmessage = (msgEv) => {
    const msg = msgEv.data;
    if (!msg.jobId) {
        console.error("received invalid message");
        return;
    }

    //we received a request. it should be handled and send a response
    if (msg.isRequest) {
        const action = msg.action;
        const handler = handlers[action];
        if (!handler) {
            console.error(`received message[${action}] but could not be handled`);
            return;
        }

        const resp = getReturnMessage(msg);
        handler(msg)
            .then(data => {
                resp.data = data;
                bc.postMessage(resp);
            })
            .catch(err => {
                resp.error = {
                    code: 1,
                    message: err.message
                };
                bc.postMessage(resp);
            });
    }
    else {
        const job = getJob(msg.jobId);
        if (!job) {
            console.error("received message but could not associate with a job");
            return;
        }
        //this will resolve the promise
        job.onDone(msg);
    }
};

function getJob(id) {
    const job = jobs[id];
    if (job) {
        delete jobs[id];
    }
    return job;
}

function registerJob(msg, onDone) {
    const job = {
        id: msg.jobId,
        action: msg.action,
        startDate: msg.startDate,
        timeout: msg.timeout,
        onDone
    };
    jobs[job.id] = job;

    if (!jobTimer) {
        jobTimer = setTimeout(checkForExpiredJobs, 1000);
    }
}

function isExpired(job) {
    if (!job.startDate) {
        return true;
    }

    const now = Date.now();
    return now - job.startDate > job.timeout;
}

function checkForExpiredJobs() {

    for (let i in jobs) {

        if (!jobs.hasOwnProperty(i)) {
            continue;
        }

        const job = jobs[i];
        if (isExpired(job)) {
            job.onDone({
                isRequest: false,
                action: job.action,
                error: {
                    code: 1,
                    message: "timeout"
                },
                data: null,
                startDate: job.startDate,
                timeout: job.timeout,
                jobId: job.id
            });
            delete jobs[i];
        }
    }

    clearTimeout(jobTimer);
    jobTimer = null;

    if (jobs.length) {
        jobTimer = setTimeout(checkForExpiredJobs, 1000);
    }
}

function getReturnMessage(msg) {
    return {
        isRequest: false,
        action: msg.action,
        data: null,
        error: null,
        startDate: Date.now(),
        timeout: null,
        jobId: msg.jobId
    }
}

/**
 * use this function to register actions to handle
 * received from broadcast channel
 *
 * @param action {string} name of the action
 * @param handler {Function} should return the response data as a promise
 */
export function registerHandler(action, handler) {
    handlers[action] = handler;
}

/**
 * Send a message using the broadcast channel
 * The response will be resolved as a promise.
 * If the response is timed out or has error, the promise will be rejected.
 *
 * @param action {string}
 * @param data - the request data
 * @param timeoutMS - timeout in milliseconds
 * @returns {Promise<any>}
 */
export function sendRequest(action, data, timeoutMS) {
    if (!timeoutMS) {
        timeoutMS = 1000 * 10; //10 seconds
    }
    const req = {
        isRequest: true,
        action,
        data,
        startDate: Date.now(),
        timeout: timeoutMS,
        jobId: ++jobSeed
    };

    return new Promise((resolve, reject) => {
        const onDone = (resp) => {
            if (resp.error) {
                reject(resp);
            }
            else {
                resolve(resp);
            }
        };
        registerJob(req, onDone);
        bc.postMessage(req);
    });
}

