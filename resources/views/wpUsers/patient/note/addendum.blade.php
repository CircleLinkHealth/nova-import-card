<blockquote>
    <p class="text-left">{{$addendum->body}}</p>

    <p class="text-right">
        <sub><em>Authored by {{$addendum->author->fullName}}, <b>{{ $addendum->created_at->diffForHumans() }}</b></em></sub>
    </p>
</blockquote>
