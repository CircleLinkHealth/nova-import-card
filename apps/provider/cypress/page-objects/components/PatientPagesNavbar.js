export default class PatientPagesNavbar {
	clickOnNotesAndActivities() {
		cy.get('.navbar-nav > :nth-child(1) > a').click();
		cy.url().should('contain', '/notes');
	}
	clickOnPatientOverview() {
		cy.get('.patient__actions > .navbar-nav > :nth-child(2) > a').click();
		cy.url().should('contain', '/summary');
	}
	clickOnPatientProfile() {
		cy.get('.navbar-nav > :nth-child(3) > a').click();
		cy.url().should('contain', '/careplan/demographics');
	}
	clickOnViewCareplan() {
		cy.get(':nth-child(5) > a').click();
		cy.url().should('contain', '/view-careplan');
	}
}
