function addPadding(v) {
    return v.toString().length < 2 ? `0${v}` : v;
}

//four digit year
function getYear(d) {
    return d.getFullYear();
}

//getMonth: 0 - 11
function getMonth(d) {
    return addPadding(d.getMonth() + 1);
}

//getDate: 1 - 31
function getDate(d) {
    return addPadding(d.getDate());
}

//getHours: 0 - 23
function getHours(d) {
    return addPadding(d.getHours());
}

//getMinutes: 0 - 59
function getMinutes(d) {
    return addPadding(d.getMinutes());
}

//getSeconds: 0 - 59
function getSeconds(d) {
    return addPadding(d.getSeconds());
}

function getTime(d) {
    return `${getHours(d)}:${getMinutes(d)}:${getSeconds(d)}`;
}

/**
 * Take a date object in javascript,
 * convert to server timezone,
 * and return a string that matches Carbon::toDateTimeString()
 *
 * @param date
 * @param serverTimezone
 * @returns {string}
 */
function getCarbonDateTimeStringInServerTimezone(date, serverTimezone) {
    //1.take local time and make sure it matches the server timezon
    const serverDateTimeStr = date.toLocaleString("en-US", {timeZone: serverTimezone});
    //2.create a new date time object from the string produced
    const serverDateTimeObj = new Date(serverDateTimeStr);
    //3.format this date time obj to match Carbon::toDateTimeString()
    return `${getYear(serverDateTimeObj)}-${getMonth(serverDateTimeObj)}-${getDate(serverDateTimeObj)} ${getTime(serverDateTimeObj)}`;
}
