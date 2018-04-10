function WebSocket() {
    this.send = function (data) {
        console.log('ws:send', data)
    }
    this.active = false
}

module.exports = WebSocket