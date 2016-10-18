<html>
<body>
<div class="container">
    To: Dr. {{ $provider->fullName }}

    Thank you for using CircleLink for chronic care management!

    Below patient note was sent to you by care coach {{ $sender->fullName }} at {{ $note->created_at }}. We appreciate
    your review:

    Re: {{ $patient->fullName }} │ DOB: {{ $patient->birthDate }} │ {{ $patient->gender }} │ {{$patient->age}} yrs
    │ {{ $patient->phone }}

    Chronic conditions tracked:
    <ul>
        @foreach($problems as $problem)
            <li>{{ $problem }}</li>
        @endforeach
    </ul>

    Note:
    <em>{{ $note->body }}</em>


    With regards,
    CircleLink Team
</div>
</body>
</html>