# Calls Management API

This page describes the internal api endpoints for managing outgoing patient calls. Use this page to see available endpoints, and the tables they return info from. All endpoints return json.

### base uri: `/api`
#### production base uri: `https://careplanmanager.com/api`

## Nurses

The term `Nurse` refers to a `NurseInfo` model with a relationship with `User`

**All nurse endpoints return a collection of, or single json representation of `App\Http\Resources\NurseInfo`**

<br>

##### `GET /api/nurses` - Get all Nurses
base table: `nurse_info` 
<br> relationships: `users`  

<br>

### Nurse Filters
#### `GET /api/nurses?canCallPatient={user_id}`
Get Nurses with CredentialedState and ContactWindows 
<br> base table: `nurse_info`
<br> relationships: `users`, `nurse_contact_window`, `nurse_info_state`, `holidays` 
