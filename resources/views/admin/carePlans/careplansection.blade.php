@if($carePlan->careSections)
    @foreach($carePlan->careSections as $careSection)
        @if($careSection->id == $section)
            <a class="" role="" data-toggle="collapse" href="#collapseSection{{ $careSection->id }}" aria-expanded="false" aria-controls="collapseSection{{ $careSection->id }}">
                <h1> {{ $careSection->display_name }} <a href="{{ route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-primary btn-xs section-reload" section="{{ $careSection->id }}">Refresh</a></h1>
            </a>
            @if(!empty($careSection->carePlanItems))
                <?php $i = 0; ?>
                <div class="collapse in" id="collapseSection{{ $careSection->id }}">
                    @foreach($careSection->carePlanItems as $planItem)
                        @if ($planItem->careItem->display_name != '')
                            @if($i % 2 == 0) START ROW<div class="row"> @endif
                                <div class="col-sm-6">
                                    {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                                    {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}
                                    <div class="col-sm-12" style="border:1px solid blue;">
                                        <h2>{{ $planItem->careItem->display_name }} <a href="{{ route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-orange btn-xs">Edit</a></h2><br />
                                        <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br />
                                        {{--[EYE:{{ $i+1 .' of '.$careSection->carePlanItems->count() }}]<br />
                                        [CarePlanItem:{{ $planItem->id }}]<br />
                                        [ui_fld_type:{{ $planItem->ui_fld_type }}]<br />
                                        [ui_row_start:{{ $planItem->ui_row_start }}]<br />
                                        [ui_row_end:{{ $planItem->ui_row_end }}]<br />
                                        [ui_col_start:{{ $planItem->ui_col_start }}]<br />
                                        [ui_default:{{ $planItem->ui_default }}]<br />Other
                                        [obs_key:{{ $planItem->careItem->obs_key }}]<br />--}}
                                        @if (!is_null($planItem->children))
                                            @foreach($planItem->children as $planItemChild)
                                                {!! $planItemChild->ui_row_start > 0 ? 'start row<div class="row">' : '' !!}
                                                    @if ($planItemChild->ui_col_start > 0)
                                                        <div class="col-sm-{!! $planItemChild->ui_col_start !!}" style="border:1px solid blue;">
                                                            @endif
                                                            <strong>{{ $planItemChild->careItem->display_name }}</strong><br />
                                                            <strong>{{ $planItemChild->meta_key . ' = ' . $planItemChild->meta_value }}</strong><br />
                                                            {{--
                                                    [ui_fld_type:{{ $planItemChild->ui_fld_type }}]<br />
                                                    [ui_row_start:{{ $planItemChild->ui_row_start }}]<br />
                                                    [ui_row_end:{{ $planItemChild->ui_row_end }}]<br />
                                                    [ui_col_start:{{ $planItemChild->ui_col_start }}]<br />
                                                    [ui_default:{{ $planItemChild->ui_default }}]<br />
                                                    [ui_sort:{{ $planItemChild->ui_sort }}]<br />--}}
                                                            @if ($planItemChild->ui_col_end > 0)
                                                        </div>
                                                    @endif
                                                    {!! $planItemChild->ui_row_end > 0 ? '</div>end row<br />' : '' !!}
                                            @endforeach
                                        @endif
                                    </div>
                                    {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                                    {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                                    @if( ($i % 2 != 0) || ($careSection->carePlanItems->count() == ($i+1)) ) END ROW</div> @endif
                            </div>
                            @endif
                            <?php ++$i; ?>
                            @endforeach
                </div>
            @endif
        @endif
    @endforeach
@endif