export function rootUrl(url, nameVal) {
    const bases = Array.from(document.getElementsByTagName('base'));
    let baseElem = bases.filter(function (elem) { return (elem.getAttribute('name') == (nameVal || 'root')) })[0];
    if (!baseElem) baseElem = bases.filter(function (elem) { return !elem.getAttribute('name') })[0];
    if (!baseElem) baseElem = document.querySelector('meta[name="base-url"]');
    if (baseElem) {
        let ret = baseElem.getAttribute('href') || baseElem.getAttribute('content');

        //add the slash
        if (ret.charAt(ret.length - 1) !== '/') ret += '/';

        //make sure the slash is not at the url
        if (url.charAt(0) === '/') url = url.substring(1);

        ret += url;
        return ret;
    }
    else return url.charAt(0) === "/" ? url : ('/' + url);
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

export function csrfToken() {
    let metaElem = document.querySelector('meta[name="csrf-token"]');
    if (metaElem) return metaElem.getAttribute('content');
    return null;
}