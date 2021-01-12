#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH=$1

function split()
{
    if [ ! -z "$2" ] && [ -f "$PWD/apps/$2/monorepo-modules.txt" ]; then
        if [ ! -d "$PWD/apps/$2/CircleLinkHealth" ]; then
            mkdir "$PWD/apps/$2/CircleLinkHealth"
        fi
        for FILE in $(cat "$PWD/apps/$2/monorepo-modules.txt")
        do
            cp -rf "$PWD/modules/$FILE" "$PWD/apps/$2/CircleLinkHealth"
        done
    fi
    SHA1=`splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
    if [ ! -z "$2" ]; then
        rm -rf "$PWD/apps/$2/CircleLinkHealth"
    fi
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote admin-app git@github.com:CircleLinkHealth/app-cpm-admin.git
remote awv-app git@github.com:CircleLinkHealth/app-awv.git
remote caller-app git@github.com:CircleLinkHealth/app-cpm-caller.git
remote provider-app git@github.com:CircleLinkHealth/app-cpm-web.git
remote eligibility-module git@github.com:CircleLinkHealth/eligibility-module.git
remote self-enrollment-module git@github.com:CircleLinkHealth/self-enrollment-module.git
remote opcache-gui-module git@github.com:CircleLinkHealth/opcache-gui.git
remote raygun-module git@github.com:CircleLinkHealth/raygun-module.git
remote time-tracking-module git@github.com:CircleLinkHealth/time-tracking.git
remote two-fa-module git@github.com:CircleLinkHealth/two-fa.git
remote customer-module git@github.com:CircleLinkHealth/customer.git
remote ccm-billing-module git@github.com:CircleLinkHealth/ccm-billing-module.git
remote core-module git@github.com:CircleLinkHealth/core.git
remote saml-sp-module git@github.com:CircleLinkHealth/saml-sp-module.git
remote revisionable-module git@github.com:CircleLinkhealth/revisionable-module.git
remote cpm-migrations-module git@github.com:CircleLinkHealth/cpm-migrations-module.git
remote sqlviews-module git@github.com:CircleLinkHealth/sqlviews-module.git
remote cerberus-gatekeeper-module git@github.com:CircleLinkHealth/cerberus-gatekeeper-module.git
remote shared-models-module git@github.com:CircleLinkHealth/shared-models-module.git
remote ccda-parser-module git@github.com:CircleLinkHealth/ccda-parser-module.git
remote patient-api-module git@github.com:CircleLinkHealth/patient-api-module.git
remote nurse-invoices-module git@github.com:CircleLinkHealth/nurse-invoices-module.git
remote shared-vue-components-module git@github.com:CircleLinkHealth/shared-vue-components-module.git
remote condition-code-lookup-module git@github.com:CircleLinkHealth/condition-code-lookup-module.git
remote synonyms-module git@github.com:CircleLinkHealth/synonyms.git
remote twilio-integration-module git@github.com:CircleLinkHealth/twilio-integration-module.git
remote laravel-module-installer-module git@github.com:CircleLinkHealth/laravel-module-installer.git
remote short-url-module git@github.com:CircleLinkHealth/short-url.git
remote pdf-service-module git@github.com:CircleLinkHealth/pdf-service-module.git
remote cpm-admin-module git@github.com:CircleLinkHealth/cpm-admin-module.git
remote vapor-cli-module git@github.com:CircleLinkHealth/vapor-cli.git
remote vapor-core-module git@github.com:CircleLinkHealth/vapor-core.git
remote vapor-devops-helpers-module git@github.com:CircleLinkHealth/vapor-devops-helpers.git

split 'apps/admin' admin-app
split 'apps/awv' awv-app
split 'apps/caller' caller-app
split 'apps/provider' provider-app
split 'modules/eligibility-module' eligibility-module
split 'modules/self-enrollment-module' self-enrollment-module
split 'modules/opcache-gui-module' opcache-gui-module
split 'modules/raygun-module' raygun-module
split 'modules/time-tracking-module' time-tracking-module
split 'modules/two-fa-module' two-fa-module
split 'modules/customer-module' customer-module
split 'modules/ccm-billing-module' ccm-billing-module
split 'modules/core-module' core-module
split 'modules/saml-sp-module' saml-sp-module
split 'modules/revisionable-module' revisionable-module
split 'modules/cpm-migrations-module' cpm-migrations-module
split 'modules/sqlviews-module' sqlviews-module
split 'modules/cerberus-gatekeeper-module' cerberus-gatekeeper-module
split 'modules/shared-models-module' shared-models-module
split 'modules/ccda-parser-module' ccda-parser-module
split 'modules/patient-api-module' patient-api-module
split 'modules/nurse-invoices-module' nurse-invoices-module
split 'modules/shared-vue-components-module' shared-vue-components-module
split 'modules/condition-code-lookup-module' condition-code-lookup-module
split 'modules/synonyms-module' synonyms-module
split 'modules/twilio-integration-module' twilio-integration-module
split 'modules/laravel-module-installer-module' laravel-module-installer-module
split 'modules/short-url-module' short-url-module
split 'modules/pdf-service-module' pdf-service-module
split 'modules/cpm-admin-module' cpm-admin-module
split 'modules/vapor-cli-module' vapor-cli-module
split 'modules/vapor-core-module' vapor-core-module
split 'modules/vapor-devops-helpers-module' vapor-devops-helpers-module
