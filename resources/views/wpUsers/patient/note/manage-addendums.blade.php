<h3>Follow-up Notes (Addendums)</h3>

<div class="row">

</div>

<div>
    @foreach($note['addendums'] as $addendum)
        @include('wpUsers.patient.note.addendum')
    @endforeach
</div>