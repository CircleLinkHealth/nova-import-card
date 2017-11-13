# Time-Tracker Web Sockets (WS) Server

A NodeJS app which hosts a web socket server at `::/time`, that manages time-tracking for CLH users.

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

```

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