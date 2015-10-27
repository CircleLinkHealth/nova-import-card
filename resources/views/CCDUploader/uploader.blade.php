@extends('app')

@section('content')
<div id="uploads"></div>
<div class="dropzone" id="dropzone">Drop an XML CCD record here</div>
<script src="{{ asset('/js/bluebutton.js') }}"></script>

<script>
    (function () {

        var dropzone = document.getElementById('dropzone');

        var upload = function (files) {
            var formData = new FormData(),
                    xhr = new XMLHttpRequest(),
                    x;

            for (x = 0; x < files.length; x = x + 1) {
                formData.append('file[]', files[x]);
            }

            xhr.open('post', 'upload-ccd');
            xhr.send(formData);

            xhr.onload = function () {
                var data = JSON.parse(this.responseText);

                var jsonCcds = [];

                data.forEach(function(ccd) {
                    var jsonCcd = parseCCD(ccd);
                    jsonCcds.push(jsonCcd);
                });

                /**
                 * AJAX POST THIS GUY TO LARAVEL
                 */
//                console.log(jsonCcds[1]);

            }
        }

        dropzone.ondrop = function (e) {
            e.preventDefault();
            this.className = 'dropzone';
            upload(e.dataTransfer.files);
        };


        dropzone.ondragover = function () {
            this.className = 'dropzone dragover';
            return false;
        };

        dropzone.ondragleave = function () {
            this.className = 'dropzone';
            return false;
        };
    }());

    function parseCCD(data)
    {
        bb = BlueButton(data);

        var ccd = {
            document : bb.data.document,
            demographics : bb.data.demographics,
            allergies : bb.data.allergies,
            carePlan : bb.data.care_plan,
            chiefComplaint : bb.data.chief_complaint,
            encouters : bb.data.encounters,
            functionalStatuses : bb.data.functional_statuses,
            immunizations : bb.data.immunizations,
            immunizationDeclines : bb.data.immunization_declines,
            instructions : bb.data.instructions,
            results : bb.data.results,
            medications : bb.data.medications,
            problems : bb.data.problems,
            procedures : bb.data.procedures,
            smokingStatus : bb.data.smoking_status,
            vitals : bb.data.vitals,
        };

        return JSON.stringify(ccd);
    }


</script>

@endsection
