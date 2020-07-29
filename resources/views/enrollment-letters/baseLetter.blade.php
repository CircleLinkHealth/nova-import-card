<div class="letter-sent">
    {{$dateLetterSent}}
</div>
<div class="letter-head">
    Dear {{$userEnrollee->first_name}},
</div>
<div class="letter-body">
    <div class="body">
        @include('enrollment-letters.enrollmentLetter')
    </div>
</div>
