<div class="headers">
    <div class="logo" style="text-align: center">
        @include('enrollment-letters.practiceLogo')
    </div>
    <br>
    <hr>
</div>
<div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
    <div class="header">
        {{$signatoryNameForHeader}}
        <br>
        {{$practiceDisplayName}}
    </div>

    <div class="letter-sent">
        {{$dateLetterSent}}
    </div>

    <div class="letter-head">
        Dear {{$userEnrollee->first_name}},
    </div>
</div>

<style>
    .letter-sent {
        margin-top: -20px;
    }

</style>
