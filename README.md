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
|  [message](./docs/events.params.md)  | The `message` key tells the ws server what action to perform |
|  [info](./docs/info.params.md)  | The `"info"` value contains other details about the current session |

## Key Structure

The user is identified a key which is a concatenation of the `"id"` (practitionerId) and `"patientId"` values, so you should specify them correctly.

## Related Documents

- [Definition of Terms](./docs/definitions.md)
- [Functional Requirements](./docs/functional-requirements.md)
- [User Story](./docs/user-story.md)
- [The "info" parameter](./docs/info.params.md)
- [The "message" parameter / Events](./docs/events.params.md)