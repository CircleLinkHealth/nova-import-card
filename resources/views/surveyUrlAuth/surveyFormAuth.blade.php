<html>

<form method="POST" action="{{route('surveyAuth', [$patientId])}}">
    {{csrf_field()}}
    Full Name: <input type="text" name="name"><br>
    D.O.B: <input type="date" name="date_of_birth"><br>
    {{--is it safe to use url() here?? i can also fetch the value from session->preciousUrl()--}}
    <input type="hidden" name="url" value="{{url()->full()}}">
    <input type="submit" value="Submit">

</form>
</html>