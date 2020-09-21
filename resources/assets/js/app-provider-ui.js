require('../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/bootstrap');

import 'es6-string-polyfills'
import '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/prototypes/array.prototype'

import Vue from 'vue'
import axios from '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/store";
import {ClientTable} from 'vue-tables-2'
import "vue-trix"
import EventBus from '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/time-tracker/comps/event-bus'
import {BindWindowFocusChange, BindWindowVisibilityChange} from '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/time-tracker/events/window.event'

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
const ComponentProxy = () => import(/* webpackChunkName: "chunk-careplan" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/component-proxy')
const CareTeamComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/pages/view-care-plan/care-team')
const CreateAppointmentsAddCarePerson = () => import(/* webpackChunkName: "chunk-careplan" */ './components/CareTeam/create-appointments-add-care-person')
const CreateCarePerson = () => import(/* webpackChunkName: "chunk-careplan" */ './components/CareTeam/create-care-person')
const UpdateCarePerson = () => import(/* webpackChunkName: "chunk-careplan" */ './components/pages/view-care-plan/update-care-person')
const Select2Component = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/src/select2')
const FabComponent = () => import(/* webpackChunkName: "chunk-provider" */ './components/fab')
const OpenModalComponent = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/open-modal')
const NotificationsComponent = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/notifications/notifications')
const CareplanActions = () => import(/* webpackChunkName: "chunk-careplan" */ './components/pages/view-care-plan/careplan-actions')
const CareDocsIndex = () => import(/* webpackChunkName: "chunk-careplan" */ './components/pages/care-docs/index')
const MedicationsListComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './ccd-models/medications')
const ProblemsList = () => import(/* webpackChunkName: "chunk-careplan" */ './ccd-models/problems')
const AllergiesList = () => import(/* webpackChunkName: "chunk-careplan" */ './ccd-models/allergies')
const NurseDailyHours = () => import(/* webpackChunkName: "chunk-careplan" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/pages/work-schedule/daily-hours')
const QuestionnaireApp = () => import(/* webpackChunkName: "chunk-careplan" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/questionnaire/app')
const TimeTracker = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/time-tracker')
const TimeTrackerCallModeComponent = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/time-tracker/time-tracker-call-mode')
const ServerTimeDisplay = () => import(/* webpackChunkName: "chunk-careplan" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/time-tracker/comps/server-time-display')
const LoaderComponent = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader')
const PersistentTextArea = () => import(/* webpackChunkName: "chunk-provider" */ './components/persistent-textarea')
const CareAreasComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/care-areas')
const DiabetesCheckModalComponent = () => import(/* webpackChunkName: "chunk-careplan" */ './components/careplan/modals/diabetes-check.modal')
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
const PatientNextCallComponent = () => import(/* webpackChunkName: "chunk-patient" */ './components/patient-next-call');
const PatientSpouseComponent = () => import(/* webpackChunkName: "chunk-patient" */ './components/patient-spouse');
const CallNumberComponent = () => import(/* webpackChunkName: "chunk-patient" */ './components/call-number');
const UserAccountSettings = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Twofa/Resources/assets/js/user-account-settings');
const AuthyPerform2FA = () => import(/* webpackChunkName: "chunk-provider" */ '../../../CircleLinkHealth/Twofa/Resources/assets/js/authy-perform-2fa');
const CcdUploader = () => import(/* webpackChunkName: "chunk-importer" */ './components/importer/ccd-upload');
const ImportedMedicalRecordsManagement = () => import(/* webpackChunkName: "chunk-importer" */ './components/importer/imported-medical-records-management');
const NurseScheduleCalendar = () => import(/* webpackChunkName: "chunk-nurse" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/NurseScheduleCalendar');
const CalendarLoader = () => import(/* webpackChunkName: "chunk-nurse" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/FullScreenLoader');
const DisputeNurseInvoice = () => import(/* webpackChunkName: "chunk-nurse" */ './../../../CircleLinkHealth/Nurseinvoices/Resources/assets/js/components/dispute-invoice')
const NurseInvoiceDailyDispute = () => import(/* webpackChunkName: "chunk-nurse" */ './../../../CircleLinkHealth/Nurseinvoices/Resources/assets/js/components/nurseInvoiceDailyDispute');
const PusherNotifications = () => import(/* webpackChunkName: "chunk-provider" */ './components/pusher-notifications')
const PusherSeeAllNotifications = () => import(/* webpackChunkName: "chunk-provider" */ './components/pusher-see-all-notifications')
const SendEmailToPatientComponent = () => import('./components/send-email-to-patient')
const AttestCallConditionsModalComponent = () => import(/* webpackChunkName: "chunk-patient" */  './components/attest-call-conditions.modal');
const CalendarDailyReport = () => import(/* webpackChunkName: "chunk-nurse" */ '../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/CalendarDailyReport');


Vue.component('attest-call-conditions-modal', AttestCallConditionsModalComponent);
Vue.component('component-proxy', ComponentProxy);
Vue.component('careTeam', CareTeamComponent);
Vue.component('createAppointmentsAddCarePerson', CreateAppointmentsAddCarePerson);
Vue.component('createCarePerson', CreateCarePerson);
Vue.component('updateCarePerson', UpdateCarePerson);
Vue.component('select2', Select2Component);
Vue.component('send-email-to-patient', SendEmailToPatientComponent);
Vue.component('fab', FabComponent);
Vue.component('openModal', OpenModalComponent);
Vue.component('notifications', NotificationsComponent);
Vue.component('careplanActions', CareplanActions);
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
Vue.component('diabetes-check-modal', DiabetesCheckModalComponent);
Vue.component('appointments', AppointmentsComponent);
Vue.component('patient-list', PatientList);
Vue.component('v-datepicker', DatepickerComponent);
Vue.component('patient-next-call', PatientNextCallComponent);
Vue.component('patient-spouse', PatientSpouseComponent);
Vue.component('add-task-modal', AddTaskModalComponent);
Vue.component('call-number', CallNumberComponent);
Vue.component('user-account-settings', UserAccountSettings);
Vue.component('authy-perform-2fa', AuthyPerform2FA);
Vue.component('ccd-upload', CcdUploader);
Vue.component('imported-medical-records-management', ImportedMedicalRecordsManagement);
Vue.component('dispute-nurse-invoice', DisputeNurseInvoice);
Vue.component('nurse-invoice-daily-dispute', NurseInvoiceDailyDispute);
Vue.component('nurse-schedule-calendar', NurseScheduleCalendar);
Vue.component('calendar-loader', CalendarLoader);
Vue.component('pusher-notifications', PusherNotifications);
Vue.component('pusher-see-all-notifications', PusherSeeAllNotifications);
Vue.component('calendar-daily-report', CalendarDailyReport);

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
