export default class Floater {
	triggerHoverOnFloater() {
		cy.get('.mini-action-container').then($el => {
			cy.wrap($el).invoke('show');
			cy.isVisible(':nth-child(1) > a > .icon'); // Add Note
			cy.isVisible(':nth-child(2) > a > .icon'); // Add Observation
			cy.isVisible(':nth-child(3) > a > .icon'); // Add Ofline Activity
			cy.isVisible(':nth-child(4) > a > .icon'); // Add Appointment
			cy.isVisible('#showAddCarePersonModal > :nth-child(1) > .icon'); // Add Care Person
			//cy.isVisible(':nth-child(6) > :nth-child(1) > .icon'); // Add Activity
		});
	}
	addObservation() {
		cy.get('.mini-action-container').then($el => {
			cy.wrap($el).invoke('show');
			cy.get(':nth-child(2) > a > .icon').click({ force: true });
			cy.url().should('contain', '/input/observation');
		});
	}

	addNote() {
		cy.get(':nth-child(1) > a > .icon').click({ force: true });
		cy.url().should('contain', '/notes/create');
	}
	addOfflineActivity() {
		cy.get(':nth-child(3) > a > .icon').click({ force: true });
		cy.url().should('contain', '/activities/create');
	}
	addAppointment() {
		cy.get(':nth-child(4) > a > .icon').click({ force: true });
		cy.url().should('contain', '/appointments/create');
	}
	addCarePerson() {
		cy.get('#showAddCarePersonModal > :nth-child(1) > .icon').click({
			force: true,
		});
	}
	addActivity() {
		cy.get(':nth-child(6) > :nth-child(1) > .icon').click({ force: true });
	}
}
