<div class="row" style="margin:20px 0px 40px 0px;">
    <div class="col-md-8 col-md-offset-2">
        <div class="row">
            <div class="col-xs-4 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
            <div class="col-xs-8">{!! Form::select('filterUser', array('all' => 'All Users') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
        </div>
    </div>
</div>