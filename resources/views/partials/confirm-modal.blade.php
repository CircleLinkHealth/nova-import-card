<!--
    A generic modal for confirming actions.
    Usage: 
    ```
        $.showConfirmModal({
            title: "Modal Title",
            body: "Modal Body",
            confirmText: 'Skip', //override confirm text
            cancelText: 'Go Back', //override cancel text
            neverShow: true //show or hide div that shows "I don't want to see this again"
        }).then(function (action) {
            if (action.constructor.name === 'Object') {
                // [action] is an object with schema:
                /**
                    {
                        action: true, //or false,
                        neverShowAgain: true //or false
                    }
                */
            }
            else {
                // [action] is a Boolean
                if (action); //user confirmed
                else; //user cancelled
            }
            
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
            <div class="modal-footer never-show">
                <p>
                    <label>
                        <input type="checkbox" name="never-show" value="never-show">
                        I don't want to see this again.
                    </label>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" name="confirm" class="btn btn-primary" data-dismiss="modal">Confirm</button>
                <button type="button" name="cancel" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .modal-footer.never-show {
            padding: 10px;
            text-align: left;
            font-size: 18px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function ($) {
            var $modal = $("#confirm-modal");
            var $title = $modal.find(".modal-title");
            var $body = $modal.find(".modal-body");
            var $confirm = $modal.find("[name='confirm']");
            var $cancel = $modal.find("[name='cancel']");
            var $close = $modal.find(".close");
            var $neverShowContainer = $modal.find(".never-show");
            var $neverShow = $modal.find("[name='never-show']")
            $.showConfirmModal = function (modal) {
                console.log("modal-definition", modal);
                $title.html(modal.title || "");
                $body.html(modal.body || "");
                $confirm.text(modal.confirmText || 'Confirm');
                $cancel.text(modal.cancelText || 'Cancel');
                $confirm.off('click');
                $cancel.off('click');
                var key = 'confirm-modal:' + (modal.name || 'unknown') + ':skipped'
                if (!!modal.neverShow) {
                    $neverShow.change(function (e) {
                        if (window.localStorage) {
                            if (e.target.checked) {
                                window.localStorage.setItem(key, true)
                            }
                            else {
                                window.localStorage.removeItem(key)
                            }
                        }
                    })
                    $neverShowContainer.show();
                }
                else $neverShowContainer.hide();
                if (window.localStorage.getItem(key)) {
                    return Promise.resolve({
                        action: true,
                        neverShowAgain: true
                    })
                }
                $modal.modal({backdrop: 'static', keyboard: false});

                var isComplex= !!modal.neverShow;

                var getReturnValue = function (action) {
                    return !isComplex ? action : {
                        action: action,
                        neverShowAgain: $neverShow.is(':checked')
                    }
                }

                return new Promise(function (resolve, reject) {
                    $confirm.on('click', function () {
                        resolve(getReturnValue(true));
                    })
                    $cancel.on('click', function () {
                        resolve(getReturnValue(false));
                    })
                    $close.on('click', function () {
                        resolve(getReturnValue(false));
                    })
                })
            }
        })(jQuery);
    </script>
@endpush