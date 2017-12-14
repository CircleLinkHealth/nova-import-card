#Concepts

##Roles
There are a few different kinds of users that consume, and/or are consumed by the application. Each user will have a `User` model. Depending on the `User`'s `Role`,  
the `User` may also have additional relationships with other Models. The most frequently used `Roles` of the application are 
    
    - Administrator => Only people who work for CLH.
    - Provider => Someone who is licensed to offer care (eg. A `Medical Doctor`). 
    - Patient => A patient.
    - CareCenter => CLH's Registerred Nurse.
     

#####Important Relationships
- `App\User` hasMany `App\Roles`

    Every `User` in the application needs to have a `Role`, associated with a `Practice`. 
    Roles will be saved on table `practice_role_user`. All available `Roles` can be found on table `lv_roles`.

    We use [this package](https://github.com/michalisantoniou6/laravel-cerberus) to manage roles. You may review/contribute the package's documentation to learn more about Roles and Permissions.  

- `App\User` hasOne `App\Patient`
    
    Patients don't have login access to CPM, but they are the application's main concert.  
    
    A patient will have `practice_role_user:role_id = 2`, which is the id of role `participant`, which is used for patients. The `User` will also have one `Patient` model, which is responsible for domain logic that concerns patients only. Common functionality should go on the user model.

- `App\User` hasOne `App\Nurse`

- `App\User` hasOne `App\ProviderInfo`

- `App\User` hasOne `App\Practice`

- `App\User` hasMany `App\Practice`