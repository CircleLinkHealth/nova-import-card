import Vue from 'vue'

//custom components
import QuestionnaireApp from './app'

Vue.config.productionTip = false

const App = new Vue({
  el: '#questionnaire-app',
  template: '<questionnaire-app :questions="questions" class-name="questionnaire"/>',
  data: {
    questions: window.questions || []
  },
  components: { 
    'questionnaire-app': QuestionnaireApp
  }
})
