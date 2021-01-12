declare var process;

/**
 * Restart process every night at 2am
 */
export function setup() {
    const THRESHOLD_INTERVAL_SECONDS = 60 * 2; //2 minutes
    const HOURS = 2;
    const MINUTES = 0;
    setInterval(function () {

        const upTimeSeconds = Math.floor(process.uptime());

        if (upTimeSeconds < THRESHOLD_INTERVAL_SECONDS) {
            // uptime less than 2 minutes.
            // most probably process was just restarted
            // console.debug("Uptime is ", upTimeSeconds, "seconds. Exiting.");
            return;
        }

        const dateNow = new Date();
        const hours = dateNow.getHours();
        const minutes = dateNow.getMinutes();
        // console.debug('Hours are now', hours, 'and minutes', minutes);

        if (hours === HOURS && minutes === MINUTES) {
            // console.debug('Exiting. Please restart me PM.');
            process.exit(0);
        }

    }, 1000 * 60);
}
