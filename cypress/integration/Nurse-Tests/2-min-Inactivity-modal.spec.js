import { NURSE_USERNAME, NURSE_PASSWORD } from '../../support/config';
import BasePage from '../../page-objects/BasePage';
import LoginPage from '../../page-objects/pages/LoginPage';
import Navbar from '../../page-objects/components/Navbar';

describe('Tests that inactivity modal pops up after 2 minutes of inactivity time', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();
	const navbar = new Navbar();

	it('Should show inactivity modal when nurse is inactive for 2 minutes', () => {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(NURSE_USERNAME, NURSE_PASSWORD);
		basePage.pause(125000);
		cy.get('.modal-container').should('be.visible');
		cy.contains('You have gone idle ...');
		cy.contains(
			'We haven’t heard from you in a while 😢. Are you still working?'
		);
		cy.isVisible('.modal-cancel-button');
		cy.isVisible('.modal-ok-button');
		cy.get('.modal-ok-button').click();
		navbar.nurseLogout();
	});
});
