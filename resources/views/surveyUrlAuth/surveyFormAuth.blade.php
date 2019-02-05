<html>

<form method="POST" action="{{route('surveyAuth', [$userId])}}">
    {{csrf_field()}}
    Full Name: <input type="text" name="name"><br>
    D.O.B: <input type="date" name="date_of_birth"><br>
    <input type="hidden" name="url" value="{{$urlWithToken}}"><br>
    <input type="submit" value="Submit">

</form>
</html>