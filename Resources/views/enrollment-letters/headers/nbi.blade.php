<div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
    <div style="display: inline-flex">
        <div class="header">
            <div class="logo" style="text-align: left; opacity: 90%;">
                @include('selfEnrollment::enrollment-letters.practiceLogo')
            </div>
        </div>
    </div>

    <div class="letter-sent">
        {{$dateLetterSent}}
    </div>

    <div class="letter-head">
        Dear {{$userEnrollee->first_name}} {{$userEnrollee->last_name}},
    </div>
</div>

<style>
    .letter-head{
        padding-bottom: unset;
    }

    .letter-sent{
        margin-top: -27px;
    }

    .header{
        padding-right: 700px;
        font-size: 23px;
    }

    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

    }
</style>
