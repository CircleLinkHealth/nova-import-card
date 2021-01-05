@foreach($letterPages as $page)
    <div class="browser-default">
        {!! $page !!}
    </div>
@endforeach

<style>
    .pagination{
        margin-right: 10px;
    }
</style>