<form action="{{route('create-test-patients')}}" method="POST">
    {{csrf_field()}}
    <input class="btn btn-md btn-primary" type="submit" value="Create/Remove">
</form>