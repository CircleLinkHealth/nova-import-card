<!DOCTYPE html>
<html>
<head>
    <title>Hospitalisation Notes Report</title>
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.9.2/tailwind.min.css"
          integrity="sha512-l7qZAq1JcXdHei6h2z8h8sMe3NbMrmowhOl+QkP3UhifPpCW2MC4M0i26Y8wYpbz1xD9t61MLT9L1N773dzlOA=="
          crossorigin="anonymous"/>
</head>
<body>

<div class="w-screen">
    <br/>
    <div class="flex items-center markdown">
        <h1 style="font-size: 2em;"><b>Hospitalisation Notes Report</b></h1>
    </div>
    <br/>
    <div class="w-screen mb-4">
        <livewire:tables.hospitalisation-notes-report exportable per-page="15"/>
    </div>

</div>

</body>
@livewireScripts
</html>