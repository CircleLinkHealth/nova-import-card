## Resource: Call

**All Query String Filters return an instance of `App\Http\Resources\Call`**

**Query String Filters are chainable and can be found at `App\Filters\ScheduledCallFilters`**


<br>Example of how to chain filters: 
<br>`GET /api/admin/calls?scheduled&caller=Sue` 
<br>Returns all Scheduled Calls assigned to callers whose name contains the search term 'Sue'.


### Endpoints

##### `GET /api/admin/calls?scheduled` - Get all Scheduled Calls for all Practices.
Only Administrators can perform this action.

<br>

##### `GET /api/admin/calls?scheduledDate=2018-01-24` - Filter Calls by date scheduled.
The date must be in format "YYYY-MM-DD"
Only Administrators can perform this action.

<br>

##### `GET /api/admin/calls?lastCallDate=2018-01-24` - Filter Calls by the date the patient was attempted to be called last.
The date must be in format "YYYY-MM-DD".
Only Administrators can perform this action. 

<br>

##### `GET /api/admin/calls?caller={search_term}` - Get all Calls assigned to callers whose name contains the search term.
Only Administrators can perform this action.
<br>

<br>

##### `GET /api/admin/calls?patientId={patient_user_id}` - Get all Calls for the patient using a user_id.
Only Administrators can perform this action.

<br>

##### `DELETE /api/admin/calls/destroy/{id},{id},{id}` - Delete a comma delimited list of Calls.
Only Administrators can perform this action.

