import BasePage from '../BasePage';

export default class ObservationsPage extends BasePage {
	addObservation($val1, $val2, text) {
		const todaysDate = Cypress.moment().format('YYYY-MM-DD');

		cy.get('#observationType').select($val1, { force: true });
		cy.get('#observationSource').select($val2, { force: true });
		cy.get('#observationValue').type(`${text}`);
		//cy.get('#observationDate').contains(todaysDate);
		cy.contains('Save').click({ force: true });
	}
}
