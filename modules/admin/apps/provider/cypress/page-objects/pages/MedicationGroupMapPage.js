import BasePage from '../BasePage';

export default class MedicationGroupMapPage extends BasePage {
	typeMedication(text) {
		cy.get('.form-control').type(`${text}`);
	}
	selectMedicationGroup(value) {
		cy.get('select').select(value);
	}
	clickOnStore() {
		cy.get('.btn btn-primary col-md-12').click();
	}
}
