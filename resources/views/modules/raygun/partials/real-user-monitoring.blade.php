@if(true === \Config::get('cpm-module-raygun.enable_real_user_monitoring'))
    @php
        $raygunUser = app(config('cpm-module-raygun.raygun_user'));
    @endphp

    <script type="text/javascript">
        !function (a, b, c, d, e, f, g, h) {
            a.RaygunObject = e, a[e] = a[e] || function () {
                (a[e].o = a[e].o || []).push(arguments)
            }, f = b.createElement(c), g = b.getElementsByTagName(c)[0],
                f.async = 1, f.src = d, g.parentNode.insertBefore(f, g), h = a.onerror, a.onerror = function (b, c, d, f, g) {
                h && h(b, c, d, f, g), g || (g = new Error(b)), a[e].q = a[e].q || [], a[e].q.push({
                    e: g
                })
            }
        }(window, document, "script", "//cdn.raygun.io/raygun4js/raygun.min.js", "rg4js");
    </script>

    <script type="text/javascript">
        rg4js('apiKey', '{{\Config::get('cpm-module-raygun.api_key')}}');
        rg4js('enableCrashReporting', {{\Config::get('cpm-module-raygun.enable_crash_reporting') ? 'true' : 'false'}});
        rg4js('enablePulse', {{\Config::get('cpm-module-raygun.enable_real_user_monitoring_pulse') ? 'true' : 'false'}});
        rg4js('logContentsOfXhrCalls', {{\Config::get('cpm-module-raygun.log_contents_of_xhr_calls') ? 'true' : 'false'}})

        @if(auth()->check())
            rg4js('setUser', {!! json_encode($raygunUser()) !!});

            function addLogoutListener() {
                if (typeof App === 'undefined') {
                    setTimeout(addLogoutListener, 500);
                    return;
                }

                if (App.EventBus) {
                    App.EventBus.$on('user:logout', function () {
                        rg4js('endSession');
                    });
                }
            }
            addLogoutListener();
        @endif
    </script>
@endif
