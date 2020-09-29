<div class="flow-text" style="max-height: 590px;">
    <div style="display: inline-flex">
        <div class="header">
            {{$signatoryNameForHeader}}
            <br>
            {{$practiceDisplayName}}
       @if($extraAddressValuesExists)
                <br>
                {{$extraAddressValues[0]['address_line_1']}}
                <br>
                {{$extraAddressValues[0]['city']}}
                {{$extraAddressValues[0]['state']}}
                {{$extraAddressValues[0]['postal_code']}}
            @endif
        </div>
        <div class="logo" style="text-align: right; opacity: 90%;">
            @include('enrollment-letters.practiceLogo')
        </div>
    </div>

    <div class="letter-sent">
        {{$dateLetterSent}}
    </div>

    <div class="letter-head">
        Dear {{$userEnrollee->first_name}},
    </div>
</div>

<style>
    .header{
        padding-right: 700px;
        font-size: 23px;
    }

    .letter-sent {
        margin-top: -22px;
    }

    .logo{
        opacity: 90%;
        margin-top: 15px;
    }

    @media (max-width: 490px) {
        .logo{
            margin-left: 50px;
        }
    }
    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

    }
</style>