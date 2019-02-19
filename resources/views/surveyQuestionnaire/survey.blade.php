<div class="container">
    <div id="app">
        <survey-form></survey-form>
    </div>


    {{--  <h2>Survey Questions</h2>
      <form method="POST" action="{{route('saveSurveyAnswer')}}">
          {{csrf_field()}}
          <div class="form-group">
              <div class="col-md-8">
                  <label for="answer">How you doing?</label><br>
                  <input id="answer" type="text" class="validate" name="answer" required>
                  <input type="hidden" name="link_token" value="{{$urlToken}}">
              </div>

              <button class="btn btn-primary" type="submit">Next</button>
          </div>
      </form>--}}
    <script src="{{asset('js/app.js')}}"></script>
</div>

