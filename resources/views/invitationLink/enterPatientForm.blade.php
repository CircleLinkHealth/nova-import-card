<html>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{route('createSendInvitationUrl')}}">
    {{csrf_field()}}
    {{--Probably will be able to input the name and/or phone also--}}
    Patient's Id: <input type="text" name="id" required><br>
    <input type="submit" value="Submit">

</form>
</html>