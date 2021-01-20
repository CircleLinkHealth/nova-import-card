import BasePage from '../../page-objects/BasePage';
import LoginPage from '../../page-objects/pages/LoginPage';
import {
	ADMIN_USERNAME,
	ADMIN_PASSWORD,
	PROVIDER_USERNAME,
	PROVIDER_PASSWORD,
	NURSE_USERNAME,
	NURSE_PASSWORD,
} from '../../support/config';
import Navbar from '../../page-objects/components/Navbar';

describe('Tests all Hrefs on Admin Panel Homepage', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();
	const emptyHref = () => {
		cy.get('#app').find('a').get('[href=""]');
	};

	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
	});
	it('should find all hrefs on Admin Panel Homepage and validate they are not empty', () => {
		loginPage.Uilogin(ADMIN_USERNAME, ADMIN_PASSWORD);
		cy.get('#app').find('a').get('[href=""]').should('length', 1); // This only applies on staging for the EHR Report Writers Google Folder
		if (emptyHref) {
			console.log('[href="]');
		}
	});
});

describe('Tests all Hrefs on Provider Homepage', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();

	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
	});
	it('should find all hrefs on Provider Homepage and validate they are not empty', () => {
		loginPage.Uilogin(PROVIDER_USERNAME, PROVIDER_PASSWORD);
		cy.get('#app').find('a').get('[href=""]').should('length', 0); // There should be no A elements with a blank href

		cy.get(':nth-child(6) > .dropdown-toggle').click()
		cy.contains('Logout').click();
	});
});

describe('Tests all Hrefs on Nurse Homepage', () => {
	const basePage = new BasePage();
	const loginPage = new LoginPage();
	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
	});
	it('should find all hrefs on Nurse Homepage and validate they are not empty', () => {
		loginPage.Uilogin(NURSE_USERNAME, NURSE_PASSWORD);
		cy.get('#app').find('a').get('[href=""]').should('length', 0); // There should be no A elements with a blank href
	});
});