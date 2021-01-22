export default class API {
	validateStatusCode200(path) {
		cy.request(Cypress.config().baseUrl + path)
			.its('status')
			.should('equal', 200);
	}
	validatesContent(path, text) {
		cy.request(Cypress.config().baseUrl + path)
			.its('body')
			.should('include', { name: `${text}` });
	}
}
