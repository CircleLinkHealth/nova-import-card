import BasePage from '../BasePage';

export default class LoginPage extends BasePage {
	Uilogin(email, password) {
		cy.Uilogin(email, password);
	}
	clickPasswordResetLink() {
		cy.contains('Lost/Need a password? Click Here').click();
	}
}
