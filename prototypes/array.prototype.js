Array.prototype.paginate = function (length) {
    var arr = this;
    length = (length || 1);
    return arr.reduce(function (a, b) {
        if (!a[a.length - 1] || a[a.length - 1].length == length) a.push([]);
        a[a.length - 1].push(b);
        return a;
    }, []);
}

Array.prototype.flatten = function () {
    var arr = this;
    return arr.reduce(function (a, b) {
        return a.concat(b);
    }, []);
}

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

Array.prototype.toggle = function (item) {
    var arr = this.valueOf();
    if (arr.indexOf(item) >= 0) arr.splice(arr.indexOf(item), 1);
    else arr.push(item);
    return arr;
}

Array.prototype.sum = function (fn) {
    var arr = this.valueOf();
    fn = fn || function (a) { return a;}
    return arr.reduce(function (a, b) {
        return a + fn(b);
    }, 0);
}

Array.prototype.any = function (fn) {
    fn = fn || function (a) { return a; }
    var arr = this.valueOf();
    for (var i = 0; i < arr.length; i++) {
        if (fn(arr[i])) return true;
    }
    return false;
}

Array.prototype.where = function (key, value) {
    var arr = this;
    if (typeof(key) == "function") {
        return arr.filter(key);
    }
    else if (typeof(key) == "string") {
        return arr.filter(function (item) {
            return item[key] == value;
        });
    }
    else {
        throw new Error("unrecognizable arguments ", key, value)
    }
}