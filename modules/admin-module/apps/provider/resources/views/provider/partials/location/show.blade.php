<div class="card-square mdl-card mdl-shadow--2dp">
    <div class="mdl-card__actions mdl-card--border">
        <span>
            {{$location['name']}}
        </span>
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
            <i class="material-icons">mode_edit</i>
        </a>
    </div>
    <div class="mdl-card__supporting-text">
        <h6>
            {{$location['address_line_1']}}, {{$location['address_line_2']}}
            <br>{{$location['city']}},
            <br>{{$location['state']}} - {{$location['postal_code']}}
            <br>{{$location['phone']}}
        </h6>
    </div>

</div>
