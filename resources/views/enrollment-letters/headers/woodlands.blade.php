<div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
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
</div>

<style>
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