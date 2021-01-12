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
