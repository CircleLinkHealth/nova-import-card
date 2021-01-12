#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH=$1

function split()
{
    RESET_MODULES_DIR=0
    if [ ! -z "$2" ] && [ -f "$PWD/apps/$2/monorepo-modules.txt" ]; then
        for DIR in $(cat "$PWD/apps/$2/monorepo-modules.txt")
        do
            APP_PATH="$PWD/apps/$2"

            echo "Installing $DIR in $APP_PATH"

            if [ -e "$APP_PATH/CircleLinkHealth/$DIR" ]; then
                echo "Deleting existing $APP_PATH/CircleLinkHealth/$DIR"
                rm -rf "$APP_PATH/CircleLinkHealth/$DIR"
            fi

            if [ ! -d "$APP_PATH/CircleLinkHealth" ]; then
                echo "Creating $APP_PATH/CircleLinkHealth"
                mkdir "$APP_PATH/CircleLinkHealth"
            fi

            echo "Copying modules"

            cp -rf "$PWD/modules/$DIR" "$APP_PATH/CircleLinkHealth/$DIR"
            git add "$APP_PATH/CircleLinkHealth/$DIR/"
            git commit -q -a
        done
    fi

    SHA1=`splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
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

split 'apps/admin-app' admin-app
split 'apps/awv-app' awv-app
split 'apps/caller-app' caller-app
split 'apps/provider-app' provider-app
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

bash "$PWD/bin/local-dev/setup-symlinks.sh"
