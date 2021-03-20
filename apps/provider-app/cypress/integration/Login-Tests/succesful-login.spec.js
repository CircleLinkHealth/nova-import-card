import BasePage from '../../page-objects/BasePage';
import LoginPage from '../../page-objects/pages/LoginPage';
import Navbar from '../../page-objects/components/Navbar';
import {
	ADMIN_USERNAME,
	ADMIN_PASSWORD,
	NURSE_USERNAME,
	NURSE_PASSWORD,
	PROVIDER_USERNAME,
	PROVIDER_PASSWORD,
	CARE_AMBASSADOR_USERNAME,
	CARE_AMBASSADOR_PASSWORD,
} from '../../support/config';

describe('Tests Successful Login for Users', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();
	const navbar = new Navbar();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
	});

	it('should allow Nurse Login', () => {
		loginPage.Uilogin(NURSE_USERNAME, NURSE_PASSWORD);
		cy.wait(3000);
		cy.contains('care-center User MD').click();
		cy.contains('Logout').click();
		// navbar.nurseLogout();
		basePage.logMessage('Nurse successfully logs in and logs out');
	});

	it('should allow Provider Login', () => {
		loginPage.Uilogin(PROVIDER_USERNAME, PROVIDER_PASSWORD);
		cy.wait(3000);
		cy.get(':nth-child(6) > .dropdown-toggle').click();
		cy.contains('Logout').click();
		basePage.logMessage('Provider successfully logs in and logs out');
	});


	it('should allow Care Ambassador Login', () => {
		loginPage.Uilogin(CARE_AMBASSADOR_USERNAME, CARE_AMBASSADOR_PASSWORD);
		cy.wait(3000);
		navbar.careAmbassadorLogout();
		basePage.logMessage('Care Ambassador successfully logs in and logs out');
	});
});
