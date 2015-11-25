(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

module.exports = function (input) {
    if (typeof input.given == 'undefined') {
        return "John Doe";
    }
    if (input.given === null) {
        if (input.family === null) {
            return "Unknown";
        } else {
            return input.family;
        }
    }
    var name,
        first_given,
        other_given,
        names = input.given.slice(0);
    var prefix = input.prefix === null ? '' : input.prefix;
    var suffix = input.suffix === null ? '' : input.suffix;
    if (names instanceof Array) {
        first_given = names.splice(0, 1);
        other_given = names.join(" ");
    } else {
        first_given = names;
    }
    name = first_given;
    name = input.call_me ? name + " \"" + input.call_me + "\"" : name;
    name = other_given ? name + " " + other_given : name;
    name = prefix + " " + name + " " + input.family + " " + suffix;
    return name;
};

},{}]},{},[1]);

//# sourceMappingURL=demographics.js.map
