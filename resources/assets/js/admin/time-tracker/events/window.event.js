/**
 * Detect window.onfocus and window.onblur
 */
export const BindWindowFocusChange = function (window, app) {
    window.onfocus = function () {
        app.$emit('tracker:start');
        app.$emit('tracker:reset');
        app.$emit('inactivity:start');
    }
    
    window.onblur = function () {
        console.log('leave')
        app.$emit('tracker:stop');
        app.$emit("inactivity:stop");
    }
    
    window.onkeydown = window.onmousemove = 
    window.onwheel = window.onmousewheel = 
    window.onmousedown = window.onkeyup = function () {
        app.$emit('tracker:reset');
    }
}

/**
 * Detect window visibility change
 */
export const BindWindowVisibilityChange = function(window, document, app) {
    var hidden = "hidden";

    // Standards:
    if (hidden in document)
        document.addEventListener("visibilitychange", onchange);
    else if ((hidden = "mozHidden") in document)
        document.addEventListener("mozvisibilitychange", onchange);
    else if ((hidden = "webkitHidden") in document)
        document.addEventListener("webkitvisibilitychange", onchange);
    else if ((hidden = "msHidden") in document)
        document.addEventListener("msvisibilitychange", onchange);
    // IE 9 and lower:
    else if ("onfocusin" in document)
        document.onfocusin = document.onfocusout = onchange;
    // All others:
    else
        window.onpageshow = window.onpagehide = window.onfocus = window.onblur = onchange;

    function onchange(evt) {
        var v = "visible",
            h = "hidden",
            evtMap = {
                focus: v,
                focusin: v,
                pageshow: v,
                blur: h,
                focusout: h,
                pagehide: h
            };

        evt = evt || window.event;
        
        const listener = (state) => {
            if (state === v) {
                app.$emit('tracker:start');
                app.$emit('tracker:reset');
                app.$emit('inactivity:start');
            }
            else {
                app.$emit('tracker:stop');
                app.$emit("inactivity:stop");
            }
            console.log(state);
        }

        if (evt.type in evtMap) {
            listener(evtMap[evt.type]);
            
        } else {
            listener(this[hidden] ? "hidden" : "visible");
        }
    }

    // set the initial state (but only if browser supports the Page Visibility API)
    if (document[hidden] !== undefined)
        onchange({
            type: document[hidden] ? "blur" : "focus"
        });
}