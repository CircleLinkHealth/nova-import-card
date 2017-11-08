@extends('partials.adminUI')

@section('content')
    <div id="trainer-results" class="container-fluid">
        <div class="row">
            <div class="col-md-6 text-center">
                <img class="col-md-12" src="{{asset('/img/robo-gif.gif')}}" alt="Hola, human.">
            </div>
            <div class="col-md-6">
                <h2>Here's what I see as features to help me identify future CCDs from this Practice.</h2>
                <h3>
                    <strong>Help me by checking off any of the fields below that could apply to more than one
                        Practice/Location/Provider, such as EHR Names ('athenahealth', 'epic').</strong>
                </h3>
                <br>
                @if(!empty($medicalRecordId))
                    <h4>Here's <a href="{{ route('get.CCDViewerController.show', ['ccdaId' => $medicalRecordId]) }}"
                                  class="btn btn-warning btn-xs"
                                  target="_blank">
                            the CCDA
                        </a> in case you need it.
                    </h4>
                @endif
            </div>
        </div>

        <form class="form-group" action="{{route('post.store.training.features')}}" method="POST">

            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <importer-trainer
                    practice-prop="{{$predictedPracticeId ?? null}}"
                    location-prop="{{$predictedLocationId ?? null}}"
                    billing-provider-prop="{{$predictedBillingProviderId ?? null}}"
            >
            </importer-trainer>

            @if(isset($importedMedicalRecord))
                <input type="hidden" name="imported_medical_record_id" value="{{ $importedMedicalRecord->id }}">
            @endif

            @if(isset($importedMedicalRecords))
                @foreach($importedMedicalRecords as $importedMedicalRecord)
                    <input type="hidden" name="imported_medical_record_ids[]" value="{{ $importedMedicalRecord->id }}">
                @endforeach
            @endif

            <div class="row">
                @if($document)
                    <div class="col-xs-6">
                        <h3>Custodian</h3>

                        <div class="input-group">
                    <span class="input-group-addon">
                      <input type="checkbox" name="documentId" value="{{$document->id}}" aria-label="...">
                    </span>
                            <p class="form-control" aria-label="...">{{$document->custodian}}</p>
                        </div>
                    </div>
                @endif

                @if($providers)
                    <div class="col-xs-6">
                        <h3>Providers and Addresses</h3>
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
                @endif
            </div>

            <div class="row text-center">
                <br>
                <br>
                <div class="col-md-12">
                    <input class="btn-danger btn btn-lg" type="submit" value="Done!">
                    <span style="border-bottom: 5px solid red;color: blue;">
                            WARNING! When you click Done, all the rows you checked off on "Custodian" and "Providers and Addresses" above will be deleted.
                    </span>
                </div>
            </div>

        </form>
    </div>
@endsection