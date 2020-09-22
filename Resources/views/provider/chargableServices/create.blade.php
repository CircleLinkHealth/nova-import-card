@extends('provider.layouts.dashboard')

@section('title', 'Chargeable Services')

@section('module')


    @include('core::partials.errors.materialize-errors')


    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.chargeable-services', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id'=>'practice-chargeable-services-form']) !!}

            <div class="row">
                @foreach($chargeableServices as $service)
                    <div class="col s12 m6">
                        <div class="card grey lighten-4">
                            <div class="card-content blue-gray-text">
                                <span class="card-title">{{$service->code}}</span>
                                <p>{{$service->description}}.</p>
                            </div>
                            <div class="card-action" style="padding: 4px 20px 0 20px;">
                                <div class="row">
                                    <div class="input-field col s8">
                                        <input name="chargeable_services[{{$service->id}}][is_on]" type="checkbox"
                                               id="service-{{$service->id}}"
                                               value="1"
                                        @if($service->code === \CircleLinkHealth\Customer\Entities\ChargeableService::CCM_PLUS_40){!!'class="ccm_plus_40"'!!}@endif
                                        @if($service->code === \CircleLinkHealth\Customer\Entities\ChargeableService::CCM_PLUS_60){!!'class="ccm_plus_60"'!!}@endif
                                        @if($service->is_on){{'checked'}}@endif
                                        @if(!auth()->user()->hasPermission('chargeableService.create')){{'disabled'}}@endif>
                                        <label for="service-{{$service->id}}">Active</label>
                                    </div>

                                    <div class="input-field col s4">
                                        <input id="service-{{$service->id}}-amount"
                                               name="chargeable_services[{{$service->id}}][amount]" type="text"
                                               class="validate" value="{{$service->amount}}"
                                        @if(!auth()->user()->hasPermission('chargeableService.create')){{'disabled'}}@endif>
                                        <label for="service-{{$service->id}}-amount" data-error="required"
                                               data-success="">Amount ($)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            @if(auth()->user()->hasPermission('chargeableService.create'))
                <button type="submit"
                        form="practice-chargeable-services-form"
                        class="btn blue waves-effect waves-light col s4"
                        onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
                    Update Preferences
                </button>
            @endif

            {!! Form::close() !!}

        </div>
        @endsection

        @push('scripts')
            <script>
                $(document).ready(function () {
                    $('select').material_select();

                    const ccmPlus40Elem = $('.ccm_plus_40');
                    const ccmPlus60Elem = $('.ccm_plus_60');

                    ccmPlus40Elem.click(function (e) {
                        ccmPlus60Elem.prop('checked', e.currentTarget.checked);
                    });

                    ccmPlus60Elem.click(function (e) {
                        ccmPlus40Elem.prop('checked', e.currentTarget.checked);
                    });
                });
            </script>
    @endpush