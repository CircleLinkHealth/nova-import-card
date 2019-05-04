export default () => {
    if (window) {

        //from the moment request was made to the server
        return new Date() - (window.performance.timing.connectStart);

        /*
        const time = window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart
        if (time >= 0) return time
        else {
            console.error('window has not fully loaded')
            return (new Date()) - window.performance.timing.navigationStart
        }
        */
    }
    else {
        console.error('window is undefined')
        return 0
    }
}