@extends('partials.adminUI')

@section('content')
    <div id="trainer-results" class="container-fluid">

        <h1>Hola, human.</h1>
        <h1>Here's what I see as features to help me identify future CCDs from this Practice. Please check off
            irrelevant information. Information such as 'athenahealth' is too broad, so it should not be saved.</h1>

        <form class="form-group" action="{{route('post.store.training.features')}}" method="POST">

            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            @if(isset($importedMedicalRecord))
                <input type="hidden" name="imported_medical_record_id" value="{{ $importedMedicalRecord->id }}">
            @endif

            @if(isset($importedMedicalRecords))
                @foreach($importedMedicalRecords as $importedMedicalRecord)
                    <input type="hidden" name="imported_medical_record_ids[]" value="{{ $importedMedicalRecord->id }}">
                @endforeach
            @endif

            @if($document)
                <div class="row">
                    <div class="col-lg-12">
                        <h1>Custodian</h1>

                        <div class="input-group">
                    <span class="input-group-addon">
                      <input type="checkbox" name="documentId" value="{{$document->id}}" aria-label="...">
                    </span>
                            <p class="form-control" aria-label="...">{{$document->custodian}}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($providers)
                <div class="row">
                    <div class="col-lg-12">
                        <h1>Providers and Addresses</h1>
                        @foreach($providers as $provider)

                            <div class="input-group">
                    <span class="input-group-addon">
                      <input type="checkbox" name="providerIds[]" value="{{$provider->id}}" aria-label="...">
                    </span>
                                <p class="form-control" aria-label="...">{{$provider->first_name}}
                                    , {{$provider->last_name}}, {{$provider->street}}, {{$provider->city}}
                                    , {{$provider->state}}, {{$provider->zip}}, {{$provider->cell_phone}}
                                    , {{$provider->home_phone}}, {{$provider->work_phone}} </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <h1>Practice</h1>

                    <select v-model="practice" class="col-md-12" name="practiceId">
                        <option v-for="p in practices" v-bind:value="p.id">@{{ p.display_name }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h1>Location</h1>

                    <select v-model="location" class="col-md-12" name="locationId">
                        <option v-for="l in locations" v-bind:value="l.id">@{{ l.name }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <h1>Billing Provider</h1>

                    <select v-model="billingProvider" class="col-md-12" name="billingProviderId">
                        <option v-for="prov in providers"
                                v-bind:value="prov.id">@{{ prov.first_name }} @{{ prov.last_name }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <input class="btn-success" type="submit" value="Done!">
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="/js/importer-training.js"></script>
@endsection