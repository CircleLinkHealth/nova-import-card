@if(true === \Config::get('cpm-module-raygun.enable_crash_reporting'))
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

        @if(auth()->check())
            rg4js('setUser', {
                identifier: "{{auth()->id()}}",
                isAnonymous: false,
                email: "{{auth()->user()->email}}",
                firstName: "{{auth()->user()->first_name}}",
                fullName: "{{auth()->user()->display_name}}"
            });
        @endif
    </script>
@endif