@if( isset($revisions) )
    @if($revisions->count() > 0)
        <ul class="list-group">
            <h2>Revision History</h2>
            @foreach($revisions->take(5) as $history)
                <li class="list-group-item">
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
                </li>
            @endforeach
        </ul>
    @endif
@endif