#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH=$1

function split()
{
    if [ ! -z "$2" && -f "$2/" ]; then
        if [ ! -z "$2" ]; then

        fi
    fi
    SHA1=`splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

# remote admin-app git@github.com:CircleLinkHealth/app-cpm-admin.git
# remote awv-app git@github.com:CircleLinkHealth/app-awv.git
# remote caller-app git@github.com:CircleLinkHealth/app-cpm-caller.git
# remote provider-app git@github.com:CircleLinkHealth/app-cpm-web.git
# remote eligibility-module git@github.com:CircleLinkHealth/eligibility-module.git
# remote self-enrollment-module git@github.com:CircleLinkHealth/self-enrollment-module.git
# remote opcache-gui-module git@github.com:CircleLinkHealth/opcache-gui.git
# remote raygun-module git@github.com:CircleLinkHealth/raygun-module.git
# remote time-tracking-module git@github.com:CircleLinkHealth/time-tracking.git
# remote two-fa-module git@github.com:CircleLinkHealth/two-fa.git
# remote customer-module git@github.com:CircleLinkHealth/customer.git
# remote ccm-billing-module git@github.com:CircleLinkHealth/ccm-billing-module.git
# remote core-module git@github.com:CircleLinkHealth/core.git
# remote saml-sp-module git@github.com:CircleLinkHealth/saml-sp-module.git
# remote revisionable-module git@github.com:CircleLinkhealth/revisionable-module.git
# remote cpm-migrations-module git@github.com:CircleLinkHealth/cpm-migrations-module.git
# remote sqlviews-module git@github.com:CircleLinkHealth/sqlviews-module.git
# remote cerberus-gatekeeper-module git@github.com:CircleLinkHealth/cerberus-gatekeeper-module.git
# remote shared-models-module git@github.com:CircleLinkHealth/shared-models-module.git
# remote ccda-parser-module git@github.com:CircleLinkHealth/ccda-parser-module.git
# remote patient-api-module git@github.com:CircleLinkHealth/patient-api-module.git
# remote nurse-invoices-module git@github.com:CircleLinkHealth/nurse-invoices-module.git
# remote shared-vue-components-module git@github.com:CircleLinkHealth/shared-vue-components-module.git
# remote condition-code-lookup-module git@github.com:CircleLinkHealth/condition-code-lookup-module.git
# remote synonyms-module git@github.com:CircleLinkHealth/synonyms.git
# remote twilio-integration-module git@github.com:CircleLinkHealth/twilio-integration-module.git
# remote laravel-module-installer-module git@github.com:CircleLinkHealth/laravel-module-installer.git
# remote short-url-module git@github.com:CircleLinkHealth/short-url.git
# remote pdf-service-module git@github.com:CircleLinkHealth/pdf-service-module.git
# remote cpm-admin-module git@github.com:CircleLinkHealth/cpm-admin-module.git
# remote vapor-cli-module git@github.com:CircleLinkHealth/vapor-cli.git
# remote vapor-core-module git@github.com:CircleLinkHealth/vapor-core.git
# remote vapor-devops-helpers-module git@github.com:CircleLinkHealth/vapor-devops-helpers.git

split 'apps/admin' admin-app
# split 'apps/awv' awv-app
# split 'apps/caller' caller-app
# split 'apps/provider' provider-app
# split 'modules/eligibility' eligibility-module
# split 'modules/self-enrollment' self-enrollment-module
# split 'modules/opcache-gui' opcache-gui-module
# split 'modules/raygun' raygun-module
# split 'modules/time-tracking' time-tracking-module
# split 'modules/two-fa' two-fa-module
# split 'modules/customer' customer-module
# split 'modules/ccm-billing' ccm-billing-module
# split 'modules/core' core-module
# split 'modules/saml-sp' saml-sp-module
# split 'modules/revisionable' revisionable-module
# split 'modules/cpm-migrations' cpm-migrations-module
# split 'modules/sqlviews' sqlviews-module
# split 'modules/cerberus-gatekeeper' cerberus-gatekeeper-module
# split 'modules/shared-models' shared-models-module
# split 'modules/ccda-parser' ccda-parser-module
# split 'modules/patient-api' patient-api-module
# split 'modules/nurse-invoices' nurse-invoices-module
# split 'modules/shared-vue-components' shared-vue-components-module
# split 'modules/condition-code-lookup' condition-code-lookup-module
# split 'modules/synonyms' synonyms-module
# split 'modules/twilio-integration' twilio-integration-module
# split 'modules/laravel-module-installer' laravel-module-installer-module
# split 'modules/short-url' short-url-module
# split 'modules/pdf-service' pdf-service-module
# split 'modules/cpm-admin-module' cpm-admin-module
# split 'modules/vapor-cli' vapor-cli-module
# split 'modules/vapor-core' vapor-core-module
# split 'modules/vapor-devops-helpers' vapor-devops-helpers-module
