import DocumentMock from './document.mock'

/**
 * 
 * @param {object} opts.querySelectorMap
 * @param {array} opts.elements
 * @param {object} opts.attributes
 * @param {object} opts.props
 * @param {string} opts.tagName
 */
const ElementMock = function (opts = {}) {
    this.querySelectorMap = opts.querySelectorMap || ({})
    this.elements = opts.elements || ([])
    this.attributes = opts.attributes || ({})
    this.props = opts.props || ({})
    this.tagName = opts.tagName || 'element'

    Object.assign(this, new DocumentMock(opts))

    this.events = {}

    this.getAttribute = function (name) {
        return this.attributes[name]
    }
}

export default ElementMock