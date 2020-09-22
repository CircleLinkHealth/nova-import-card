import BasePage from '../BasePage';

export default class EmailSubscriptionsPage extends BasePage {
	clickOnNotes() {
		cy.get(':nth-child(2) > label > input').click();
	}
	clickOnCpApprovalReminders() {
		cy.get(':nth-child(4) > label > input').click();
	}
	clickOnInvoiceReminders() {
		cy.get(':nth-child(6) > label > input').click();
	}
	clickOnInvoiceDisputes() {
		cy.get(':nth-child(8) > label > input').click();
	}
	clickOnBeforeInvoiceEmails() {
		cy.get(':nth-child(10) > label > input').click();
	}
	clickOnInvoiceReviews() {
		cy.get(':nth-child(12) > label > input').click();
	}
}
