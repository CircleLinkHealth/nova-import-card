@extends('layouts.no-menu')

@include('footer')

@section('content')
    <div class="container">
        <div class="content">

            <div class="page-content">

                <textarea id="xml" style="display: block;">
                    @if(!empty($xml))
                        {{ $xml }}
                    @endif
                </textarea>

                <button style="background-color: orange; color: white;" onclick="convert()">Convert to Human Form</button>

                <h2>View by section:</h2>
                <a href="#document-section">Document</a>,
                <a href="#demographics-section">Demographics</a>,
                <a href="#allergies-section">Allergies</a>,
                <a href="#careplan-section">Care Plan</a>,
                <a href="#chiefcomplaint-section">Chief Complaint</a>,
                <a href="#encounters-section">Encounters</a>,
                <a href="#functionalstatus-section">Functional Status</a>,
                <a href="#immunizations-section">Immunizations</a>,
                <a href="#immunizationdeclines-section">Declined Immunizations</a>,
                <a href="#instructions-section">Patient Instructions</a>,
                <a href="#medications-section">Medications</a>,
                <a href="#problems-section">Problems</a>,
                <a href="#procedures-section">Procedures</a>,
                <a href="#results-section">Results (Labs)</a>,
                <a href="#smokingstatus-section">Smoking Status</a>,
                <a href="#vitals-section">Vitals</a>

                <a name="document-section"></a>

                <h2>Document</h2>
                <pre><code id="document" class="javascript"></code></pre>


                <a name="demographics-section"></a>

                <h2>Demographics</h2>
                <pre><code id="demographics" class="javascript"></code></pre>


                <a name="allergies-section"></a>

                <h2>Allergies</h2>
                <pre><code id="allergies" class="javascript"></code></pre>


                <a name="careplan-section"></a>

                <h2>Care Plan</h2>
                <pre><code id="careplan" class="javascript"></code></pre>


                <a name="chiefcomplaint-section"></a>

                <h2>Chief Complaint</h2>
                <pre><code id="chiefcomplaint" class="javascript"></code></pre>


                <a name="encounters-section"></a>

                <h2>Encounters</h2>
                <pre><code id="encounters" class="javascript"></code></pre>


                <a name="functionalstatus-section"></a>

                <h2>Functional Status</h2>
                <pre><code id="functionalstatus" class="javascript"></code></pre>


                <a name="immunizations-section"></a>

                <h2>Immunizations</h2>
                <pre><code id="immunizations" class="javascript"></code></pre>


                <a name="immunizationdeclines-section"></a>

                <h2>Declined Immunizations</h2>
                <pre><code id="immunizationdeclines" class="javascript"></code></pre>


                <a name="instructions-section"></a>

                <h2>Patient Instructions</h2>
                <pre><code id="instructions" class="javascript"></code></pre>


                <a name="medications-section"></a>

                <h2>Medications</h2>
                <pre><code id="medications" class="javascript"></code></pre>


                <a name="problems-section"></a>

                <h2>Problems</h2>
                <pre><code id="problems" class="javascript"></code></pre>


                <a name="procedures-section"></a>

                <h2>Procedures</h2>
                <pre><code id="procedures" class="javascript"></code></pre>


                <a name="results-section"></a>

                <h2>Results (Labs)</h2>
                <pre><code id="results" class="javascript"></code></pre>


                <a name="smokingstatus-section"></a>

                <h2>Smoking Status</h2>
                <pre><code id="smokingstatus" class="javascript"></code></pre>


                <a name="vitals-section"></a>

                <h2>Vitals</h2>
                <pre><code id="vitals" class="javascript"></code></pre>

            </div>
        </div>
    </div>



    <script>
        var baseURL = "";
        var xml, bb;
        var doc = document.getElementById('document');
        var demographics = document.getElementById('demographics');
        var allergies = document.getElementById('allergies');
        var carePlan = document.getElementById('careplan');
        var chiefComplaint = document.getElementById('chiefcomplaint');
        var encounters = document.getElementById('encounters');
        var functionalStatus = document.getElementById('functionalstatus');
        var immunizations = document.getElementById('immunizations');
        var immunizationDeclines = document.getElementById('immunizationdeclines');
        var instructions = document.getElementById('instructions');
        var results = document.getElementById('results');
        var medications = document.getElementById('medications');
        var problems = document.getElementById('problems');
        var procedures = document.getElementById('procedures');
        var smokingStatus = document.getElementById('smokingstatus');
        var vitals = document.getElementById('vitals');

        function hl(src) {
            return hljs.highlight('javascript', src).value
        }

        function load(kind) {
            var xhReq = new XMLHttpRequest();

            var url;
            switch (kind) {
                case 'well_track_one_ccd':
                    url = 'http://michalisantoniou.com/CLH/well-track-one-ccd.xml';
                    break;
            }

            xhReq.open('GET', url, false);
            xhReq.send(null);
            var xml = xhReq.responseText;

            // TODO: Replace '\t' in xml with '  '
            xml = xml.replace(/\t/g, '  ');

            clearAll();
            document.getElementById('xml').value = xml;
            convert();
        }

        function clearAll() {
            clearXML();
            clearJSON();
        }

        function clearXML() {
            document.getElementById('xml').value = '';
        }
        function clearJSON() {
            var els = document.getElementsByTagName('code');

            // i = 1 so it doesn't clear the sample usage example
            for (var i = 1; i < els.length; i++) {
                els[i].innerHTML = '';
            }
            ;

            bb = null;
        }

        function convert() {
            clearJSON();
            xml = document.getElementById('xml').value;
            bb = BlueButton(xml);

            doc.innerHTML = bb.data.document.json();
            demographics.innerHTML = bb.data.demographics.json();
            allergies.innerHTML = bb.data.allergies.json();
            carePlan.innerHTML = bb.data.care_plan.json();
            chiefComplaint.innerHTML = bb.data.chief_complaint.json();
            encounters.innerHTML = bb.data.encounters.json();
            functionalStatus.innerHTML = bb.data.functional_statuses.json();
            immunizations.innerHTML = bb.data.immunizations.json();
            immunizationDeclines.innerHTML = bb.data.immunization_declines.json();
            instructions.innerHTML = bb.data.instructions.json();
            results.innerHTML = bb.data.results.json();
            medications.innerHTML = bb.data.medications.json();
            problems.innerHTML = bb.data.problems.json();
            procedures.innerHTML = bb.data.procedures.json();
            smokingStatus.innerHTML = bb.data.smoking_status.json();
            vitals.innerHTML = bb.data.vitals.json();
        }
    </script>

    <script>
        document.onload = convert();
    </script>

    </body>
    </html>
@stop
