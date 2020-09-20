@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Create Activity </div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')

                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('ActivityController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <?php $activity_types = [
                                'General (Clinical)'                      => 'General (Clinical)',
                                'Medication Reconciliation'               => 'Medication Reconciliation',
                                'Appointments'                            => 'Appointments',
                                'Test (Scheduling, Communications, etc)'  => 'Test (Scheduling, Communications, etc)',
                                'Call to Other Care Team Member'          => 'Call to Other Care Team Member',
                                'Review Care Plan'                        => 'Review Care Plan',
                                'Review Patient Progress'                 => 'Review Patient Progress',
                                'Transitional Care Management Activities' => 'Transitional Care Management Activities',
                                'Other'                                   => 'Other',
                            ]; ?>

                                <div class="col-md-6">
                                    <select name="parent_id">
                                        <option value="">None</option>
                                        @foreach( $activity_types as $id => $type )
                                            <option value="{{ $id }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Add Activity
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
