# The `"info"` parameter

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

| Parameter | Meaning |
| ------ | ---:|
| `patientId` | Patient's ID |
| `providerId` | Current User's ID |
| `totalTime` | Total Current Monthly Time Tracked |
| `wsUrl` | The URL of the WS Server |
| `programId` | The ID of the practice the practitioner is in |
| `urlShort` and `urlFull` | Short and Full URLs of the current page being tracked |
| `ipAddr` | IP Address of the Client |
| `activity` | Description of the Current Page |
| `title` | Location of current blade php view |
| `submitUrl` | The location of API to submit the time-track log to |
| `startTime` | The Time when the Tracking started (8 seconds after server page render) |