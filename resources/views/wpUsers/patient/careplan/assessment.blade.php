@extends('partials.providerUI')

<?php

$today = \Carbon\Carbon::now()->toFormattedDateString();
?>

@section('title', 'G0506 Enrollment Assessment')
@section('activity', 'Care Plan Assessment')

@push('styles')
    <style>
        .font-24 {
            font-size: 24px;
        }

        .top-20 {
            margin-top: 20px;
        }

        .color-blue {
            color: #109ace;
        }

        .bg-blue {
            background-color: #109ace;
        }

        input[type='radio'], input[type='checkbox'] {
            display: inline;
        }

        .questionnaire .question-text {
            line-height: 50px;
        }

        .questionnaire .question-option {
            padding: 10px;
        }

        .questionnaire .form-group {
            margin-bottom: 30px;
        }

        .modal-body {
            line-height: 40px;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <section class="patient-summary">
            <div class="patient-info__main">
                <div class="row">
                    <div class="col-xs-12 top-20">
                        <h1 class="color-blue">G0506 Enrollment Assessment</h1>
                        @if ($assessment) 
                            <h4 class="text-right">Performed on {{Carbon::parse($assessment->updated_at)->format('m/d/Y')}} at 
                                {{$assessment->updated_at->setTimezone($patient->timezone ?? 'America/New_York')->format('g:i A T')}} 
                                by {{$approver->display_name}}</h4>
                        @endif
                        
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12">
                                <form method="post" name="questionnaire-form">
                                    @if ($editable) 
                                        <div class="text-right">
                                            <input type="button" name="skip" class="btn btn-warning font-24" value="Skip">
                                        </div>
                                    @endif
                                    <div>
                                        <questionnaire-app ref="questionnaireApp" :questions="questions" class-name="questionnaire" :editable="{{$editable ? 'true' : 'false'}}"></questionnaire-app>
                                    </div>
                                    <div id="questionnaire-app"></div>
                                    {{ csrf_field() }}
                                    <input type="hidden" name="careplan_id" value="{{$patient->id}}" />
                                    <input type="hidden" name="provider_approver_id" value="{{Auth::user()->id}}" />
                                    <button class="btn btn-success font-24" {{ !$editable ? 'disabled' : '' }}>Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @include('partials.confirm-modal')
            </div>
        </section>
    </div>
    @push('styles')
        <script type="application/json" id="questions-script">
            <?php
                include app_path().'/../public/data/ccm-eligibility-questions.json';
            ?>
        </script>
        <script type="application/json" id="assessment-script">
            <?php
                echo json_encode($assessment);
            ?>
        </script>
        <script>
            var questions = JSON.parse(document.getElementById('questions-script').innerHTML);
            var answers = JSON.parse(document.getElementById('assessment-script').innerHTML);
        </script>
    @endpush
    @push('scripts')
        
        <script>
            (function ($) {
                $.fn.serializeObject = function () {
                    return $(this).serializeArray().reduce(function (obj, x) {
                                if ((!obj[x.name]) && obj[x.name] != '') obj[x.name] = x.value;
                                else {
                                    if (Array.isArray(obj[x.name])) {
                                        obj[x.name] = obj[x.name].concat(x.value);
                                    }
                                    else {
                                        obj[x.name] = (new Array((obj[x.name] || "").toString())).concat(x.value);
                                    }
                                }
                                return obj;
                            }, {})
                }
            })(jQuery);

            (function () {
                var $form = $("[name='questionnaire-form']");
                var $skipBtn = $form.find("[name='skip']");

                $skipBtn.click(function () {
                    $.showConfirmModal({
                        title: 'Are you sure you want to skip?',
                        body: 'Advanced care planning by MD during OR after visit is needed to bill the G0506 code.',
                        confirmText: 'Skip',
                        cancelText: 'Go Back',
                        neverShow: true,
                        name: 'skip-assessment'
                    }).then(function (obj) {
                        if (obj.action) {
                            location.href = "{{url('manage-patients/' . $patient->id . '/view-careplan?skippedAssessment')}}"
                        }
                        console.log(obj)
                    })
                })
            })()
        </script>
    @endpush
@endsection