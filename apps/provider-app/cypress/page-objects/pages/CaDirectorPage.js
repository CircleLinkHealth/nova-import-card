import BasePage from '../BasePage';

export default class CaDirectorPage extends BasePage {
	clickOnEraseDemoPatients() {
		cy.get('.btn-danger').click();
	}
	clickOnCreateDemoPatients() {
		cy.get('.col-md-12 > .btn-info').click();
	}
	clickOnShowAssignedPatientsOnly() {
		cy.contains('Show Assigned Patients Only').click();
	}
	clickOnIncludeConsented() {
		cy.contains('Include Consented').click();
	}
	clickOnIncludeIneligible() {
		cy.contains('Include Ineligible').click();
	}
	clickOnClearSelectedPatients() {
		cy.contains('Clear Selected Patients').click();
	}
	setRecordsNumber(value) {
		cy.select('#VueTables__limit_u298h').select(value); //options: [10, 25, 50, 100, 200]
	}
	clickAssignToCa() {
		cy.contains('Assign To CA').click();
	}
	clickUnassignFromCa() {
		cy.contains('Unassign From CA').click();
	}
	clickMarkAsIneligible() {
		cy.contains('Mark As Ineligible').click();
	}
	selectFiveEnrollees() {
		cy.get('tbody > :nth-child(1) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
		cy.get('tbody > :nth-child(2) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
		cy.get('tbody > :nth-child(3) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
		cy.get('tbody > :nth-child(4) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
		cy.get('tbody > :nth-child(5) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
	}
	selectFirstEnrollee() {
		cy.get('tbody > :nth-child(1) > :nth-child(1) > .form-control')
			.check()
			.should('be.checked');
	}
	uncheckSelectedEnrollees() {
		cy.get('input [type="checkbox]').uncheck();
	}
	selectCareAmbassador(text) {
		cy.get('.vs__selected-options > .form-control').click();
		cy.contains(`${text}`).click();
	}
	clickOnEditFirstEnrollee() {
		cy.get('tbody > :nth-child(1) > :nth-child(2) > .btn').click();
	}
	getEnrolleeTable() {
		cy.get('#enrollees');
	}
}
