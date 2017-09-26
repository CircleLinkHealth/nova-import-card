<div class="">
    <form id="create-addendum">
        <label for="new-addendum-body">Add a new addendum</label>
        <textarea id="new-addendum-body" class="form-control" rows="4" name="addendum" placeholder="Type the note here."></textarea>

        <div class="text-right">
            <button form="create-addendum" type="submit"
                    class="btn btn-success btn-lg form-item--button form-item-spacing">
                Save
            </button>
        </div>
    </form>
</div>

<div>
    @foreach($note['addendums'] as $addendum)
        @include('wpUsers.patient.note.addendum')
    @endforeach
</div>