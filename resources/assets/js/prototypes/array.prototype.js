Array.prototype.distinct = function (fn) {
    if (typeof (fn) == 'string') {
        var key = '' + fn;
        fn = function (a) { return a[key]; }
    }
    else fn = fn || function (a) { return a; }
    var arr = this;
    return arr.reduce(function (a, b) {
        return a.map(fn).indexOf(fn(b)) < 0 ? a.concat(b) : a;
    }, []);
}

export default Array