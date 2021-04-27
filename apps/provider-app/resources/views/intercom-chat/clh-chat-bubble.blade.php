@php
    $intercomAppId = config('services.intercom.intercom_app_id');
@endphp

<script>
    if (window.innerWidth > 700){
        window.intercomSettings = {
            app_id: {!! @json_encode($intercomAppId) !!},
            name: {!! @json_encode($user->display_name) !!},
            email: {!! @json_encode($user->email) !!},
            created_at: "{!! strtotime($user->created_at) !!}",
            alignment:{!! @json_encode($alignment) !!},
            vertical_padding: 20
        }
    }
</script>

<script>
    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/t8vmjlp7';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>