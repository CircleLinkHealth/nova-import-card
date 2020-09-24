import { TESTER_PROVIDER } from '../../support/config';
import BasePage from '../BasePage';

export default class ViewCareplan extends BasePage {
	clickOnWeAreManaging() {
		cy.get(
			':nth-child(2) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
	}
	addCondition(text) {
		cy.get('.v-complete > .form-control').type(`${text}`);
		cy.get('form > .text-right > .btn').click({ force: true });
	}
	deleteCondition(text) {
		cy.get('.modal-container').contains(`${text}`).click();
		cy.get('.selected > .delete').click();
	}
	addHealthGoalWeight(value, int) {
		cy.get(
			':nth-child(3) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
		cy.get('.modal-body > :nth-child(1) > :nth-child(1)')
			.contains('Weight')
			.click();
		cy.get('form > :nth-child(1) > :nth-child(1) > .form-control')
			.clear()
			.type(value);
		cy.get(':nth-child(2) > .form-control').clear().type(int);

		cy.get(':nth-child(2) > .text-right > .btn').then($saveWeight => {
			if ($saveWeight.has.property('disabled')) {
				cy.get('.form-control > input').contains('Enable').click();
			} else {
				cy.get('.col-sm-12 > .form-control')
					.contains('Enable')
					.click({ force: true });
			}
		});

		/*cy.get(':nth-child(2) > .text-right > .btn').then($saveWeight => {
			if ($saveWeight.is('disabled')) {
				return cy.get('.form-control > input').contains('Enable').click();
			}
			return cy.get(':nth-child(2) > .text-right > .btn').click();
		});
*/
		/*if (Cypress.$(':nth-child(2) > .text-right > .btn').is('not.disabled')) {
			cy.get(':nth-child(2) > .text-right > .btn').click();
		} else if (Cypress.$(':nth-child(2) > .text-right > .btn').is('disabled')) {
			cy.get('.col-sm-12 > .form-control').contains('Enable').click();
			cy.get(':nth-child(2) > .text-right > .btn').click();
		}
		
		let btnStatus = Cypress.$(':nth-child(2) > .text-right > .btn').is(
			''
		);

		cy.get('.col-sm-12 > .form-control').contains('Enable').click();
		cy.get(':nth-child(2) > .text-right > .btn');

		if (btnStatus == true) {
			cy.get('.col-sm-12 > .form-control').contains('Enable').click();
		}
		cy.get(':nth-child(2) > .text-right > .btn').click();
		*/
	}

	addOtherNotes(text) {
		cy.get(
			':nth-child(14) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
		cy.get('form > :nth-child(1) > .form-control').focus().type(`${text}`);
		cy.contains('Save').click();
		cy.reload();
		cy.get(':nth-child(14) > .gutter > .col-xs-12 > ul').should(
			'contain',
			`${text}`
		);
	}
	deleteOtherNotes() {
		cy.get(
			':nth-child(14) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
		cy.get('form > :nth-child(1) > .form-control').clear();
		cy.contains('Save').click({ force: true });
		cy.reload();
		cy.get(':nth-child(14) > .gutter > .col-xs-12 > ul').should('be.empty');
	}

	clickOnAddAppointment() {
		cy.get(
			':nth-child(13) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
		cy.get('.modal-container').contains('Appointments');
	}
	setAppointmentFor(text) {
		cy.get('input[value="+"]').click();
		cy.contains('Create Appointment');
		cy.get('form > :nth-child(1) > .text-right > .btn').should('be.disabled');
		cy.get('.selected-tag').click();
		cy.get('.modal-container > .modal-body')
			.should('contain', TESTER_PROVIDER)
			.click();

		cy.get('.col-sm-4 > .form-control').type(`${text}`);
		cy.get(':nth-child(5) > .form-control').type('THIS IS A TEST APPOINTMENT');
		cy.get('form > :nth-child(1) > .text-right > .btn').click({ force: true });
		cy.contains('h4', 'Upcoming Appointments');
	}

	deleteAppointmentsFor(text) {
		cy.get('input[value="x"]').should('contain', `${text}`).click();
	}
	clickOnSocialServices() {
		cy.get(
			':nth-child(11) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
	}
	addSocialServices(text) {
		cy.get('form > :nth-child(1) > .form-control').type(`${text}`);
		cy.contains('Save').click({ force: true });
		cy.reload();
		cy.get(':nth-child(11) > .gutter > .col-xs-12 > ul').should(
			'contain',
			`${text}`
		);
	}
	deleteSocialServices() {
		cy.get(
			':nth-child(11) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
		cy.get('form > :nth-child(1) > .form-control').clear();
		cy.contains('Save').click({ force: true });
		cy.reload();
		cy.get(':nth-child(11) > .text-center').should(
			'contain',
			'No Instructions at this time'
		);
	}
	clickOnAddAllergies() {
		cy.get(
			':nth-child(10) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
	}
	addAllergies(text) {
		cy.get('.form-group > :nth-child(1) > .form-control').type(`${text}`);
		cy.contains('Create').click({ force: true });
		cy.reload();
		cy.get(':nth-child(10) > .gutter > .col-xs-12 > ul').should(
			'contain',
			`${text}`
		);
	}
	deleteAllergies(text) {
		cy.get('.pad-top-10').contains(`${text}`).click();
		cy.get('.selected > .delete').click({ force: true });
		cy.get('.pad-top-10').should('not.contain', `${text}`);
	}
	clickOnWeAreInformingYouAbout() {
		cy.get(
			':nth-child(6) > :nth-child(1) > .col-xs-12 > .patient-summary__subtitles > .btn'
		).click();
	}
}
