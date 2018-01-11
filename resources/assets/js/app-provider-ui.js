require('./bootstrap');

import 'es6-string-polyfills'

import Vue from 'vue'
import axios from './bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "./store";

if (document) {
    const elem = document.querySelector('meta[name="base-url"]')
    if (elem) {
        axios.defaults.baseURL = elem.getAttribute('content');
    }
    else {
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

import ComponentProxy from './components/shared/component-proxy'
import CareTeamComponent from './components/pages/view-care-plan/care-team'
import CreateAppointmentsAddCarePerson from './components/CareTeam/create-appointments-add-care-person'
import CreateCarePerson from './components/CareTeam/create-care-person'
import UpdateCarePerson from './components/pages/view-care-plan/update-care-person'
import Select2Component from './components/src/select2'
import FabComponent from './components/fab'
import OpenModalComponent from './components/shared/open-modal'
import NotificationsComponent from './components/shared/notifications/notifications'
import PdfCarePlans from './components/pages/view-care-plan/pdf-careplans'
import MedicationsListComponent from './ccd-models/medications'
import ProblemsList from './ccd-models/problems'
import AllergiesList from './ccd-models/allergies'
import NurseDailyHours from './components/pages/work-schedule/daily-hours'
import QuestionnaireApp from './admin/questionnaire/app'
import TimeTracker from './admin/time-tracker'
import ServerTimeDisplay from './admin/time-tracker/comps/server-time-display'
import LoaderComponent from './components/loader'
import PersistentTextArea from './components/persistent-textarea'
import CareAreasComponent from './components/careplan/care-areas'
import HealthGoalsComponent from './components/careplan/health-goals'
import MedicationsComponent from './components/careplan/medications'
import SymptomsComponent from './components/careplan/symptoms'
import LifestylesComponent from './components/careplan/lifestyles'
import InstructionsComponent from './components/careplan/instructions'
import AllergiesComponent from './components/careplan/allergies'
import SocialServicesComponent from './components/careplan/social-services'
import MiscModalComponent from './components/careplan/modals/misc.modal'
import OthersComponent from './components/careplan/others'

import EventBus from './admin/time-tracker/comps/event-bus'
import { BindWindowFocusChange, BindWindowVisibilityChange } from './admin/time-tracker/events/window.event'

Vue.component('component-proxy', ComponentProxy)
Vue.component('careTeam', CareTeamComponent)
Vue.component('createAppointmentsAddCarePerson', CreateAppointmentsAddCarePerson)
Vue.component('createCarePerson', CreateCarePerson)
Vue.component('updateCarePerson', UpdateCarePerson)
Vue.component('select2', Select2Component)
Vue.component('fab', FabComponent)
Vue.component('openModal', OpenModalComponent)
Vue.component('notifications', NotificationsComponent)
Vue.component('pdfCareplans', PdfCarePlans)
Vue.component('medicationsList', MedicationsListComponent)
Vue.component('problemsList', ProblemsList)
Vue.component('allergiesList', AllergiesList)
Vue.component('nurseDailyHours', NurseDailyHours)
Vue.component('questionnaire-app', QuestionnaireApp)
Vue.component('time-tracker', TimeTracker)
Vue.component('server-time-display', ServerTimeDisplay)
Vue.component('loader', LoaderComponent)
Vue.component('persistent-textarea', PersistentTextArea)
Vue.component('care-areas', CareAreasComponent)
Vue.component('health-goals', HealthGoalsComponent)
Vue.component('medications', MedicationsComponent)
Vue.component('symptoms', SymptomsComponent)
Vue.component('lifestyles', LifestylesComponent)
Vue.component('instructions', InstructionsComponent)
Vue.component('allergies', AllergiesComponent)
Vue.component('social-services', SocialServicesComponent)
Vue.component('others', OthersComponent)
Vue.component('misc-modal', MiscModalComponent)

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

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}

BindWindowFocusChange(window)
BindWindowVisibilityChange(window, document)