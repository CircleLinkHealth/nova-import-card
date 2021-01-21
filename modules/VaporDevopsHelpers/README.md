# vapor-devops-helpers

To update secrets
- Clear all existing secrets using command `php artisan cpmvapor:clearsecrets production` (CircleLinkHealth\Core\Console\Commands\Vapor\DeleteAllSecrets)
- Download production-secrets.env from https://s3.console.aws.amazon.com/s3/buckets/cpm-production-keys?region=us-east-1&tab=objects.
- Upload new secrets using `php artisan cpmvapor:uploadsecrets /absolute/path/to/secrets/production-secrets.env production`
- Download production-vars.env from https://s3.console.aws.amazon.com/s3/buckets/cpm-production-keys?region=us-east-1&tab=objects.
- Make sure below vars are correct for the environment we are deploying to
```
LOW_CPM_QUEUE_NAME=
HIGH_CPM_QUEUE_NAME=
REVISIONABLE_QUEUE=
APP_ENV=
APP_URL=
AWV_URL=
CPM_CALLER_URL=
CPM_PROVIDER_APP_URL=
```
- Manually paste the env vars in vapor:
  - app-cpm-web: production-common-vars.env + production-provider-vars.env
  - cpm-admin: production-common-vars.env + production-superadmin-vars.env

