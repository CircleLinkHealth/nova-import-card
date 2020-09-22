import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import {
	ADMIN_USERNAME,
	ADMIN_PASSWORD,
	TESTER_PRACTICE_ID,
	TESTER_PROVIDER,
} from '../../support/config';
import faker from 'faker';

describe('Tests admin can create new patient', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const patientEmail = faker.internet.exampleEmail();
	const agentEmail = faker.internet.exampleEmail();
	const todaysDate = Cypress.moment().format('YYYY-MM-DD');
	const patientName = faker.name.firstName();
	const patientSurname = faker.name.lastName();
	const patientFullName = patientName + ' ' + patientSurname;
	const MRN = faker.random.number();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(ADMIN_USERNAME, ADMIN_PASSWORD);
	});

	it('Should fill out form and save profile', () => {
		cy.visit('/manage-patients/demographics/create');
		cy.isHidden('.patient__actions > .navbar-nav');
		cy.get('#first_name').type(patientName);
		cy.get('#last_name').type(patientSurname);
		cy.get('input[dusk="male-gender"]').should('not.be.checked').click({
			force: true,
		});
		cy.get('input[dusk="female-gender"]').should('not.be.checked');
		cy.get('input[name="preferred_contact_language"]').should('be.checked');
		cy.get('#mrn_number').type(MRN);
		cy.get('#home_phone_number').type(faker.phone.phoneNumberFormat()); // default location is the US - RETURNS FORMAT XXX-XXX-XXXX -- faker.phone.phoneNumber() RETURNS (xxx) xxx-xxx-xxxx
		cy.get('#mobile_phone_number').type(faker.phone.phoneNumberFormat());
		cy.get('#email').type(patientEmail);
		cy.get('#address').type(faker.address.streetAddress());
		cy.get('#city').type(faker.address.city());
		cy.get('#zip').type(faker.address.zipCode());
		cy.get('#agent_name').type(faker.name.firstName());
		cy.get('#agent_relationship').type('Test Relationship');
		cy.get('#agent_email').type(agentEmail);
		cy.get('#agent_telephone').type(faker.phone.phoneNumberFormat());
		cy.get('#days')
			.select(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], { force: true })
			.invoke('val')
			.should('deep.equal', ['1', '2', '3', '4', '5']);

		cy.get('#window_start').click('left').type('09:00');

		cy.get('#window_end').click('left').type('04:00');

		cy.get('#frequency').should('contain', '2x Monthly').select('3x Monthly');

		cy.get('#contactMethodCCT')
			.should('not.be.checked')
			.check({
				force: true,
			})
			.should('be.checked');

		cy.get(
			':nth-child(3) > .row > :nth-child(2) > .btn-group > .btn > .filter-option'
		) // Gets "Time Zone" dropdown element and validates pre-set value of "Eastern Time"
			.contains('Eastern Time');

		cy.get('input[name="consent_date"]').should('have.value', todaysDate);

		cy.get('#program_id').select(TESTER_PRACTICE_ID);
		cy.get('#provider_id').select(TESTER_PROVIDER);
		cy.get('#perform-status-select').should('contain', 'Enrolled');

		cy.get('.btn-primary').click({
			force: true,
		});
		cy.isVisible('.patient__actions > .navbar-nav');
	});
	it('Should verify that patient exists', () => {
		cy.visit('/manage-patients/dashboard');
		cy.get('.VueTables__mrn-filter-wrapper > .form-control').type(MRN);
		cy.contains(MRN)
			.parent('tr')
			.within(() => {
				cy.contains(patientFullName);
				cy.contains('Enrolled');
				cy.contains('Approve Now');
			});
	});
});
