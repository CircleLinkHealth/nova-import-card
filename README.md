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

or if you use Nodemon,

```bash
nodemon app.js
```

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

### `"id"`

The `"id"` value is the user id of the Nurse or Doctor making use of the page which must be set. 

### `"patientId"`

The `"patientId"` value is the id of the patient the Nurse or Doctor is administering currently, which may or may not be set.

If the `patientId` field is not set, be sure to give it a default value of `"0"`. Note that `null` or `undefined` values for the `patientId` field will NOT be accepted.

The user is identified by a concatenation of the `"id"` and `"patientId"` values, so you should specify them correctly.

### `"message"`

The `message` key tells the ws server what action to perform and can take any of the following values:

- start

[Start] is usually triggered to tell the server to start tracking time when the user focuses on the page

- stop

[Stop] is triggered to tell the server to stop tracking time when the user performs an action like leaving the page

- update

[Update] is triggered to create or modify the tracking information which is given in the `"info"` value

### `"info"`

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
