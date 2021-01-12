<div id="insurance-policies"
     class="form-group form-item form-item-spacing col-sm-12">

    <h4 id="policies-title" class="form-title">Insurance Policies</h4>

    <?php $counter = 0; ?>

    @foreach($insurancePolicies as $insurance)

        <div id="policy-grp-{{$counter++}}">
            <button
                    type="button"
                    class="full-width btn-default borderless md-line-height"
                    data-toggle="collapse"
                    data-target="#insurance-{{ $counter }}">
                <span class="pull-left">{{ $insurance->name }}</span>
                <span class="glyphicon glyphicon-pencil pull-right glow"
                      aria-hidden="true"></span>
            </button>

            <div id="insurance-{{ $counter }}"
                 class="collapse md-line-height text-right">

                @if(!empty($insurance->type))
                    {{ $insurance->type }}
                @else
                    {{ 'Insurance type is not available' }}
                @endif


                @if(!empty($insurance->policy_id))
                    / {{ $insurance->policy_id }}
                @else
                    {{ 'Policy ID is not available' }}
                @endif

                <br>

                @if(! $insurance->approved)

                    <div class="radio-inline">
                        <input id="approve-{{ $counter }}"
                               name="insurance[{{ $insurance->id }}]"
                               value="1" type="radio">
                        <label for="approve-{{ $counter }}"><span></span>Approve</label>
                    </div>

                @endif

                <div class="radio-inline">
                    <input id="delete-{{ $counter }}"
                           name="insurance[{{ $insurance->id }}]"
                           value="0" type="radio">
                    <label for="delete-{{ $counter }}"><span></span>Delete</label>
                </div>

                <br><br>
            </div>
        </div>
    @endforeach


</div>