@extends('partials.adminUI')
@section('content')
        <div style="position: fixed;bottom: 0;width: 100%;z-index: 100;opacity: 0.95;">
            @include('errors.errors')
        </div>

        <div class="container">
            @foreach($data as $user)
              {{--  <div id="nurse-{{$user->nurseInfo->id}}" class="row" style="padding-bottom: 10%;">
                    <h3>
                        <b>{{ $user->getFullName() }}</b>
                        <span class="pull-right red-text">Timezone: {{ $user->timezone ? $user->timezone : 'Not set' }}</span>
                    </h3>

                    <div class="row">
                        @include('partials.care-center.work-schedule-slot.admin-store', [ 'nurseInfo' => $user->nurseInfo ])
                    </div>

                    @if($user->nurseInfo->windows->count() > 0)
                        <h4>Existing Windows</h4>
                    @else
                        <h4>This nurse does not have any windows.</h4>
                    @endif

                    <div class="row">
                        <div class="row">
                            <h3>{{$user->getFullName()}}'s Schedule</h3>
                        </div>
                        <div class="col-md-12">
                            @include('partials.care-center.work-schedule-slot.index', [
                                'windows' => $user->nurseInfo->windows,
                                'holidaysThisWeek' => $user->nurseInfo->holidays_this_week,
                                'nurse' => $user->nurseInfo
                            ])
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <h3>{{$user->getFullName()}}'s Days Off</h3>
                            </div>
                            @include('partials.care-center.holiday-schedule.index', [
                                'holidays' => $user->nurseInfo->upcoming_holiday_dates
                            ])
                        </div>
                    </div>
                </div>--}}

              <!-- Button trigger modal -->
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                      Launch demo modal
                  </button>

                  <!-- Modal -->
                  <div class="modal fade-xl" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalCenterTitle">Modal title</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                  </button>
                              </div>
                              <div class="modal-body">
                                  <h3>
                                      <b>{{ $user->getFullName() }}</b>
                                      <span class="pull-right red-text">Timezone: {{ $user->timezone ? $user->timezone : 'Not set' }}</span>
                                  </h3>

                                  <div class="row">
                                      @include('partials.care-center.work-schedule-slot.admin-store', [ 'nurseInfo' => $user->nurseInfo ])
                                  </div>

                                  @if($user->nurseInfo->windows->count() > 0)
                                      <h4>Existing Windows</h4>
                                  @else
                                      <h4>This nurse does not have any windows.</h4>
                                  @endif

                                  <div class="row">
                                      <div class="row">
                                          <h3>{{$user->getFullName()}}'s Schedule</h3>
                                      </div>
                                      <div class="col-md-12">
                                          @include('partials.care-center.work-schedule-slot.index', [
                                              'windows' => $user->nurseInfo->windows,
                                              'holidaysThisWeek' => $user->nurseInfo->holidays_this_week,
                                              'nurse' => $user->nurseInfo
                                          ])
                                      </div>
                                  </div>

                                  <div class="row">
                                      <div class="col-md-12">
                                          <div class="row">
                                              <h3>{{$user->getFullName()}}'s Days Off</h3>
                                          </div>
                                          @include('partials.care-center.holiday-schedule.index', [
                                              'holidays' => $user->nurseInfo->upcoming_holiday_dates
                                          ])
                                      </div>
                                  </div>
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                 {{-- <button type="button" class="btn btn-primary">Save changes</button>--}}
                              </div>
                          </div>
                      </div>
                  </div>
                @endforeach
        </div>
@endsection

@push('scripts')
    @if(session('editedNurseId'))
        <script>
            (function () {
                window.location.hash = '#nurse-{{session('editedNurseId')}}';
            })();
        </script>
    @endif
@endpush