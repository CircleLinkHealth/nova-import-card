export const distinct = function (fn) {
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

export const includes = function (item) {
    var arr = this;
    for (var i = 0; i < arr.length; i++) {
        if (arr[i] == item) return true;
    }
    return false;
}

export const find = function (fn) {
    fn = fn || function (a) { return a; }
    var arr = this;
    for (var i = 0; i < arr.length; i++) {
        if (fn(arr[i])) return arr[i];
    }
    return null;
}

export const findIndex = function (fn) {
    fn = fn || function (a) { return a; }
    var arr = this;
    for (var i = 0; i < arr.length; i++) {
        if (fn(arr[i])) return i;
    }
    return -1;
}

Array.prototype.distinct = distinct

if (!Array.prototype.includes) {
    Array.prototype.includes = includes
}

if (!Array.prototype.find) {
    Array.prototype.find = find
}

if (!Array.prototype.findIndex) {
    Array.prototype.findIndex = findIndex
}

export default Array