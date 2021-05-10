## Job with large payload processor

#### Problem
AWS SQS limits the maximum payload size a message can have to 256kb. This presents problems when we want to process jobs with a larger payload.


#### Solution
Before the job is dispatched, we will evaluate the size of the SQS message. If it's over 256kb, we will:
- store the original message in S3
- delete the job from the queue
- dispatch a different job with a reference to the payload in S3
- when the new job runs, pull the old job from S3 and run it syncronously

#### How to use this module
Add below in the boot method of your AppServiceProvider
```
Queue::before(function (JobProcessing $event) {
    (new LargeJobsDispatcherInterceptor())->handle($event->job);
});
```