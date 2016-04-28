{{--<input type=hidden name="careSections[]" value="{{ $careSection->id }}">--}}

<div class="main-form-container col-lg-8 col-lg-offset-2 cp-section"
     style="border-right: 3px solid #50b2e2;border-left: 3px solid #50b2e2;">
    <div class="main-form-block main-form-horizontal main-form-primary-horizontalX col-md-12 Xcp-section"
            {{--id="section{{ $careSection->id }}"--}}
    >
        {{--This is the careplan section edit from the admin panel--}}
        @if(isset($editMode) && $editMode != false)
            @include('partials.carePlans.sectionEdit')
        @else
            <h4>Diagnosis / Problems to Monitor</h4>
        @endif


        <div class="form-block form-block--left col-md-6">
            @for($i = 0; $i < count($cptProblems); $i++)
                @if($i == round(count($cptProblems) / 2))
                </div><div class='form-block form-block--right col-md-6'>
                @endif
            <div class="row">
                <div class="form-item col-sm-12" style="padding-left: 0px;">
                    <?php $item = $cptProblems[$i]; ?>
                    @include('partials.cpm-models.item')
                </div>
            </div>
            @endfor

        </div>

    </div>
</div>