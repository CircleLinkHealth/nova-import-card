export default () => {
    if (window) {
        //from the moment request was made to the server
        return new Date() - (window.performance.timing.connectStart);
    }
    else {
        console.error('window is undefined')
        return 0
    }
}