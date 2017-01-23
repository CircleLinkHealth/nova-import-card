<style>

</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('post.train.importing.algorithm') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="ccda">Just upload a CCDA to train the algo.</label>
                    <input type="file" name="ccda" id="ccda" required>
                    <p class="help-block">Hint: You can also drop a CSV file on this panel</p>

                    <input type="submit" class="btn btn-default funky-background" value="Train" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>