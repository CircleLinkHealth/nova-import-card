import faker from 'faker';
import BasePage from '../../page-objects/BasePage';
import LoginPage from '../../page-objects/pages/LoginPage';
import PasswordResetPage from '../../page-objects/pages/PasswordResetPage';
import { TESTER_GMAIL } from '../../support/config';

describe('Tests Cases for Forgotten Password', () => {
	const passwordResetPage = new PasswordResetPage();
	const basePage = new BasePage();
	const loginPage = new LoginPage();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
	});

	it('should have a correct link to #/reset', () => {
		cy.contains('Lost/Need a password? Click Here');
		cy.should(
			'have.attr',
			'href',
			Cypress.config().baseUrl + '/auth/password/reset'
		);
	});

	it('should not send a reset password link to emails not found in the system', () => {
		loginPage.clickPasswordResetLink();
		cy.url().should('contain', '/auth/password/reset');
		passwordResetPage.typeEmail(faker.internet.exampleEmail());
		passwordResetPage.clickSendPasswordResetLink();
		passwordResetPage.alertIsVisible(
			"We can't find a user with that email address."
		);
	});

	it('should send a reset password link to emails found in the system', () => {
		loginPage.clickPasswordResetLink();
		cy.url().should('contain', '/auth/password/reset');
		passwordResetPage.typeEmail(TESTER_GMAIL); // must be valid user email => /../../../config.js
		passwordResetPage.clickSendPasswordResetLink();
		passwordResetPage.alertIsVisible(
			'We have emailed your password reset link!'
		);
	});
});
