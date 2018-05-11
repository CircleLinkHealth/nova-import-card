/**
 * 
 * @param {*} opts 
 */
const DocumentMock = function (opts = {}) {
    this.readyState = opts.readyState || 'loading'

    this.events = {}

    this.addEventListener = function (name, fn) {
        if (typeof this.events[name] !== 'object') {
            this.events[name] = []
        }

        this.events[name].push(fn)
    }

    this.removeListener = function (name, fn) {
        let idx

        if (typeof this.events[name] === 'object') {
            idx = this.events[name].indexOf(fn)

            if (idx > -1) {
                this.events[name].splice(idx, 1)
            }
        }
    }

    this.emit = function (name) {
        var i, listeners, length, args = [].slice.call(arguments, 1);

        if (typeof this.events[name] === 'object') {
            listeners = this.events[name].slice()
            length = listeners.length

            for (i = 0; i < length; i++) {
                listeners[i].apply(self, args)
            }
        }
    }

    setTimeout(function () {
        this.emit('DOMContentLoaded')
    }, opts.loadTimeout || 100)
}

export default DocumentMock