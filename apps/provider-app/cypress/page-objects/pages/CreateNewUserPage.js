import BasePage from '../BasePage';

export default class CreateNewUserPage extends BasePage {
	enterUsername(username) {
		cy.get('#username').type(username);
	}

	enterEmail(email) {
		cy.get('#email').type(email);
	}

	enterPassword(password) {
		cy.get('#password').type(password);
	}

	confirmPassword(text) {
		cy.get('#password_confirmation').type(`${text}`);
	}

	enterFirstName(text) {
		cy.get('#first_name').type(`${text}`);
	}

	enterLastName(text) {
		cy.get('#last_name').type(`${text}`);
	}

	selectRole(text) {
		cy.get('#role').select(`${text}`);
	}

	selectPractice(name) {
		cy.get('#program_id').select(name);
	}

	clickOnCreateUser() {
		cy.get('.btn-success').click();
	}
}
