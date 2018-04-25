<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.enrollees.import.array') }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="enrollee_ids">Eligible Patient IDs</label>
                    <input class="form-control" type="text" name="enrollee_ids" id="enrollee_ids"
                           placeholder="135,1235,123126,123,2452" required>

                    <br>

                    <input type="submit" class="btn btn-default" value="Import" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>
