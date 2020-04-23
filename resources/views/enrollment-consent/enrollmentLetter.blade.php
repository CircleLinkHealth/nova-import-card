@foreach($letterPages as $page)
    <div class="pagination">
        {!! $page !!}
    </div>
@endforeach

<style>
    .pagination{
        margin-right: 10px;
    }
</style>