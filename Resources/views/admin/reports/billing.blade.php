@extends('partials.providerUI')

@section('title', 'Approve Billable Patients')
@section('activity', 'Approve Billable Patients')

@section('content')
    @push('styles')
        <style>
            .select2-container {
                width: 300px !important;
            }

            tr.bg-flagged {
                background-color: #ffff0052 !important;
            }
        </style>

        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <!-- Modal -->
    <div class="modal fade" id="problemPicker" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title" id="problem-modal-title">
                        Select Eligible Problem for <span id="patientName"></span>
                    </h4>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form name="problem_form" id="problem_form">
                        <div class="form-group">
                            <label for="ccd_problem_id">Eligible Problems</label>
                            <select class="form-control"
                                    id="ccd_problem_id" name="ccd_problem_id">

                            </select>
                        </div>

                        <input type="hidden" id="report_id" name="report_id">
                        <input type="hidden" id="problem_no" name="problem_no">
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="confirm_problem" class="btn btn-primary">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid" style="padding-top: 50px;">
        <div class="row">
            <div class="col-md-12">
                @include('core::partials.errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        function getPractice($practice)
                        {
                            return [
                                'id'           => $practice->id,
                                'display_name' => $practice->display_name,
                            ];
                        }
                        ?>
                        @push('styles')
                            <script>
                                function decodeHTML(html) {
                                    const div = document.createElement('div')
                                    div.innerHTML = html
                                    return div.innerText
                                }

                                const practices = @json($practices);
                                const cpmProblems = @json($cpmProblems).map(function (problem) {
                                    problem.name = decodeHTML(problem.name);
                                    return problem
                                });

                                const chargeableServices = @json($chargeableServices);

                                const dates = @json($dates);

                                const isSoftwareOnly = @json(auth()->user()->isSoftwareOnly());
                            </script>
                        @endpush
                        <billing-report ref="billingReport"></billing-report>
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop