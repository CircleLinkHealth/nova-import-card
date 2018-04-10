# Definitions

A **session** is a series of connections between a CLH user and our web-sockets server, from the time a tab navigates to a CLH-providerUI page, to the time all tabs on those pages are closed.

The **wss** is our web sockets server

The **client** is any of the tabs on the browser 

An **event** is a string message that was sent between the client and server to trigger some action. Each event comes with a payload, which contains data that is used to identify the sender, and/or in executing the action the event is meant for.

## Tracker Instance

This is an object that is maintained on the wss, containing information about the session. It looks like:

```js
{
   key: "{patient-id}-{provider-id}",
   inactiveTime: 0, //inactiveTime in seconds
   activities: [
      {
         name: "", 
         title: "",
         urlFull: "", 
         urlShort: "",
         sockets: [ ... ] //contains web sockets for pages that contain this activity
      },
      { ... }
   ]
}
```

## Events

### client:start

When a client navigates to a CLH-providerUI page, the client sends a **client:start** event to the wss. This event contains the info:

```js
{
  message: "client:start",
  info: { ... } /**timeTrackerInfo**/
}
```

If this is the first connection in the session, the wss creates a tracker for this.

### client:leave

When a client's focus leaves a CLH-providerUI page, the client sends a **client:leave** event to the wss. This contains the same info as above, but with the **message** field as "client:leave".

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **false**.

### client:enter

When a client focuses on a CLH-providerUI page, the client sends a **client:enter** event to the wss. This contains the same info as above, but with the **message** field as "client:enter"

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **true**. 

The wss also sends a *server:sync* to all clients, to correct their time.

#### server:sync

The **server:sync** event is sent from wss to client to correct the livecount time of the client which might be out of sync with the server, because of the known inaccuracies of **setInterval**

### server:modal

The server instructs the client to show the modal popup, asking the user if they were inactive because of a patient or not. 

- If the user clicks yes, the client sends **client:modal:yes** to the wss
- If the user clicks no, the client sends **client:modal:no** to the wss

#### client:modal:yes

(See above). When received by the wss, it adds the inactiveTime to the activity duration. The inactiveTime is set to 0.

#### client:modal:no

(See above). When received by the wss, it only adds 30 seconds to the activity duration, and the inactiveTime is set to 0

## Clock

The clock is implemented with a **setInterval** function that runs every second on the server. It loops through all activities on the **tracker instance**, and checks if any has a socket with its **active** property set to **true**. If an activity does, it adds +1 to the **duration** on that **activity**.

### Inactive Time (server)

During the clock loops, if there are sockets, but no socket is found in any activity its **active** property set to **true**, then the user can be said to be inactive. The clock then increments the **inactiveTime** on the tracker instance by +1

#### End Inactive Time (server)

On the **client:enter** event, the wss knows that the user has focused on a page, so it checks the value of **inactiveTime** using the following pseudocode:

```bash
if inactiveTime < 120 
  add inactiveTime to activity duration
  set inactiveTime to 0
else if inactiveTime < 600
  trigger modal
else
  logout user
```