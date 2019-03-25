<div class="container">
    <h2>Survey Questions</h2>
    <form method="POST" action="{{route('save.awv.survey.answer')}}">
        {{csrf_field()}}
    <div class="row justify-content-center">
        <div class="col-md-8">
            <survey-form-component></survey-form-component>

            <label for="answer">How you doing?</label><br>
            <input id="answer" type="text" class="validate" name="answer" required>
        </div>
        <button class="btn btn-primary" type="submit">Next</button>
    </div>
    </form>
</div>
