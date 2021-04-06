# Smart On Fhir SSO

## Features
Provides a framework to integrate SSO with applications that implement a Fhir Server,
i.e. EHRs such as Epic, Cerner etc.
The implementation has been tested with [SMART App Launcher](https://launch.smarthealthit.org/?auth_error=&fhir_version_1=r4&fhir_version_2=r4&iss=&launch_ehr=1&launch_url=https%3A%2F%2Fprovider-epic-sso.ngrok.io%2Fsmart-on-fhir-sso%2Flaunch&patient=87a339d0-8cae-418e-89c7-8651e6aab3c6&prov_skip_auth=1&prov_skip_login=1&provider=37881086-7b05-4b18-a279-08e331f50e9b&pt_skip_auth=1&public_key=&sb=&sde=&sim_ehr=1&token_lifetime=15&user_pt=).
Implemented:
- smarthealthit.org; to test the implementation
- Epic EHR

## Setup for existing integrations (Epic SSO)
1. Create app in Epic on FHIR. You should set two redirect uris: `your_domain/smart-on-fhir-sso/launch` and `your_domain/smart-on-fhir-sso/epic-code`.
2. Set env variables `EPIC_APP_CLIENT_ID` and `EPIC_APP_STAGING_CLIENT_ID`.
3. Publish config and configure a routes middleware that should at least have the following:
```
[
   EncryptCookies::class,
   AddQueuedCookiesToResponse::class,
   StartSession::class,
   \Illuminate\Session\Middleware\AuthenticateSession::class,
   LogoutIfAccessDisabled::class,
]
   ```
The default middleware name is `saml`, in case you don't want to publish the config file.

## Add new integration:
1. Get a client id from the EHR you want to integrate with. You should probably pass this into the app using env variables. See `config.php`.
2. Create a controller `MyControlerName` that implements `SmartOnFhirSsoController`.
   You will have to provide a platform name, the property name to read from the decoded open id token that defines the user id, a clientId, a redirectUrl and a route that will get an authorization code. See example in `EpicSsoController::getAuthToken()`.
3. Add a route in `web.php` that points to `MyControllerName::getAuthToken()`.

### Read more [here](http://hl7.org/fhir/smart-app-launch/index.html).
