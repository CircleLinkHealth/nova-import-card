(function () {

    var dropzone = document.getElementById('dropzone');

    function createCORSRequest(method, url) {
        var xhr = new XMLHttpRequest();
        if ("withCredentials" in xhr) {

            // Check if the XMLHttpRequest object has a "withCredentials" property.
            // "withCredentials" only exists on XMLHTTPRequest2 objects.

            xhr.open(method, url, true);

        } else if (typeof XDomainRequest != "undefined") {

            // Otherwise, check if XDomainRequest.
            // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
            xhr = new XDomainRequest();
            xhr.open(method, url);

        } else {

            // Otherwise, CORS is not supported by the browser.
            xhr = null;

        }
        return xhr;
    }

    var url = '/upload-raw-ccds';

    var xhr = createCORSRequest('post', url);
    if (!xhr) {
        throw new Error('CORS not supported');
    }

    var upload = function (files) {
        var formData = new FormData(),
            x;

        for (x = 0; x < files.length; x = x + 1) {
            formData.append('file[]', files[x]);
        }

        xhr.send(formData);

        xhr.onload = function () {
            var data = JSON.parse(this.responseText);

            var jsonCcds = [];

            data.forEach(function(ccd) {
                var jsonCcd = parseCCD(ccd.xml);

                var parsedCCD = {
                    userId:ccd.userId,
                    ccd:jsonCcd
                };

                jsonCcds.push(parsedCCD);
            });

        xhr.onerror = function() {
            console.log('There was an error!');
        };

        /**
         * XHR POST THIS GUY TO THE API
         */

        var url2 = '/upload-parsed-ccds';

        var xhr2 = createCORSRequest('post', url2);

        var json = JSON.stringify(jsonCcds);

        xhr2.send(json);

        xhr2.onload = function () {
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
