<style>
    .note-addendum {
        padding: 15px;
        border: 1px solid #eee;
        margin: 15px 0 30px 0;
        background-color: #ebf1f5;
    }
</style>

<div class="note-addendum">
    <p class="text-left">{{$addendum->body}}</p>

    <p class="text-right" style="padding-top: 2%;">
        <sub>Authored by <em>{{$addendum->author->fullName}}, <b>{{ $addendum->created_at->diffForHumans() }}</b></em></sub>
    </p>
</div>
