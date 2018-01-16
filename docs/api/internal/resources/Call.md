## Resource: Call

**All Query String Filters return an instance of `App\Http\Resources\Call`**

**Query String Filters can be found at `App\Filters\CallFilters`**

### Endpoints

##### `GET /api/admin/calls?scheduled` - Get all Scheduled Calls for all Practices.
Only Administrators can perform this action.

<br>

##### `GET /api/admin/calls?caller={search_term}` - Get all Calls assigned to callers whose name contains the search term.
###### `GET /api/admin/calls?scheduled&caller=Sue` - Get all Scheduled Calls assigned to callers whose name contains the search term 'Sue'.
Only Administrators can perform this action.

<br>

##### `DELETE /api/admin/calls/destroy/{id},{id},{id}` - Delete a comma delimited list of Calls.
Only Administrators can perform this action.

