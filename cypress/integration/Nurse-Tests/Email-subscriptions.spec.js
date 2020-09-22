import BasePage from '../../page-objects/BasePage';
import LoginPage from '../../page-objects/pages/LoginPage';
import { NURSE_USERNAME, NURSE_PASSWORD } from '../../support/config';
import EmailSubscriptionsPage from '../../page-objects/pages/EmailSubscriptionsPage';

describe('Tests Email Subscriptions Page', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();
	const emailConfig = new EmailSubscriptionsPage();

	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(NURSE_USERNAME, NURSE_PASSWORD);
	});
	it('Should allow nurse to check / uncheck options for receiving emails', () => {
		cy.visit('/notification-subscriptions-dashboard');
		cy.contains('h2', 'Email Subscriptions');
		cy.isVisible('#submit');

		emailConfig.clickOnNotes();
		cy.should('not.be.checked');
		emailConfig.clickOnCpApprovalReminders();
		cy.should('not.be.checked');
		emailConfig.clickOnInvoiceReminders();
		cy.should('not.be.checked');
		emailConfig.clickOnInvoiceDisputes();
		cy.should('not.be.checked');
		emailConfig.clickOnBeforeInvoiceEmails();
		cy.should('not.be.checked');
		emailConfig.clickOnInvoiceReviews();
		cy.should('not.be.checked');

		cy.get('#submit').click();

		emailConfig.clickOnNotes();
		cy.should('be.checked');
		emailConfig.clickOnCpApprovalReminders();
		cy.should('be.checked');
		emailConfig.clickOnInvoiceReminders();
		cy.should('be.checked');
		emailConfig.clickOnInvoiceDisputes();
		cy.should('be.checked');
		emailConfig.clickOnBeforeInvoiceEmails();
		cy.should('be.checked');
		emailConfig.clickOnInvoiceReviews();
		cy.should('be.checked');
		cy.get('#submit').click();
	});
});
