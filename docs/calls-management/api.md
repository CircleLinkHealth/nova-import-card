# Calls Management API

This page describes the internal api endpoints for managing outgoing patient calls. Use this page to see available endpoints, and the tables they return info from. 
<br>**All endpoints return json.**

### base uri: `/api`
#### production base uri: `https://careplanmanager.com/api`

## Nurses

The term `Nurse` refers to a `NurseInfo` model with a relationship with `User`

**All nurse endpoints return a collection of, or single `App\Http\Resources\NurseInfo`**

<br>

##### `GET /api/nurses` - Get all Nurses
base table: `nurse_info` 
<br> relationships: `users`  

<br>

### Nurse Query String Filters

Filters are chainable. This is an example of a request for 3 different filters _canCallPatient_, _states_, and _windows_: `GET /api/nurses?canCallPatient=1234&states&windows`
<br> base table: `nurse_info`

#### `GET /api/nurses?canCallPatient={user_id}`
Get Nurses with CredentialedState and ContactWindows, that can call the patient with `user_id` specified in the url.
<br> relationships: `users`, `nurse_contact_window`, `nurse_info_state`, `holidays` 

#### `GET /api/nurses?states` - Get all Nurses with CredentialedState.
#### `GET /api/nurses?states=NJ,NY` - Get all Nurses licenced both in NJ and NY. 
#### `GET /api/nurses?statesOr=CA,TX` - Get all Nurses licenced in CA or TX.
<br> relationships: `users`, `nurse_info_state`

#### `GET /api/nurses?windows`
Get all Nurses with ContactWindows. 
<br> relationships: `users`, `nurse_contact_window`, `nurse_info_state`, `holidays` 
