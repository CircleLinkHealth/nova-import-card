(function () {

    var dropzone = document.getElementById('dropzone');

    var upload = function (files) {
        var formData = new FormData(),
            xhr = new XMLHttpRequest(),
            x;

        for (x = 0; x < files.length; x = x + 1) {
            formData.append('file[]', files[x]);
        }

        xhr.open('post', 'upload-raw-ccds');
        xhr.send(formData);

        xhr.onload = function () {
            var data = JSON.parse(this.responseText);

            var jsonCcds = [];

            data.forEach(function(ccd) {
                var jsonCcd = parseCCD(ccd);
                jsonCcds.push(jsonCcd);
            });

        /**
         * XHR POST THIS GUY TO LARAVEL
         */

        xhr.open('post', 'upload-parsed-ccds');
        xhr.send(JSON.stringify(jsonCcds));

        xhr.onload = function () {
            alert(this.responseText);
        }

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

    return ccd;
}
