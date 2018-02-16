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

if (!Array.prototype.includes) {
    Array.prototype.includes = function (item) {
        var arr = this;
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] == item) return true;
        }
        return false;
    }
}

if (!Array.prototype.find) {
    Array.prototype.find = function (fn) {
        fn = fn || function (a) { return a; }
        var arr = this;
        for (var i = 0; i < arr.length; i++) {
            if (fn(arr[i])) return arr[i];
        }
        return null;
    }
}

if (!Array.prototype.findIndex) {
    Array.prototype.findIndex = function (fn) {
        fn = fn || function (a) { return a; }
        var arr = this;
        for (var i = 0; i < arr.length; i++) {
            if (fn(arr[i])) return i;
        }
        return -1;
    }
}

export default Array