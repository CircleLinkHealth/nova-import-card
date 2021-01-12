 <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('ccd-old-viewer.post') }}" method="post" enctype="multipart/form-data" target="_blank">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="uploadedCcd">Select CCD to upload :</label>
                        <input type="file" name="uploadedCcd" id="uploadedCcd" required>
                        <p class="help-block">Hint: You can also drop a CCD on the Upload CCD button</p>

                        <input type="submit" class="btn btn-default" value="Upload & View CCD" name="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>