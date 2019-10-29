<div class="panel panel-default">
    <div class="panel-heading">Create Test patients</div>
    <div class="panel-body">
        <form action="{{route('create-test-patients')}}" method="POST">
            {{csrf_field()}}
            <input class="btn btn-md btn-primary" type="submit" value="Create Patients">
        </form>
    </div>
</div>