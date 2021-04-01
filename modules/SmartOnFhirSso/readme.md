# Smart On Fhir SSO

## Features
Provides a framework to integrate SSO with applications that implement a Fhir Server,
i.e. EHRs such as Epic, Cerner etc.
The implementation has been tested with [SMART App Launcher](https://launch.smarthealthit.org/?auth_error=&fhir_version_1=r4&fhir_version_2=r4&iss=&launch_ehr=1&launch_url=https%3A%2F%2Fprovider-epic-sso.ngrok.io%2Fsmart-on-fhir-sso%2Flaunch&patient=87a339d0-8cae-418e-89c7-8651e6aab3c6&prov_skip_auth=1&prov_skip_login=1&provider=37881086-7b05-4b18-a279-08e331f50e9b&pt_skip_auth=1&public_key=&sb=&sde=&sim_ehr=1&token_lifetime=15&user_pt=).
Implemented:
- smarthealthit.org; to test the implementation
- Epic EHR

## Add new integration:
1. Get a client id from the EHR you want to integrate with. You should probably pass this into the app using env variables. See `config.php`.
2. Create a controller `MyControlerName` that implements `SmartOnFhirSsoController`. You will have to provider clientId, redirectUrl and a route that will get an authorization code. See example in `EpicSsoController::getAuthToken()`.
3. Add a route in `web.php` that points to `MyControllerName::getAuthToken()`.

## TODO:
- tests

### Read more [here](http://hl7.org/fhir/smart-app-launch/index.html).
