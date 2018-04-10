# Time-Tracker Web Sockets (WS) Server

A NodeJS app which hosts a web socket server at `::/time`, that manages time-tracking for CLH users.

## How to use

Clone the Repository:

```bash
git clone https://github.com/CircleLinkHealth/time-tracker.git
```

Navigate to the Directory

```bash
    cd time-tracker
```

Install NPM Dependencies

```bash
npm install
```

Start the Node App at `http://localhost:3000`

```bash
npm start
```

## Available Endpoints

| Endpoint | Type | What it does |
| ------ |:---:| ----:|
|  ~/  | GET | Shows swagger UI |
|  ~/keys  | GET |Returns a list of practitionerId-patientId keys currently active |
|  ~/:practitionerId/:patientId  | GET | Returns a practitioner-patient time activities |
|  ~/:practitionerId/:patientId  | PUT | Modifies latent properties in practiioner-patient time activities, such as adding to startTime |

## WS Structure

The ws server exists at `http://localhost:3000/time`, and messages are usually a stringified version of this format:

```json
{
    "id": "0",
    "patientId": "0",
    "message": "...",
    "info": { ... }
}
```

| Parameter | Meaning|
| ------ | ----:|
|  id  | The practitionerId of the Nurse or Doctor making use of the page which must be set |
|  patientId  | The id of the patient being administered to. Default value is `"0"`. `NULL` or `UNDEFINED` will not be accepted |
|  message  | The `message` key tells the ws server what action to perform |
|  info  | The `"info"` value contains other details about the current session |

### The `"info"` parameter

The `"info"` value contains more information about the current session and takes the following structure:

```json
{
  "patientId": "344",
  "providerId": "3864",
  "totalTime": 2820,
  "wsUrl": "ws://localhost:3000/time",
  "programId": "8",
  "urlFull": "https://cpm-web.dev/manage-patients/344/notes",
  "urlShort": "/manage-patients/344/notes",
  "ipAddr": "127.0.0.1",
  "activity": "Notes/Offline Activities Review",
  "title": "patient.note.index",
  "submitUrl": "https://cpm-web.dev/api/v2.1/pagetimer",
  "startTime": "2017-11-13 05:52:09"
}
```

Where:

`patientId` => Patient's ID

`providerId` => Current User's ID

`totalTime` => Total Current Monthly Time Tracked

`wsUrl` => The URL of the WS Server

`programId` => Not sure what this is 😋

`urlShort` and `urlFull` => Short and Full URLs of the current page being tracked

`ipAddr` => IP Address of the Client

`activity` => Description of the Current Page

`title` => Location of current blade php view

`submitUrl` => The location of API to submit the time-track log to

`startTime` => The Time when the Tracking started (8 seconds after server page render)

## Key Structure

The user is identified a key which is a concatenation of the `"id"` (practitionerId) and `"patientId"` values, so you should specify them correctly.

## Messages / Events

The `message` key tells the ws server what action to perform and can take any of the following values:

- client:start

When a client navigates to a CLH-providerUI page, the client sends a **client:start** event to the wss. This event contains the info:

```json
{
  message: "client:start",
  info: { ... } /**timeTrackerInfo**/
}
```

If this is the first connection in the session, the wss creates a tracker for this.

- client:leave

When a client's focus leaves a CLH-providerUI page, the client sends a **client:leave** event to the wss. This contains the same info as above, but with the **message** field as "client:leave".

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **false**.

- client:enter

When a client focuses on a CLH-providerUI page, the client sends a **client:enter** event to the wss. This contains the same info as above, but with the **message** field as "client:enter"

When the wss detects this, it sets the **active** variable on the web socket instance for that connection to **true**.

The wss also sends a *server:sync* to all clients, to correct their time.

- server:sync

The **server:sync** event is sent from wss to client to correct the livecount time of the client which might be out of sync with the server, because of the known inaccuracies of **setInterval**

- server:modal

The server instructs the client to show the modal popup, asking the user if they were inactive because of a patient or not. 

- If the user clicks yes, the client sends **client:modal:yes** to the wss
- If the user clicks no, the client sends **client:modal:no** to the wss

- client:modal:yes

(See above). When received by the wss, it adds the inactiveTime to the activity duration. The inactiveTime is set to 0.

- client:modal:no

(See above). When received by the wss, it only adds 30 seconds to the activity duration, and the inactiveTime is set to 0

## Related Documents

- [Functional Requirements](./docs/functional-requirements.md)
- [User Story](./docs/user-story.md)