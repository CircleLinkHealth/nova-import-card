import BasePage from '../../page-objects/BasePage';
import DataGenerator from '../../fixtures/data-generator';

describe('Tests Cases For Unsuccessful Login', () => {
	const data = new DataGenerator();
	const basePage = new BasePage();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		cy.get('#login-submit-button').as('Log In');
		cy.get('input[name = "password"]').as('password');
		cy.get('input[name = "email"]').as('email');
	});

	it('should require email and password', () => {
		cy.get('@Log In').click();
		cy.get('.alert-danger').should('be.visible').its('length').should('eq', 1);
		cy.contains('The email field is required.');
		cy.contains('The password field is required');
		cy.log('both email and password are needed');
	});

	it('should require email', () => {
		cy.get('@password').type(data.generateID());
		cy.get('@Log In').click();
		cy.isVisible('.alert-danger').and(
			'contain',
			'The email field is required.'
		);
		cy.log('The email field is required.');
	});

	it('should require password', () => {
		cy.get('@email').type(data.generateEmail());
		cy.get('@Log In').click();
		cy.isVisible('.alert-danger').and(
			'contain',
			'The password field is required.'
		);
		cy.log('The password field is required.');
	});

	it('should not allow wrong credentials', () => {
		cy.get('@email').type(data.generateEmail());
		cy.get('@password').type(data.generateID());
		cy.get('@Log In').click();
		cy.isVisible('.alert-danger').and(
			'contain',
			'These credentials do not match our records.'
		);
		cy.log('These credentials do not match our records.');
	});
});
