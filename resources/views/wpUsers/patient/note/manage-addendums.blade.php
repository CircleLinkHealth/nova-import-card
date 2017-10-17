<div>
    @if(!$note['addendums']->isEmpty())
        @foreach($note['addendums'] as $addendum)
            @include('wpUsers.patient.note.addendum')
        @endforeach
    @endif
</div>

<form id="create-addendum" method="POST"
      action="{{route('note.store.addendum', ['patientId' => $patient->id, 'noteId' => $note['id']])}}" style="margin: 30px 0 20px 0;">
    {{csrf_field()}}

    <label for="new-addendum-body"></label>
    <textarea id="new-addendum-body" class="form-control" rows="4" name="addendum-body" required
              placeholder="Type an addendum here."></textarea>

    <div class="text-right">
        <button form="create-addendum" type="submit"
                class="btn btn-success btn-lg form-item--button form-item-spacing">
            Add Addendum
        </button>
    </div>
</form>