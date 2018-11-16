<div id="tips" class="modal confirm modal-fixed-footer">
    <div class="modal-content">
        <div class="row">
            <div class="input-field col s12">
                <h5>Tips</h5>
                <br/>

                @if(count($enrollee->practice->enrollmentTips))
                    {!! $enrollee->practice->enrollmentTips->content !!}
                @endif

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="row">
            <div class="col s6">
                <div style="margin-top: 10px">
                    <input id="do-not-show-tips-again"
                           name="do-not-show-tips-again"
                           type="checkbox" @click="doNotShowTipsAgain"/>
                    <label for="do-not-show-tips-again">Do not show again</label>
                </div>
            </div>
            <div class="col s6 text-right">
                <button type="button"
                        data-dismiss="modal" aria-label="Got it!"
                        class="modal-close waves-effect waves-light btn">
                    Got it!
                </button>
            </div>
        </div>

    </div>
</div>

