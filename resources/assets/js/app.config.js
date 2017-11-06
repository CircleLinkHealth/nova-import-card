export function rootUrl(url, nameVal) {
    const bases = Array.from(document.getElementsByTagName('base'));
    let baseElem = bases.filter(function (elem) { return (elem.getAttribute('name') == (nameVal || 'root')) })[0];
    if (!baseElem) baseElem = bases.filter(function (elem) { return !elem.getAttribute('name') })[0];
    if (baseElem) {
        var ret = baseElem.getAttribute('href');
        if (ret.charAt(ret.length - 1) != '/') ret += '/';
        ret += url;
        return ret;
    }
    else return '/' + url;
}

export function baseValue(name) {
    const bases = Array.from(document.getElementsByTagName('base'));
    let baseElem = bases.filter(function (elem) { return (elem.getAttribute('name') == (name || 'root')) })[0];
    if (!baseElem) baseElem = bases.filter(function (elem) { return !elem.getAttribute('name') })[0];
    if (baseElem) {
        return baseElem.getAttribute('value');
    }
    else return '';
}