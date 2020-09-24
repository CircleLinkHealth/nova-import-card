import LoginPage from '../../page-objects/pages/LoginPage';
import {
	ADMIN_USERNAME,
	ADMIN_PASSWORD,
	TESTER_CA,
} from '../../support/config';
import BasePage from '../../page-objects/BasePage';
import CaDirectorPage from '../../page-objects/pages/CaDirectorPage';
import Navbar from '../../page-objects/components/Navbar';

describe('Tests CA Director Page: Edit Enrollee and Assign to Care Ambassador ', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const caDirectorPage = new CaDirectorPage();
	const navbar = new Navbar();
	const selectTestEnrollee = () =>
		cy
			.contains('testName')
			.parent('tr')
			.within(() => {
				cy.get('.form-control').click();
			});

	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.Uilogin(ADMIN_USERNAME, ADMIN_PASSWORD);
		cy.visit('/admin/ca-director');
	});


	it('Should allow CA Director to edit first unassigned enrollee on the table', () => {
		cy.isHidden('.col-sm-5.text-right'); // options for "Assign To CA", Unassign From CA", "Mark As Ineligible"
		basePage.logMessage(
			'The Assign To CA, Unassign From CA and Mark As Ineligible buttons are hidden'
		);
		cy.contains('Show Unassigned Patients Only').click();
		caDirectorPage.clickOnEditFirstEnrollee();
		cy.isVisible('h3').and('contain', 'Edit Patient Data');
		cy.isVisible('h4').and('contain', 'Demographics');
		cy.isVisible('h4').and('contain', 'Status');
		cy.isVisible('h4').and('contain', 'Patient Phones');
		cy.isVisible('h4').and('contain', 'Patient Insurances');
		cy.get('#first-name').clear().type('testName');
		cy.get('#last-name').clear().type('testSurname');
		cy.get('#address-2').clear().type('testAddress');
		cy.get('#zip').clear().type('23453');
		cy.get('#city').clear().type('TESTER CITY');
		cy.get('.modal-ok-button')
			.wait(2000)
			.scrollIntoView()
			.should('be.visible')
			.click();
		basePage.logMessage('It successfully edits enrollee information');
	});

	it('Should assign edited enrollee to test care ambassador', () => {
		selectTestEnrollee();
		cy.isVisible('.col-sm-5.text-right'); // options for "Assign To CA", Unassign From CA", "Mark As Ineligible"
		basePage.logMessage(
			'The Assign To CA, Unassign From CA and Mark As Ineligible buttons are visible'
		);
		caDirectorPage.clickAssignToCa();
		cy.isVisible('.modal-container');
		cy.contains('Assign Care Ambassador to selected Patient(s)');
		cy.get('.vs__selected-options > .form-control')
			.click()
			.type(TESTER_CA)
			.type('{enter}');

		cy.get('.modal-ok-button').click({ force: true });
		caDirectorPage.getEnrolleeTable();
		cy.contains('testName')
			.parent('tr')
			.within(() => {
				// all searches are automatically rooted to the found tr element
				cy.contains('testSurname');
				cy.contains(TESTER_CA);
				cy.contains('Call Queue');
				cy.contains('testAddress');
				cy.contains('TESTER CITY');
			});
	});

	it('Should allow unassign from care ambassador', () => {
		selectTestEnrollee();
		cy.contains('Unassign From CA').click();
		cy.isVisible('.modal-container');

		cy.get('.modal-container').should(
			'contain',
			'You have selected 1 patient(s).'
		);
		cy.get('.modal-ok-button').click();
		cy.contains('testName')
			.parent('tr')
			.within(() => {
				cy.contains(TESTER_CA).should('not.exist');
			});
	});
});
