@extends('partials.providerUI')

@section('title', 'Review Invoice')
@section('activity', 'Review Invoice')

@push('scripts')
    <script>
        function startIntro() {
            let intro = introJs();

            intro.setOptions({
                showProgress: true,
                showBullets: false
            }).onbeforechange(function () {
                if (this._currentStep === 4) {
                    let el = document.getElementById('toggle-invoice-dispute-form');

                    if (el && el.innerText.includes('Show')) {
                        el.click();

                    }
                }
            });
            intro.start();
        }
    </script>
@endpush

@section('content')
    <div class="container" style="padding-bottom: 10%;">
        @include('nurseinvoices::dispute-deadline-warning')

        @if($monthInvoiceMap->isNotEmpty() && !auth()->user()->isAdmin())
            <div class="row" style="padding-top: 20px;">
                <div class="col-md-12">
                    <div class="pull-right">
                        <form method="post" action="{{route('nurseinvoices.show')}}" class="form-inline">
                            {{csrf_field()}}

                            <div class="form-group">
                                <select name="invoice_id"
                                        class="form-control dropdown select2 inline-block invoice-month-dropdown">
                                    @foreach($monthInvoiceMap as $id => $month)
                                        <option value="{{$id}}" {{$id === $invoiceId ? 'selected' : ''}}>{{$month->format('F Y')}}</option>
                                    @endforeach
                                </select>

                                <input type="submit" class="btn btn-info inline-block" value="View Invoice"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($shouldShowDisputeForm)
                <div class="tutorial-button">
                    <a href="javascript:void(0);" onclick="startIntro();">
                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                    </a>
                </div>
            @endif
        @endif

        <div class="row">
            <div class="col-md-12">
                @empty($invoice->id)
                    <div class="alert alert-default" style="margin-top: 50px;">
                        <h4>Your invoice is not ready yet. You will receive an email once it has been generated.</h4>
                    </div>
                @else
                    <div data-step="1"
                         data-intro="This is your last month's invoice. It is generated monthly, on the first day of the month. You will receive an email as soon as it is created with a link to this page. You will have a few days to approve or dispute the invoice. Click 'Next' to find out more.">
                        @include('nurseinvoices::invoice-v3')
                    </div>
                @endempty
            </div>
        </div>

        @include('nurseinvoices::dispute-deadline-warning')

        <div class="row">
            <div class="col-md-12">
                @isset($disputes)
                    @foreach($disputes as $dispute)
                        <div class="col-md-12 alert alert-{{$dispute->is_resolved ? 'success' : 'warning'}}">
                            <div class="col-md-12">
                                <h3>Dispute Status: <b>{{$dispute->is_resolved ? 'Resolved' : 'Open'}}</b></h3>
                            </div>

                            <div class="col-md-12">
                                <h5>Your Message:</h5>
                            </div>
                            <div class="col-md-12">
                                <p>{{$dispute->reason}}</p>
                            </div>
                            <hr>
                            @if($dispute->is_resolved)
                                <div class="col-md-12">
                                    <h5>CLH Message:</h5>
                                </div>
                                <div class="col-md-12">
                                    <p>{{$dispute->resolution_note}}</p>
                                    <p>written by {{optional($dispute->resolver)->getFullName()}}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endisset

                @if ($shouldShowDisputeForm)
                    <dispute-nurse-invoice invoice-id="{{$invoiceId}}"></dispute-nurse-invoice>
                @elseif ($invoice->is_nurse_approved)
                    <div class="row">
                        <div class="col-md-12 alert alert-success">
                            <h4>You approved this invoice
                                on {{presentDate($invoice->nurse_approved_at->setTimezone(auth()->user()->timezone), true, true, true)}}</h4>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .tutorial-button {
            font-size: 70px;
            position: fixed;
            bottom: 40px;
            right: 40px;
        }

        .invoice-month-dropdown {
            margin-right: 15px;
            min-width: 140px;
        }
    </style>
@endpush
