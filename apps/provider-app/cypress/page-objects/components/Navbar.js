import { TESTER_CA } from "../../support/config";

export default class Navbar {
	clickOnLogo() {
		cy.get('.navbar-brand').click();
	}
	search(text) {
		cy.get('#patient-search-text-box').type(`${text}`);
	}
	clickOnActivities() {
		cy.contains('Activities').click();
	}
	clickOnSchedule() {
		cy.contains('Schedule').click();
	}
	clickOnNotesReport() {
		cy.contains('Reports').click();
		cy.contains('Notes Report')
			.should('have.attr', 'href', '/manage-patients/provider-notes')
			.click();
	}
	clickOnUnder20MinutesReport() {
		cy.contains('Reports').click();
		cy.contains('Under 20 Minutes Report')
			.should('have.attr', 'href', '/manage-patients/u20')
			.click();
	}
	clickOnNotifications() {
		cy.contains('Notifications').click();
	}
	clickOnPrintPausedPatientLetters() {
		cy.get(':nth-child(1) > :nth-child(5) > .dropdown-toggle').click(); // gets Reports dropdown
		cy.contains('Print Paused Patient Letters').click();
	}
	clickOnCaDirector() {
		cy.contains('CA Director')
			.should(
				'have.attr',
				'href',
				Cypress.config().baseUrl + '/admin/ca-director'
			)
			.click({ force: true });
	}
	nurseLogout() {
		cy.get(':nth-child(6) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	}
	providerLogout() {
		cy.get(':nth-child(7) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	}
	adminLogout() {
		cy.get('.navbar-right > :nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	}
	careAmbassadorLogout() {
		cy.contains(TESTER_CA).click();
		cy.contains('Logout').click();
	}
}
