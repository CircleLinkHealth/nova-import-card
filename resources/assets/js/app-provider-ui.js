require('./bootstrap');

import 'es6-string-polyfills'
import './prototypes/array.prototype'

import Vue from 'vue'
import axios from './bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "./store";
import {ClientTable} from 'vue-tables-2'

Vue.use(ClientTable, {}, false)


if (document) {
    const elem = document.querySelector('meta[name="base-url"]')
    if (elem) {
        axios.defaults.baseURL = elem.getAttribute('content');
    } else {
        console.error('base url not found.')
    }
}

Vue.use(VueAxios, axios)

Vue.config.debug = true

Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
})
const BillingComponent = () => import(/* webpackChunkName: "chunk-billing" */ './admin/billing')
const ComponentProxy = () => import(/* webpackChunkName: "chunk" */ './components/shared/component-proxy')
const CareTeamComponent = () => import(/* webpackChunkName: "chunk-careteam" */ './components/pages/view-care-plan/care-team')
const CreateAppointmentsAddCarePerson = () => import(/* webpackChunkName: "chunk-careteam" */ './components/CareTeam/create-appointments-add-care-person')
const CreateCarePerson = () => import(/* webpackChunkName: "chunk-careteam" */ './components/CareTeam/create-care-person')
const UpdateCarePerson = () => import(/* webpackChunkName: "chunk-careteam" */ './components/pages/view-care-plan/update-care-person')
const Select2Component = () => import(/* webpackChunkName: "chunk-misc" */ './components/src/select2')
const FabComponent = () => import(/* webpackChunkName: "chunk-misc" */ './components/fab')
const OpenModalComponent = () => import(/* webpackChunkName: "chunk-misc" */ './components/shared/open-modal')
const NotificationsComponent = () => import(/* webpackChunkName: "chunk-misc" */ './components/shared/notifications/notifications')
const PdfCarePlans = () => import(/* webpackChunkName: "chunk" */ './components/pages/view-care-plan/pdf-careplans')
const CareDocsIndex = () => import(/* webpackChunkName: "chunk" */ './components/pages/care-docs/index')
const MedicationsListComponent = () => import(/* webpackChunkName: "chunk" */ './ccd-models/medications')
const ProblemsList = () => import(/* webpackChunkName: "chunk" */ './ccd-models/problems')
const AllergiesList = () => import(/* webpackChunkName: "chunk" */ './ccd-models/allergies')
const NurseDailyHours = () => import(/* webpackChunkName: "chunk" */ './components/pages/work-schedule/daily-hours')
const QuestionnaireApp = () => import(/* webpackChunkName: "chunk-assessment" */ './admin/questionnaire/app')
const TimeTracker = () => import(/* webpackChunkName: "chunk-time-tracker" */ './admin/time-tracker')
const TimeTrackerCallModeComponent = () => import(/* webpackChunkName: "chunk-time-tracker" */ './admin/time-tracker/time-tracker-call-mode')
const ServerTimeDisplay = () => import(/* webpackChunkName: "chunk" */ './admin/time-tracker/comps/server-time-display')
const LoaderComponent = () => import(/* webpackChunkName: "chunk-misc" */ './components/loader')
const PersistentTextArea = () => import(/* webpackChunkName: "chunk-misc" */ './components/persistent-textarea')
const CareAreasComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/care-areas')
const HealthGoalsComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/health-goals')
const MedicationsComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/medications')
const SymptomsComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/symptoms')
const LifestylesComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/lifestyles')
const InstructionsComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/instructions')
const AllergiesComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/allergies')
const SocialServicesComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/social-services')
const MiscModalComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/modals/misc.modal')
const OthersComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/others')
const AppointmentsComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/appointments')
const AddTaskModalComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/modals/add-task.modal');
const PatientList = () => import(/* webpackChunkName: "chunk-patient-listing" */ './components/patients/listing')
const DatepickerComponent = () => import(/* webpackChunkName: "chunk-datepicker" */ 'vuejs-datepicker')
const ImporterTrainerComponent = () => import(/* webpackChunkName: "chunk-importer-trainer" */ './components/importer/trainer')
const PatientNextCallComponent = () => import(/* webpackChunkName: "chunk-patient-next-call" */ './components/patient-next-call');
const CallNumberComponent = () => import(/* webpackChunkName: "chunk-call-number" */ './components/call-number');
const UserAccountSettings = () => import(/* webpackChunkName: "chunk-user-account-settings" */ './components/user-account-settings');
const AuthyPerform2FA = () => import(/* webpackChunkName: "chunk-authy-perform-2fa" */ './components/authy-perform-2fa');
const CcdUploader = () => import(/* webpackChunkName: "chunk-ccd-uploader" */ './components/importer/ccd-upload');
const CcdViewer = () => import(/* webpackChunkName: "chunk-ccd-viewer" */ './components/importer/ccd-viewer');
const CallMgmtAppV2 = () => import(/* webpackChunkName: "chunk-admin" */ './admin/calls/app-v2')
const DisputeNurseInvoice = () => import(/* webpackChunkName: "chunk-nurse" */ './../../../Modules/Nurseinvoices/Resources/assets/js/components/dispute-invoice')
const NurseInvoiceDailyDispute = () => import(/* webpackChunkName: "chunk-nurse" */ './../../../Modules/Nurseinvoices/Resources/assets/js/components/nurseInvoiceDailyDispute');
const PusherNotifications = () => import(/* webpackChunkName: "chunk-pusher-notifications" */ './components/pusher-notifications')
const PusherSeeAllNotifications = () => import(/* webpackChunkName: "chunk-pusher-notifications" */ './components/pusher-see-all-notifications')
const AttestCallConditionsModalComponent = () => import(/* webpackChunkName: "chunk-attest-call-conditions" */  './components/attest-call-conditions.modal');

