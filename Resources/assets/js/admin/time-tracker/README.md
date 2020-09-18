# Time-Tracker

The Time-Tracker App is a Vue app that keeps track of the time a User spends on any of the providerUI pages. This exists, becauses the user here is usually a nurse or a doctor, and we use this time to know how much pay the user gets.

## How it works

The component works via a system of multiple events.

- It connects to a web socket (ws) server
- The time between `window.onfocus` and `window.onblur` events is tracked by making requests to the ws server
- The `visibilitychange` event is tracked to cover for the flaws of the `blur` and `focus` events.
- To ensure the user doesn't just leave the page running and rack up time, we track inactivity.
  - This means events like: 

    - `window.onkeydown`
    - `window.onmousemove`
    - `window.onwheel`
    - `window.onmousewheel`
    - `window.onmousedown`
    - `window.onkeyup` 
    
    are tracked to calculate inactivity periods and make sure the server doesn't track those periods.

## Components

A number of components are used in this App.

### Time Display



### Inactivity Modal

### Inactivity Tracker