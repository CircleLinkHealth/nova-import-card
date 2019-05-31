@extends('partials.providerUI')

@section('title', 'reviewInvoice')
@section('activity', 'reviewInvoice')

@section('content')
    <div class="container" style="padding-bottom: 10%;">
        @if ($shouldShowDisputeForm)
            <div class="row">
                <div class="col-md-12">
                    <h4 class="pull-right alert alert-warning">Invoices auto-approve unless disputed by
                        the {{$disputeDeadline->format('jS')}} of the
                        month at
                        {{$disputeDeadline->format('h:iA T')}}.</h4>
                </div>
            </div>
        @endif

        <span class="pull-right"> <a href="javascript:void(0);"
                                     onclick="javascript:introJs().setOption('showProgress', true).start();"><span
                        class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a></span>

        <div class="row">
            <div class="col-md-12">
                @empty($invoice->id)
                    <div class="alert alert-default" style="margin-top: 50px;">
                        <h4>Your invoice is not ready yet. You will receive an email once it has been generated.</h4>
                    </div>
                @else
                    <div data-step="1"
                         data-intro="This is your last month's invoice. It is generated monthly, on the first day of the month. You will receive an email as soon as it is created with a link to this page. You will have a few days to approve or dispute the invoice. Click 'Next' to find out more.">
                    @include('nurseinvoices::invoice-v2')
                    </div>
                @endempty
            </div>
        </div>

        @if ($shouldShowDisputeForm)
            <div class="row">
                <div class="col-md-12">
                    <h4 class="pull-right alert alert-warning">Invoices auto-approve unless disputed by
                        the {{$disputeDeadline->format('jS')}} of the
                        month at
                        {{$disputeDeadline->format('h:iA T')}}.</h4>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                @isset($dispute)
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
                @else
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
                @endisset
            </div>
        </div>
    </div>

    <script type="text/javascript"
            src="https://circlelinkhealth.atlassian.net/s/d41d8cd98f00b204e9800998ecf8427e-T/-t2ekke/b/11/a44af77267a987a660377e5c46e0fb64/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?locale=en-US&collectorId=721240a8"></script>
@endsection
