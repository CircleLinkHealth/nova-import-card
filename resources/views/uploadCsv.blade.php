<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">Upload Call List CSV Document</div>

        <div class="panel-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('post.CallController.import') }}" method="post" enctype="multipart/form-data" target="_blank">
                            <div class="form-group">
                                <label for="uploadedCsv">Upload Call List .csv:</label>
                                <input type="file" name="uploadedCsv" id="uploadedCsv" required>

                                <input type="submit" class="btn btn-default" value="Upload" name="submit">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>