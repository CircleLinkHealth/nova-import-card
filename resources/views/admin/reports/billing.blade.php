@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .select2-container {
                width: 300px !important;
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

                        {{--<div id="showOther" class="form-group" style="display:none">--}}
                            {{--<label for="otherProblem">If other, please specify</label>--}}
                            {{--<input class="form-control" name="otherProblem" id="otherProblem">--}}
                        {{--</div>--}}

                        {{--<div class="form-group">--}}
                            {{--<label for="code">Problem ICD10 Code</label>--}}
                            {{--<input class="form-control" name="code" id="code">--}}
                        {{--</div>--}}

                        <input type="hidden" id="report_id" name="report_id">
                        <input type="hidden" id="problem_no" name="problem_no">
                        {{--<input type="hidden" id="has_problem" name="has_problem">--}}
                        {{--<input type="hidden" id="modal_date" name="modal_date">--}}
                        {{--<input type="hidden" id="modal_practice_id" name="modal_practice_id">--}}
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


    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Approve Billable Patients
                            </div>
                            <?php 
                                function getPractice($practice) {
                                    return [
                                        'id' => $practice->id,
                                        'display_name' => $practice->display_name
                                    ];
                                }
                            ?>
                            @push('styles')
                                <script>
                                    var practices = JSON.parse("{{json_encode($practices)}}".replace(/\&quot;/g, '"'))
                                </script>

                            @endpush
                            <billing-report></billing-report>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>
@stop