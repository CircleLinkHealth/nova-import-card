export default function (cb, document) {
    document = document || window.document
    if (document.readyState !== 'loading') {
        this.$nextTick(() => {
            if (typeof (cb) == 'function') cb()
        })
    } else {
        document.addEventListener('DOMContentLoaded', () => cb())
    }
}
