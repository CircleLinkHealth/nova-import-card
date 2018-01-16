## Resource: Nurse


#### Important notes!
The term `Nurse` refers to a `NurseInfo` model with a relationship with `User`

**All Nurse endpoints return a collection of, or single `App\Http\Resources\NurseInfo`**

**Query String Filters are chainable:** 

Example of a request for 3 different filters _canCallPatient_, _states_, and _windows_: 

`GET /api/nurses?canCallPatient=1234&states&windows`
 
<br>

### Endpoints

##### `GET /api/nurses` - Get all Nurses
<br> relationships: `users`  

<br>

##### `GET /api/nurses?canCallPatient={user_id}`
Get Nurses with CredentialedState and ContactWindows, that can call the patient with `user_id` specified in the url.
<br> relationships: `users`, `nurse_contact_window`, `nurse_info_state`, `holidays` 

<br>

##### `GET /api/nurses?states` - Get all Nurses with CredentialedState.
###### `GET /api/nurses?states=NJ,NY` - Get all Nurses licenced both in NJ and NY. 
###### `GET /api/nurses?statesOr=CA,TX` - Get all Nurses licenced in CA or TX.
relationships: `users`, `nurse_info_state`

<br>

##### `GET /api/nurses?windows`
Get all Nurses with ContactWindows. 
<br> relationships: `users`, `nurse_contact_window`, `nurse_info_state`, `holidays` 
