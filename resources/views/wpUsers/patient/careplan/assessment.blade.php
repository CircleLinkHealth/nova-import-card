@extends('partials.providerUI')

<?php
    $today = \Carbon\Carbon::now()->toFormattedDateString();
?>

@section('title', 'Care Plan Assessment')
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
                        <h1 class="color-blue">G0506 Template Form for MD</h1>
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12">
                                <form name="questionnaire-form">
                                    <div class="text-right">
                                        <input type="button" name="skip" class="btn btn-warning font-24" value="Skip">
                                    </div>
                                    <div id="questionnaire-app"></div>
                                    <button class="btn btn-success font-24">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @include('partials.confirm-modal')
            </div>
        </section>
    </div>
    @push('scripts')
        <script type="application/json" id="questions-script">
            <?php 
                include app_path() . '/../public/data/ccm-eligibility-questions.json';
            ?>
        </script>
        <script>
            var questions = JSON.parse(document.getElementById('questions-script').innerHTML);
        </script>
        <script src="{{asset('compiled/js/v-questionnaire.min.js')}}"></script>
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
                        body: 'Advanced care planning by MD during visit is needed to bill the G0506 code.',
                        confirmText: 'Skip',
                        cancelText: 'Go Back',
                        neverShow: true
                    }).then(function (obj) {
                        console.log(obj)
                    })
                })
            })()
        </script>
    @endpush
@endsection