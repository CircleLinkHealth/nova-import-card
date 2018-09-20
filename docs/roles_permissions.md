# Roles/Permissions

#### Package
We maintain and use [Laravel Cerberus](https://github.com/circlelinkhealth/laravel-cerberus).


## General Concepts
- Key models are **Role** and **Permission**.
- `Permissions` can be **directly related** to any model. (see [Many to Many Polymorphic Relationship](https://laravel.com/docs/5.5/eloquent-relationships#many-to-many-polymorphic-relations), and `permissibles` table).
At the time this doc is written, we're associating `Permissions` with `Roles` and `Users`. 
- `Roles` have the **same** `Permissions` for **all** `Practices`.
- A `User` can have many `Roles` for a `Practice`, and that is stored in table `practice_role_user`.
- The `User` will have all `Permissions` from all `Roles` assigned to them for a `Practice`.
- Column `permissibles.is_active` can be used to enable/disable `Permissions` for a `Model`, depending on whether it's set to true or false.
  

##### Important Roles 
- `administrator` => Admins can see and do it all. Only CLH employees can have this Role. 
- `care-center` => Care Coaches (Nurses) who work for CLH. They place calls to patients (participants) regularly. CCM countable.
- `provider` => Doctors at a Practice.
- `care-ambassador` => People hired by CLH to call patients and enroll them to CarePlan Manager.
- `office_admin` => Non medical staff that work at a Practice. Not CCM countable. 
- `med_assistant` => Medical staff that work at a Practice, and are not Doctors or Registered Nurses. CCM Countable. 

##### View Only Roles
These `Roles` can only see data, but not make changes to it.
- `administrator-view-only` 
- `care-ambassador-view-only` 
- `saas-admin-view-only`


#### Types of Permissions:
- `CRUD Permissions` (Create, Read, Update, Delete) => associated with `Models`. 
   
   **Examples**: `note.create`, `note.read`, `note.update` and `note.delete`.

- `Domain Permissions` are used for specific business logic related actions. 
 
    **Examples:** `care-plan-approve`, `care-plan-qa-approve`, `note.send`, and `legacy-bhi-consent-decision.create`.

## Workflow
#### Managing Permissions
- Permissions are handled in `database/seeds/RequiredPermissionsTableSeeder.php`

    * To create `CRUD Permissions` for a model, add the model's name to method `resources()`. Method `crudPermission()`, will create `CRUD Permissions` for each `Model`, using template "Model.CRUD Permission".
    
    * `Domain Permissions` are created in method `domainPermissions()`.

- Prefer to **check permissions in route files** (*web.php, api.php, console.php*), to decouple access control from application logic.

#### Applying Roles/Permissions changes to the DB
- Run `php artisan make:rpm`. This will create a **new migration**, which will run `database/seeds/RequiredRolesPermissionsSeeder.php`.
- Run `php artisan migrate`



#### Extras

- To generate Roles-Permissions CSV:  
https://careplanmanager.com/admin/roles-permissions 
- To generate Routes-Permissions CSV: https://careplanmanager.com/admin/routes-permissions

