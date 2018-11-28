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
            <h4>{{ $section->title }}</h4>
        @endif

        <div class="form-block form-block--left col-md-6">

            <?php $half = round((count($section->items) + count($section->miscs)) / 2); ?>

            {{--Do this once for the items--}}
            @for($i = 0; $i < count($section->items); $i++)
                @if($i == $half)
        </div>
        <div class='form-block form-block--right col-md-6'>
            @endif

            <div class="row">
                <div class="form-item col-sm-12" style="padding-left: 0px;@if (is_a($section->items[$i], App\Models\CPM\CpmProblem::class) && $section->items[$i]->name == 'Diabetes' && !auth()->user()->isAdmin()) display:none; @endif">
                    <?php
                    $item = $section->items[$i];
                    ?>
                    @include('partials.cpm-models.item')
                </div>
            </div>
            @endfor

            {{--And once for the miscs--}}

            {{--
            @todo: refactor the following hacks

            These are hacks to help name HTML attributes name and id.
            We want to be able to map them to models.
            So a CpmMisc showing up in problems, should be have name="miscs[]" and not name="problems[]"
            --}}
            <?php $section->name = 'cpmMiscs'; ?>
            <?php $section->patientItemIds = $section->patientMiscsIds; ?>
            <?php $section->patientItems = $section->patientMiscs; ?>

            @for($i = 0; $i < count($section->miscs); $i++)
                @if($i == $half)
        </div>
        <div class='form-block form-block--right col-md-6'>
            @endif
            <div class="row">
                <div class="form-item col-sm-12" style="padding-left: 0px;">
                    <?php
                    $item = $section->miscs[$i];
                    ?>
                    @include('partials.cpm-models.item')
                </div>
            </div>
            @endfor

        </div>

    </div>
</div>