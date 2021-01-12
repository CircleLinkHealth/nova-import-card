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
        for DIR in $(cat "$PWD/apps/$2/monorepo-modules.txt")
        do
            cp -rf "$PWD/modules/$DIR" "$PWD/apps/$2/CircleLinkHealth/"
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
remote CcdaParser git@github.com:CircleLinkHealth/ccda-parser-module.git
remote CerberusGatekeeper git@github.com:CircleLinkHealth/cerberus-gatekeeper-module.git
remote CpmAdmin git@github.com:CircleLinkHealth/cpm-admin-module.git
remote CcmBilling git@github.com:CircleLinkHealth/ccm-billing-module.git
remote ConditionCodeLookup git@github.com:CircleLinkHealth/condition-code-lookup-module.git
remote CpmMigrations git@github.com:CircleLinkHealth/cpm-migrations-module.git
remote Core git@github.com:CircleLinkHealth/module-core.git
remote Customer git@github.com:CircleLinkHealth/module-customer.git
remote CpmAdmin git@github.com:CircleLinkHealth/cpm-admin-module.git
remote Eligibility git@github.com:CircleLinkHealth/eligibility-module.git
remote LaravelModuleInstaller git@github.com:CircleLinkHealth/laravel-module-installer.git
remote NurseInvoices git@github.com:CircleLinkHealth/nurse-invoices-module.git
remote OpcacheGui git@github.com:CircleLinkHealth/opcache-gui.git
remote PatientApi git@github.com:CircleLinkHealth/patient-api-module.git
remote PdfService git@github.com:CircleLinkHealth/pdf-service-module.git
remote Raygun git@github.com:CircleLinkHealth/module-raygun.git
remote Revisionable git@github.com:CircleLinkhealth/revisionable-module.git
remote SamlSp git@github.com:CircleLinkHealth/saml-sp-module.git
remote SelfEnrollment git@github.com:CircleLinkHealth/self-enrollment-module.git
remote SharedModels git@github.com:CircleLinkHealth/shared-models-module.git
remote SharedVueComponents git@github.com:CircleLinkHealth/shared-vue-components-module.git
remote ShortUrl git@github.com:CircleLinkHealth/short-url.git
remote SqlViews git@github.com:CircleLinkHealth/sqlviews-module.git
remote Synonyms git@github.com:CircleLinkHealth/synonyms.git
remote TimeTracking git@github.com:CircleLinkHealth/module-time-tracking.git
remote TwoFA git@github.com:CircleLinkHealth/module-two-fa.git
remote TwilioIntegration git@github.com:CircleLinkHealth/twilio-integration-module.git
remote VaporCli git@github.com:CircleLinkHealth/vapor-cli.git
remote VaporCore git@github.com:CircleLinkHealth/vapor-core.git
remote VaporDevopsHelpers git@github.com:CircleLinkHealth/vapor-devops-helpers.git

split 'apps/admin' admin-app
split 'apps/awv' awv-app
split 'apps/caller' caller-app
split 'apps/provider' provider-app
split 'modules/CcdaParser' CcdaParser
split 'modules/CerberusGatekeeper' CerberusGatekeeper
split 'modules/CpmAdmin' CpmAdmin
split 'modules/CcmBilling' CcmBilling
split 'modules/ConditionCodeLookup' ConditionCodeLookup
split 'modules/CpmMigrations' CpmMigrations
split 'modules/Core' Core
split 'modules/Customer' Customer
split 'modules/CpmAdmin' CpmAdmin
split 'modules/Eligibility' Eligibility
split 'modules/LaravelModuleInstaller' LaravelModuleInstaller
split 'modules/NurseInvoices' NurseInvoices
split 'modules/OpcacheGui' OpcacheGui
split 'modules/PatientApi' PatientApi
split 'modules/PdfService' PdfService
split 'modules/Raygun' Raygun
split 'modules/Revisionable' Revisionable
split 'modules/SamlSp' SamlSp
split 'modules/SelfEnrollment' SelfEnrollment
split 'modules/SharedModels' SharedModels
split 'modules/SharedVueComponents' SharedVueComponents
split 'modules/ShortUrl' ShortUrl
split 'modules/SqlViews' SqlViews
split 'modules/Synonyms' Synonyms
split 'modules/TimeTracking' TimeTracking
split 'modules/TwoFA' TwoFA
split 'modules/TwilioIntegration' TwilioIntegration
split 'modules/VaporCli' VaporCli
split 'modules/VaporCore' VaporCore
split 'modules/VaporDevopsHelpers' VaporDevopsHelpers
