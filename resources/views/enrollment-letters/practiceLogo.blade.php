@if(isset($practiceLogoSrc) && isset($practiceDisplayName))
    <div class="practice-logo">
        <img src="{{$practiceLogoSrc}}"
             alt="{{$practiceDisplayName}}"/>
    </div>
@endif
<style>
    .practice-logo img{
        height: 120px;
    }

    @media (max-width: 490px) {
        .practice-logo img{
            height: 90px;
        }

    }

</style>