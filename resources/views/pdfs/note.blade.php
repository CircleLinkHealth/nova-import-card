<html>

<head>
    <style>

    </style>
</head>

<body>
<div class="container">

    <div class="col-md-3 col-md-offset-9">
        <img src="{{ public_path('img/logo.svg') }}" class="img-responsive" alt="CLH Logo">
    </div>

    <p>
        To: Dr. {{ $provider->fullName }}
    </p>

    <p>
        Thank you for using CircleLink for chronic care management!
    </p>


    <p>
        Below patient note was sent to you by care coach {{ $sender->fullName }} at {{ $note->created_at }}. We
        appreciate
        your review:
    </p>

    <p>
        Re: {{ $patient->fullName }} &#124; DOB: {{ $patient->birthDate }} &#124; {{ $patient->gender }}
        &#124; {{$patient->age}} yrs &#124; {{ $patient->phone }}
    </p>

    <div class="row">
        Chronic conditions tracked:
        <ul>
            @foreach($problems as $problem)
                <li>{{ $problem }}</li>
            @endforeach
        </ul>
    </div>

    <p>
        Note:
        <em>{{ $note->body }}</em>
    </p>

    <p>
        With regards,
    </p>

    <p>
        CircleLink Team
    </p>
</div>
</body>
</html>