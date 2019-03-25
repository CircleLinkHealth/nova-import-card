<html>
{{--This Link has expired click <a href="{{route('resendUrl', [$userId])}}">HERE</a> to get a new one--}}


<form method="POST" action="{{route('resendUrl', [$userId])}}">
    {{csrf_field()}}
  This link has expired. <input type="submit" value="Click here to receive a new one">

</form>
</html>