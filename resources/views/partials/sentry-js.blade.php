<script
        src="https://browser.sentry-cdn.com/5.19.2/bundle.min.js"
        integrity="sha384-JpEWxJ7oLRn+baXBgcCKEv73uYWsgouzEResgJneOXvTjDZ+1crAXcNAKZoiL96Z"
        crossorigin="anonymous"></script>

<script>
    function init() {
        const apiKey = @json(env('SENTRY_LARAVEL_DSN', null));
        if (!apiKey) {
            return;
        }
        Sentry.init({
            dsn: apiKey,
            environment: @json(config('app.env', 'production'))
        });

        console.debug('Sentry initialized');
        const isAuth = @json(auth()->check());
        if (!isAuth) {
            return;
        }

        const user = @json(auth()->check() ? [
            'id' => auth()->id(),
            'username' => auth()->user()->username,
            'email' => auth()->user()->email
        ] : []);

        Sentry.configureScope(function (scope) {
            scope.setUser(user);
        });

        console.debug('Sentry Scope configured');
    }

    init();
</script>