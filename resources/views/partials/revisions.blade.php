@if( isset($revisions) )
    @if($revisions->count() > 0)
        <h2>Revision History</h2>
        <div class="panel-group" id="accordion">
            @foreach($revisions->take(20) as $history)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseRev{{ $history->id }}">
                                @if($history->key == 'created_at' && !$history->old_value)
                                    @if($history->userResponsible())
                                        {{ $history->userResponsible()->user_login }}[{{ $history->userResponsible()->ID }}]
                                    @else
                                        Unknown user [id={{ $history->user_id }}]
                                    @endif
                                    created new resource {{ $history->revisionable_type }}[{{ $history->revisionable_id }}] on <em>{{ $history->created_at }}</em>
                                @else
                                    @if($history->userResponsible())
                                        {{ $history->userResponsible()->user_login }}[{{ $history->userResponsible()->ID }}] changed {{ $history->fieldName() }} from <strong>{{ (strlen($history->oldValue()) > 30) ? substr($history->oldValue(),0,10).'...' : $history->oldValue() }}</strong> to <strong>{{ (strlen($history->newValue()) > 13) ? substr($history->newValue(),0,10).'...' : $history->newValue() }}</strong> on <em>{{ $history->created_at }}</em>
                                    @else
                                        <em>{{ empty($history->user_id) ? 'SYSTEM' : 'Unknown User [id='.$history->user_id.']' }}</em> changed {{ $history->fieldName() }} from <strong>{{ (strlen($history->oldValue()) > 30) ? substr($history->oldValue(),0,10).'...' : $history->oldValue() }}</strong> to <strong>{{ (strlen($history->newValue()) > 13) ? substr($history->newValue(),0,10).'...' : $history->newValue() }}</strong> on <em>{{ $history->created_at }}</em>
                                    @endif
                                @endif
                            </a>
                        </h4>
                    </div>
                    <div id="collapseRev{{ $history->id }}" class="panel-collapse collapse">
                        <div class="panel-body">
                            <h4>Before:</h4><strong>{{ (strlen($history->oldValue()) > 30) ? substr($history->oldValue(),0,1000).'...' : $history->oldValue() }}</strong>
                            <h4>After:</h4><strong>{{ (strlen($history->newValue()) > 13) ? substr($history->newValue(),0,1000).'...' : $history->newValue() }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <a ref="text-sm">see all</a>
    @endif
@endif