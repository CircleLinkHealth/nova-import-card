{{--<li>--}}
{{--<a class="" href="{{ route('provider.dashboard.manage.practice', ['practiceSlug' => $practiceSlug]) }}">--}}
{{--<i class=" material-icons">perm_identity</i>--}}
{{--Account--}}
{{--</a>--}}
{{--</li>--}}
<li>
    <a class="" href="{{ route('provider.dashboard.manage.practice', ['practiceSlug' => $practiceSlug]) }}">
        <i class=" material-icons">business</i>
        Practice
    </a>
</li>
<li>
    <a class="" href="{{ route('provider.dashboard.manage.locations', ['practiceSlug' => $practiceSlug]) }}">
        <i class=" material-icons">add_location</i>
        Locations
    </a>
</li>
<li>
    <a class="" href="{{ route('provider.dashboard.manage.staff', ['practiceSlug' => $practiceSlug]) }}">
        <i class=" material-icons">assignment_ind</i>
        Staff
    </a>
</li>


{{--<li>--}}
{{--<a class="" href=""><i class=" material-icons">help_outline</i><span--}}
{{--class="">Chat with us on Slack</span></a>--}}
{{--</li>--}}