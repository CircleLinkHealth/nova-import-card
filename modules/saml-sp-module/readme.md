# Laravel SAML SP for CPM.

## Notes:
1. Follow https://github.com/aacotroneo/laravel-saml2 to setup the package.
2. Create a `Saml2Controller` that extends `\Aacotroneo\Saml2\Http\Controllers\Saml2Controller` and add to `saml2_settings.php`.
3. If you get errors about http vs https, set `proxyVars => true` in `saml2_settings`.
4. You should have a route for errors. See `saml2/error` (points to `Resources/views/errror.blade.php`).
5. You probably need a separate middleware for the saml2 routes. See `saml` middleware.

## TODO:
- tests
