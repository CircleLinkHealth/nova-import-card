export default () => {
    if (window) {

        let loadTimeExcludeUnloadFromPreviousPage = 0;
        if (window.performance.timing.unloadEventEnd > 0) {
            loadTimeExcludeUnloadFromPreviousPage = Math.abs(window.performance.timing.navigationStart - window.performance.timing.unloadEventEnd);
        }

        return new Date() - window.performance.timing.navigationStart - loadTimeExcludeUnloadFromPreviousPage;
    }
    else {
        console.error('window is undefined')
        return 0
    }
}