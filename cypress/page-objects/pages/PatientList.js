import BasePage from '../BasePage';

export default class PatientList extends BasePage {
	selectPatient(text) {
		cy.isVisible('.main-form-block');
		cy.get('tbody > :nth-child' + `(${text})` + ' > :nth-child(1) > div > a')
			.invoke('removeAttr', 'target')
			.click({ force: true });
	}
}
