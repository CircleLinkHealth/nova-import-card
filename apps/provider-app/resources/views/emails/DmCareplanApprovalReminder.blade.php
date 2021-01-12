
Dear {{$notifiable->getFullName()}},
     
Thank you for using CircleLink Health for Chronic Care Management!
     
We are delighted to report {{$numberOfCareplans}} care plan(s) awaiting your approval.
 
To review and approve, simply copy and paste www.careplanmanager.com into a web browser and login.
 
Then, on the homepage, click "Approve Now" in the “Pending Care Plans” table/list (center of page), for the first patient you wish to approve.
 
You can review and approve new CCM care plans in the next page. Just click “Approve and View Next” to approve and view the next pending care plan. You can edit the care plan with green edit icons.
 
Alternatively, you can upload your own PDF care plan using the "Upload PDF" button. (NOTE: Please make sure uploaded PDF care plans conform to Medicare requirements.)
 
Our registered nurses will take it from here!
 
Thank you again,
CircleLink Team
 
To receive this notification less (or more) frequently, please adjust your settings by visiting this site: {{route('provider.dashboard.manage.notifications', ['practiceSlug' => $notifiable->primaryPractice->name])}}

