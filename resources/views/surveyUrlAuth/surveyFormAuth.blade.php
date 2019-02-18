<html>

<form method="POST" action="{{route('surveyAuth')}}">
    {{csrf_field()}}
    Full Name: <input type="text" name="name"><br>
    D.O.B: <input type="date" name="birth_date"><br>
    <input type="hidden" name="url" value="{{$urlWithToken}}"><br>
    <input type="submit" value="Submit">

</form>
</html>