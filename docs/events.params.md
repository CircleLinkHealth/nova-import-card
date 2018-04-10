# Messages / Events

The `message` key tells the ws server what action to perform and can take any of the following values:

## client:start

When a client navigates to a CLH-providerUI page, the client sends a **client:start** event to the wss. This event contains the info:

```js
{
  message: "client:start",
  info: { ... } /**timeTrackerInfo**/
}
```

If this is the first connection in the session, the wss creates a tracker for this.

## client:leave

When a client's focus leaves a CLH-providerUI page, the client sends a **client:leave** event to the wss. This contains the same info as above, but with the **message** field as "client:leave".

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **false**.

## client:enter

When a client focuses on a CLH-providerUI page, the client sends a **client:enter** event to the wss. This contains the same info as above, but with the **message** field as "client:enter"

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **true**.

The wss also sends a *server:sync* to all clients, to correct their time.

## server:sync

The **server:sync** event is sent from wss to client to correct the livecount time of the client which might be out of sync with the server, because of the known inaccuracies of **setInterval**

## server:modal

The server instructs the client to show the modal popup, asking the user if they were inactive because of a patient or not. 

- If the user clicks yes, the client sends **client:modal:yes** to the wss
- If the user clicks no, the client sends **client:modal:no** to the wss

## client:modal:yes

(See above). When received by the wss, it adds the inactiveTime to the activity duration. The inactiveTime is set to 0.

## client:modal:no

(See above). When received by the wss, it only adds 30 seconds to the activity duration, and the inactiveTime is set to 0