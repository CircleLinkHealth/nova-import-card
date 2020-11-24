import BasePage from '../BasePage';

export default class PasswordResetPage extends BasePage {
	typeEmail(text) {
		cy.get('.form-control').type(`${text}`, { log: false });
	}

	clickSendPasswordResetLink() {
		cy.get('.btn').click();
	}

	alertIsVisible(text) {
		cy.get('.alert').should('be.visible').and('contain', `${text}`);
	}
}
