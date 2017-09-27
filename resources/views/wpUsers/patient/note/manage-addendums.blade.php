<form id="create-addendum" method="POST"
      action="{{route('note.store.addendum', ['patientId' => $patient->id, 'noteId' => $note['id']])}}">
    {{csrf_field()}}

    <label for="new-addendum-body">Add a new follow-up note</label>
    <textarea id="new-addendum-body" class="form-control" rows="4" name="addendum-body" required
              placeholder="Type the follow-up note here."></textarea>

    <div class="text-right">
        <button form="create-addendum" type="submit"
                class="btn btn-success btn-lg form-item--button form-item-spacing">
            Save
        </button>
    </div>
</form>


<div>
    @if(!$note['addendums']->isEmpty())
        @foreach($note['addendums'] as $addendum)
            @include('wpUsers.patient.note.addendum')
        @endforeach
    @else
        <p>There are no Follow-up Notes at this time. New Follow-up Notes will be displayed here.</p>
    @endif
</div>