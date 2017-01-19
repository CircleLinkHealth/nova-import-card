@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">

        <h1>Hola, human.</h1>
        <h1>Here's what I found. Please check off the information I should forget. I need specific information
            that identifies a practice, location and billing provider. Information such as 'athenahealth' is considered
            too broad, so it should not be saved.</h1>

        @if($document)
            <div class="row">
                <div class="col-lg-12">
                    <h1>Custodian</h1>

                    <div class="input-group">
                    <span class="input-group-addon">
                      <input type="checkbox" name="custodian" aria-label="...">
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
                      <input type="checkbox" name="custodian" aria-label="...">
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

                <select v-model="practice" class="col-md-12">
                    <option v-for="p in practices" v-bind:value="p.id">@{{ p.display_name }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <h1>Location</h1>

                <select v-model="location" class="col-md-12">
                    <option v-for="l in locations" v-bind:value="l.id">@{{ l.name }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <h1>Billing Provider</h1>

                <select v-model="billingProvider" class="col-md-12">
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
    </div>
@endsection

@section('javascript')
    <script src="/js/importer-training.js"></script>
@endsection