<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('post.eligibility.reprocess', [$batch->id]) }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-group">
                        <h4>{{$batch->practice->display_name}}</h4>
                    </div>

                    <label for="dir">Drive Dir</label>
                    <input class="" type="text" name="dir" id="dir"
                           value="{{$batch->options['dir'] ?? $batch->options['folder'] ?? ''}}" required>

                    <br>

                    <label for="file">Filename as appears in Drive (eg `CircleLink.json`). Only fill in if input is in
                        json format.</label>
                    <input class="" type="text" name="file" id="file" value="{{$batch->options['fileName'] ?? ''}}">

                    <br>

                    <input class="" type="checkbox" name="filterLastEncounter"
                           id="filterLastEncounter" {{!! $batch->options['filterLastEncounter'] ? 'checked' : ''}}>
                    <label for="">filterLastEncounter</label>

                    <br>

                    <input class="" type="checkbox" name="filterProblems"
                           id="filterProblems" {{!! $batch->options['filterProblems'] ? 'checked' : ''}}>
                    <label for="">filterProblems</label>

                    <br>

                    <input class="" type="checkbox" name="filterInsurance"
                           id="filterInsurance" {{!! $batch->options['filterInsurance'] ? 'checked' : ''}}>
                    <label for="">filterInsurance</label>

                    <br>

                    <h5>Reprocessing Method</h5>

                    <input class="" type="radio" name="reprocessingMethod" id="reprocessingMethod"
                           value="{{CircleLinkHealth\Eligibility\Entities\EligibilityBatch::REPROCESS_SAFE}}" checked>
                    <label for="reprocessingMethod">Safe: This will replace patient data in CPM with data from list,
                        without deleting any records. Choose this if the already processed list has been passed to the
                        callers.</label>

                    <br>

                    <input class="" type="radio" name="reprocessingMethod" id="reprocessingMethod"
                           value="{{CircleLinkHealth\Eligibility\Entities\EligibilityBatch::REPROCESS_FROM_SCRATCH}}">
                    <label for="reprocessingMethod">Start from scratch: This will delete all patient data in CPM related
                        to this batch, and start processing the list from scratch. Do NOT choose this if the already
                        processed list has been passed to the callers, because you will NOT be able to import patients
                        from the originally processed batch.</label>

                    <input type="submit" class="btn btn-default" value="Reprocess" name="submit">
                </div>
            </form>
        </div>
    </div>
</div>
