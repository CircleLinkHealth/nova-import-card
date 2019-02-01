<html>

<form method="POST" action="{{route('surveyAuth', [$patientId])}}">
    {{csrf_field()}}
    Full Name: <input type="text" name="name"><br>
    D.O.B: <input type="date" name="date_of_birth"><br>
    <input type="submit" value="Submit">

</form>
</html>