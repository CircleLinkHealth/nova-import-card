import LoginPage from '../../page-objects/pages/LoginPage';
import { NURSE_USERNAME, NURSE_PASSWORD } from '../../support/config';

describe('Tests Navbar for Nurse', () => {
	const loginPage = new LoginPage();
	before(function () {
		cy.visit('/');
		cy.wait(3000);
		loginPage.login(NURSE_USERNAME, NURSE_PASSWORD);
	});

	it('should display Scheduled Activities page content', function () {
		cy.contains('Activities').should(
			'have.attr',
			'href',
			Cypress.config().baseUrl + '/manage-patients/patient-call-list'
		);
	});

	/*it('should display Work Schedule page content', function () {
		cy.get('#work-schedule-link') // Gets "Work Schedule" from User Dropdown
			.contains('Work Schedule')
			.should(
				'have.attr',
				'href',
				Cypress.config().baseUrl + '/care-center/work-schedule'
			);
			
	});
	*/
});
