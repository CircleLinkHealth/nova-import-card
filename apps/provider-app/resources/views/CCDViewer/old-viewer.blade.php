@extends('layouts.no-menu')

@include('footer')

@section('content')
    <div class="container">
        <div class="content">

            <div class="page-content">

                <h1 class="text-center">Raw CCD Viewer</h1>

                <nav id="fixed-ccd-viewer-menu" class="col-md-10">
                    <a href="#document-section">Document</a> ||
                    <a href="#demographics-section">Demographics</a> ||
                    <a href="#allergies-section">Allergies</a> ||
                    <a href="#careplan-section">Care Plan</a> ||
                    <a href="#chiefcomplaint-section">Chief Complaint</a> ||
                    <a href="#encounters-section">Encounters</a> ||
                    <a href="#functionalstatus-section">Functional Status</a> ||
                    <a href="#immunizations-section">Immunizations</a> ||
                    <br>
                    <a href="#immunizationdeclines-section">Declined Immunizations</a> ||
                    <a href="#instructions-section">Patient Instructions</a> ||
                    <a href="#medications-section">Medications</a> ||
                    <a href="#payers-section">Payers</a> ||
                    <a href="#problems-section">Problems</a> ||
                    <a href="#procedures-section">Procedures</a> ||
                    <a href="#results-section">Results (Labs)</a> ||
                    <a href="#smokingstatus-section">Smoking Status</a> ||
                    <a href="#vitals-section">Vitals</a>
                </nav>

                <a name="document-section"></a>

                <h2>Document</h2>
                <pre>
                    {!! json_encode($ccd->document, JSON_PRETTY_PRINT) !!}
                </pre>


                <a name="demographics-section"></a>

                <h2>Demographics</h2>
                <pre><code id="demographics" class="javascript">
                        {!! json_encode($ccd->demographics, JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="allergies-section"></a>

                <h2>Allergies</h2>
                <pre><code id="allergies" class="javascript">
                        {!! json_encode($ccd->allergies, JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="careplan-section"></a>

                <h2>Care Plan</h2>
                <pre><code id="careplan" class="javascript">
                        {!! json_encode($ccd->care_plan ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="chiefcomplaint-section"></a>

                <h2>Chief Complaint</h2>
                <pre><code id="chiefcomplaint" class="javascript">
                        {!! json_encode($ccd->chief_complaint ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="encounters-section"></a>

                <h2>Encounters</h2>
                <pre><code id="encounters" class="javascript">
                        {!! json_encode($ccd->encounters ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="functionalstatus-section"></a>

                <h2>Functional Status</h2>
                <pre><code id="functionalstatus" class="javascript">
                        {!! json_encode($ccd->functional_statuses ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="immunizations-section"></a>

                <h2>Immunizations</h2>
                <pre><code id="immunizations" class="javascript">
                        {!! json_encode($ccd->immunizations ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="immunizationdeclines-section"></a>

                <h2>Declined Immunizations</h2>
                <pre><code id="immunizationdeclines" class="javascript">
                        {!! json_encode($ccd->immunizations ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="instructions-section"></a>

                <h2>Patient Instructions</h2>
                <pre><code id="instructions" class="javascript">
                        {!! json_encode($ccd->instructions ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="medications-section"></a>

                <h2>Medications</h2>
                <pre><code id="medications" class="javascript">
                        {!! json_encode($ccd->medications ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="payers-section"></a>

                <h2>Payers</h2>
                <pre><code id="payers" class="javascript">
                        @if(isset($ccd->payers))
                            {!! json_encode($ccd->payers ?? [], JSON_PRETTY_PRINT) !!}
                        @endif
                    </code></pre>


                <a name="problems-section"></a>

                <h2>Problems</h2>
                <pre><code id="problems" class="javascript">
                        {!! json_encode($ccd->problems ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="procedures-section"></a>

                <h2>Procedures</h2>
                <pre><code id="procedures" class="javascript">
                        {!! json_encode($ccd->procedures ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="results-section"></a>

                <h2>Results (Labs)</h2>
                <pre><code id="results" class="javascript">
                        {!! json_encode($ccd->results ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="smokingstatus-section"></a>

                <h2>Smoking Status</h2>
                <pre><code id="smokingstatus" class="javascript">
                        {!! json_encode($ccd->smoking_status ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>


                <a name="vitals-section"></a>

                <h2>Vitals</h2>
                <pre><code id="vitals" class="javascript">
                        {!! json_encode($ccd->vitals ?? [], JSON_PRETTY_PRINT) !!}
                    </code></pre>
            </div>
        </div>
    </div>

    </body>
    </html>
@stop
