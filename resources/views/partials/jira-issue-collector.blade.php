<script type="text/javascript"
        src="https://circlelinkhealth.atlassian.net/s/d41d8cd98f00b204e9800998ecf8427e-T/-wkgthe/b/49/a44af77267a987a660377e5c46e0fb64/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?locale=en-US&collectorId=e9f0b2b7"></script>

<script type="text/javascript">window.ATL_JQ_PAGE_PROPS = {
        "triggerFunction": function (showCollectorDialog) {
            //Requires that jQuery is available!
            jQuery("#jiraIssueCollectorTrigger").click(function (e) {
                e.preventDefault();
                showCollectorDialog();
            });
        },

        fieldValues: {
            fullname: "{{auth()->user()->display_name}}",
            email: "{{auth()->user()->email}}",
        },

        environment: {
            'build_number': "{{config('app.app_version')}}",
            'user_id': "{{auth()->id()}}",
        }
    };


</script>

<div id="jiraIssueCollectorTrigger">
    Report Bug
</div>


<style>
    #jiraIssueCollectorTrigger {
        position: fixed;
        left: 0;
        bottom: 0;
        background-color: #f5f5f5;
        color: #444 !important;
        font-size: 12rem;
        padding: 6px;
        cursor: pointer;
    }
</style>
