<div name="complexity_toggle_container">
    <form method="post" name="complexity_toggle_form" id="complexity_toggle" action="{{URL::route('patient.ccm.toggle', array('patient' => $patient->id))}}" class="form-horizontal">
        {{ csrf_field() }}
        <div class="radio-inline">
            <input type="checkbox" name="complex" {{$ccm_complex ? 'checked' : ''}} id="complex"/>
            <label for="complex"><span> </span>Complex CCM</label>
            <input type="hidden" name="action" value="{{URL::route('patient.ccm.toggle', array('patient' => $patient->id))}}" />
        </div>
    </form>
</div>

<script>
    /**
    * Manage the CCM Badge Form
    * Dependencies:
    * - partials.confirm-modal.blade.php
    */
    (function ($, document) {
        var $form = $("[name='complexity_toggle_form']");
        var $container = $("[name='complexity_toggle_container']");
        var $token = $container.find("[name='_token']");
        var $action = $container.find("[name='action']");
        var $checkbox = $container.find("[name='complex']");
        var $modal = $("#confirmButtonModal");
        var ccmBadgeModelState = {
            title: "Confirm Complex CCM Patient",
            body: `<p>Please confirm patient will benefit from extra CCM care time this month.</p>
                   <p>Friendly Reminder: A Medication Reconciliation is required for Complex CCM patients.</p>`
        }
        var submitViaAjax = function (e) {
            console.log("complex-ccm-badge-form-request", $action.val(), $(this).serialize(), e)
            $.post($action.val(), $(this).serialize()).then(function (res) {
                console.log("complex-ccm-badge-form-response", "see network");
            }).catch(function (err) {
                console.error(err);
            })
            return false;
        }
        var submitForm = function (isChecked) {
            if ($form.length) submitViaAjax.call($form.first())
            else {
                /**
                * Laravel does not render the form when it exists within a parent form, so create a new form dynamically
                */
                var newForm = document.createElement("form");
                newForm.action = $action.val();
                newForm.method = "post";
                var tokenInput = document.createElement("input");
                tokenInput.type = "hidden";
                tokenInput.value = $token.val();
                tokenInput.setAttribute("name", "_token");
                var checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.setAttribute("name", "complex");
                checkbox.setAttribute("checked", "checked");
                checkbox.checked = isChecked;
                newForm.append(tokenInput);
                newForm.append(checkbox);
                document.body.append(newForm);
                console.log(newForm);
                submitViaAjax.call(newForm)
            }
        }
        console.log("ccs-badge-form", $form, $container);
        $(document).ready(function () {
            /*
            * Dependency Validation
            */
            if (typeof $.showConfirmModal != 'function') {
                throw new Error("Cannot find '$.showConfirmModal' ... Please add 'partials.confirm-modal.blade.php' to page")
            }

            $checkbox.change(function (e) {
                if ($checkbox.is(':checked')) {
                    $.showConfirmModal(ccmBadgeModelState).then(function (action) {
                        if (action) submitForm(true);
                        else $checkbox.prop("checked", false); //set the prop back to false
                    })
                }
                else submitForm(false);
            });
        });
    })(jQuery, document)
</script>