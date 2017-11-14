<!--
    A generic modal for confirming actions.
    Usage: 
    ```
        $.showConfirmModal({
            title: "Modal Title",
            body: "Modal Body"
        }).then(function (action) {
            if (action); //user confirmed
            else; //user cancelled
        })
    ```
-->
<div id="confirm-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" name="confirm" id="complex_confirm" class="btn btn-primary"
                        data-dismiss="modal">Confirm
                </button>
                <button type="button" name="cancel" id="complex_cancel" class="btn btn-danger"
                        data-dismiss="modal">Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function ($) {
            var $modal = $("#confirm-modal");
            var $title = $modal.find(".modal-title");
            var $body = $modal.find(".modal-body");
            var $confirm = $modal.find("[name='confirm']");
            var $cancel = $modal.find("[name='cancel']");
            $.showConfirmModal = function (modal) {
                console.log("modal-definition", modal);
                $title.html(modal.title || "");
                $body.html(modal.body || "");
                $confirm.text(modal.confirmText || 'Confirm');
                $cancel.text(modal.cancelText || 'Cancel');
                $confirm.off('click');
                $cancel.off('click');
                $modal.modal({backdrop: 'static', keyboard: false});
                return new Promise(function (resolve, reject) {
                    $confirm.on('click', function () {
                        resolve(true);
                    })
                    $cancel.on('click', function () {
                        resolve(false);
                    })
                })
            }
        })(jQuery);
    </script>
@endpush