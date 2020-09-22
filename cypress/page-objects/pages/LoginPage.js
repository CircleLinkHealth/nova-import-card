import BasePage from '../BasePage';

export default class LoginPage extends BasePage {
	login(email, password) {
		cy.login(email, password);
	}
	clickPasswordResetLink() {
		cy.contains('Lost/Need a password? Click Here').click();
	}
}
