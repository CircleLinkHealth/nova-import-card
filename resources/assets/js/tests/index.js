/**
 * 
 * @param {setup scripts runs before all tests} param0 
 */

const createElem = ({
    name,
    props,
    attrs,
    target
}) => {
    attrs = attrs || ({})
    props = props || ({})
    if (name) {
        const elem = document.createElement(name)
        Object.keys(attrs).map(key => {
            elem.setAttribute(key, attrs[key])
        })
        Object.keys(props).map(key => {
            Object.defineProperty(elem, key, {
                value: props[key],
                configurable: true
            })
        })
        if (target) {
            target.appendChild(elem)
        }
        return elem
    }
}

const createBaseElem = (attrs = {}) => {
    return createElem({
        name: 'base',
        attrs,
        target: document.head
    })
}

createBaseElem({
    name: 'root',
    href: '/'
})

createBaseElem({
    name: 'custom',
    href: '/custom/',
    value: 'custom'
})

createElem({
    name: 'meta',
    attrs: {
        name: 'csrf-token',
        content: 'SAMPLE-CSRF-TOKEN'
    },
    target: document.head
})