import EventBus from './admin/time-tracker/comps/event-bus'
import {BindWindowFocusChange, BindWindowVisibilityChange} from './admin/time-tracker/events/window.event'

Vue.component('attest-call-conditions-modal', AttestCallConditionsModalComponent);
Vue.component('billing-report', BillingComponent);
Vue.component('component-proxy', ComponentProxy);
Vue.component('careTeam', CareTeamComponent);
Vue.component('createAppointmentsAddCarePerson', CreateAppointmentsAddCarePerson);
Vue.component('createCarePerson', CreateCarePerson);
Vue.component('updateCarePerson', UpdateCarePerson);
Vue.component('select2', Select2Component);
Vue.component('fab', FabComponent);
Vue.component('openModal', OpenModalComponent);
Vue.component('notifications', NotificationsComponent);
Vue.component('pdfCareplans', PdfCarePlans);
Vue.component('careDocsIndex', CareDocsIndex);
Vue.component('medicationsList', MedicationsListComponent);
Vue.component('problemsList', ProblemsList);
Vue.component('allergiesList', AllergiesList);
Vue.component('nurseDailyHours', NurseDailyHours);
Vue.component('questionnaire-app', QuestionnaireApp);
Vue.component('time-tracker', TimeTracker);
Vue.component('time-tracker-call-mode', TimeTrackerCallModeComponent);
Vue.component('server-time-display', ServerTimeDisplay);
Vue.component('loader', LoaderComponent);
Vue.component('persistent-textarea', PersistentTextArea);
Vue.component('care-areas', CareAreasComponent);
Vue.component('health-goals', HealthGoalsComponent);
Vue.component('medications', MedicationsComponent);
Vue.component('symptoms', SymptomsComponent);
Vue.component('lifestyles', LifestylesComponent);
Vue.component('instructions', InstructionsComponent);
Vue.component('allergies', AllergiesComponent);
Vue.component('social-services', SocialServicesComponent);
Vue.component('others', OthersComponent);
Vue.component('misc-modal', MiscModalComponent);
Vue.component('appointments', AppointmentsComponent);
Vue.component('patient-list', PatientList);
Vue.component('v-datepicker', DatepickerComponent);
Vue.component('importer-trainer', ImporterTrainerComponent);
Vue.component('patient-next-call', PatientNextCallComponent);
Vue.component('add-task-modal', AddTaskModalComponent);
Vue.component('call-number', CallNumberComponent);
Vue.component('user-account-settings', UserAccountSettings);
Vue.component('authy-perform-2fa', AuthyPerform2FA);
Vue.component('ccd-upload', CcdUploader);
Vue.component('ccd-viewer', CcdViewer);
Vue.component('call-mgmt-app-v2', CallMgmtAppV2);
Vue.component('dispute-nurse-invoice', DisputeNurseInvoice);
Vue.component('nurse-invoice-daily-dispute', NurseInvoiceDailyDispute);
Vue.component('pusher-notifications', PusherNotifications);
Vue.component('pusher-see-all-notifications', PusherSeeAllNotifications);


const App = new Vue({
    el: '#app',
    store,
    data: {
        questions: window.questions || [],
        timeTrackerInfo: window.timeTrackerInfo || {}
    },
    mounted() {
        if (Object.keys(this.timeTrackerInfo).length === 0) {
            console.error("Time-Tracker: Info Object should have values");
        }
    }
})

App.EventBus = EventBus

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}

BindWindowFocusChange(window)
BindWindowVisibilityChange(window, document)


console.log(process.env['MIX_LOG_DNA_CLIENT_INGESTION_KEY']);