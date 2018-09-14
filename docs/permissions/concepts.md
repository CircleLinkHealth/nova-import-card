# User Roles and Permissions

#### Laravel Cerberus
The package we use for our User Permissions is:  https://github.com/michalisantoniou6/laravel-cerberus.


## Current Permission Implementation

#### Roles
We have **Roles** and **Permissions**, both of which are currently used to determine which parts of the web application a `User` can see.
There are currently 21 Roles in the system.

##### Important Roles
- `admin` 
- `care-center` (display_name => 'Care Coach')
- `provider`
- `care-ambassador`

##### View Only Roles
- `administrator-view-only` 
- `care-ambassador-view-only` 
- `saas-admin-view-only`

#### User - Role - Permission Relationship
Users, Roles and Permissions share a Many to Many Polymorphic Relationship. The `permissibles` table also contains the pivot column: `is_active`.
- Users can have **one** Role per Practice. 
- All Roles have the **same** Permissions for **all** Practices.
- Roles have **many** permissions.
- Users may have permissions associated **directly** to them. 
These are permissions that do not belong to the User's Roles, or permissions that do belong to the User's Roles but have been set to inactive (`is_active` = 0).




#### Permissions:
The current Permission system uses `Entities` and custom Permissions. 

- `Entities` can be models, collections of models or broader terms based on our business logic. 
For example, `patientProblem` is used for all routes that deal with the models `CPM Problem` or `CCD Problem`, while permission `practiceInvoice` encompasses all the data that are used in the creation of a **Practice Invoice**. 
- There are four Permissions for each Entity: `create`, `read`, `update`, `delete` (CRUD). 
For example, `note.create`, `note.read`, `note.update` and `note.delete`.

- Custom Permissions are permissions that cover specific use-cases, e.g. `legacy-bhi-consent-decision.create`, or permissions kept from the previous system for the same reason.

#### Implementation

The **current** implementation of this system uses a more granular approach in checking which content a `User` can see. 
There are 51 `Entities` (204 CRUD permissions), and 10 custom permissions. 

Permission checks are, for the most part, applied in the `web.php` file. *All* routes that allow the `User` to see or interact with any *important data*, are protected by permission checks.

This allows us to keep most of our permissions in a *focused* point in the code, while also allowing us to check what kind of data each route returns in detail.

There are also some limited cases of Roles and/or Permissions checks in some controller methods, or views.


## Workflow
#### Creating, Updating, and Deleting Permissions
To **create/delete/update Roles/Permissions**, make changes to `RequiredRolesPermissionsSeeder` and `RequiredPermissionsTableSeeder`.

Permissions directly related to Users must be added again.

After making changes run `php artisan make:rpm`. This will create a new migration to run the seeder again when we deploy.



####  Tips

- To generate Roles-Permissions CSV:  
https://careplanmanager.com/admin/roles-permissions 
- To generate Routes-Permissions CSV: https://careplanmanager.com/admin/routes-permissions

