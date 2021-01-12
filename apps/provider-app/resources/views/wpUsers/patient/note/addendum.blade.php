<div>
    <p class="text-left">{{$addendum->body}}</p>

    <p class="text-right" style="padding-top: 2%;">
        <sub>Authored by <em>{{$addendum->author->getFullName()}}, <b>{{ $addendum->created_at->format('m/d/Y h:iA T') }}</b></em></sub>
    </p>
</div>

