<div id="dtBox"></div>

<script>
    $(document).ready(function () {
        $("#dtBox").DateTimePicker({
            dateFormat: "MM-dd-yyyy",
            minuteInterval: 30
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>