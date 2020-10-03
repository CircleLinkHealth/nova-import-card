import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import {
	PROVIDER_USERNAME,
	PROVIDER_PASSWORD,
	} from '../../support/config';
import PatientList from '../../page-objects/pages/PatientList';
import ViewCareplan from '../../page-objects/pages/ViewCareplan';
import DataGenerator from '../../fixtures/data-generator';
import Floater from '../../page-objects/components/Floater';

describe('Tests Care Plan Setup by Provider', () => {
	const data = new DataGenerator();
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const patientList = new PatientList();
	const viewCareplan = new ViewCareplan();
	const floater = new Floater();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.Uilogin(PROVIDER_USERNAME, PROVIDER_PASSWORD);
		cy.visit('/manage-patients/listing');
		cy.contains('Patient List').should('be.visible');
	});

	it('Should make assertions on the Care Plan page', () => {
		patientList.selectPatient('1');
		cy.contains('Care Plan');
		cy.url('contains', '/view-careplan');
		cy.isVisible('.col-md-12 > .row > .text-right > :nth-child(1)'); //Upload Button
		cy.isVisible('.action-button > .icon'); //"+" floating button
		cy.contains('Upload PDF');
		cy.contains('h2', 'We Are Managing');
		cy.contains('h2', 'Your Health Goals');
		cy.contains('h2', 'Medications');
		cy.contains('h2', 'Watch out for ');
		cy.contains('h2', 'We are Informing You About:');
		cy.contains('h2', 'Check In Plan');
		cy.contains('h2', 'Follow These Instructions ');
		cy.contains('h2', 'Allergies');
		cy.contains('h2', 'Social Services');
		cy.contains('h2', 'Care Team');
		cy.contains('h2', 'Appointments');
		cy.contains('h2', 'Other Notes');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});

	it('Should add new condition', () => {
		patientList.selectPatient('2'); // passes string parameter to :nth-child, allowing to determine which patient to choose on the table in asc order
		viewCareplan.clickOnWeAreManaging();
		viewCareplan.addCondition(data.generateWord());
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should delete condition', () => {
		patientList.selectPatient('3');
		viewCareplan.clickOnWeAreManaging();
		viewCareplan.addCondition('TEST CONDITION');
		cy.reload();
		viewCareplan.clickOnWeAreManaging();
		viewCareplan.deleteCondition('TEST CONDITION');
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});

	it('Should add and delete Allergies ', () => {
		patientList.selectPatient('9');
		viewCareplan.clickOnAddAllergies();
		viewCareplan.addAllergies('TEST ALLERGY');
		viewCareplan.clickOnAddAllergies();
		viewCareplan.deleteAllergies('TEST ALLERGY');
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});

	/*it('Should add and delete Social Services ', () => {
		patientList.selectPatient('7');
		viewCareplan.clickOnSocialServices();
		viewCareplan.addSocialServices('SOCIAL SERVICES');
		viewCareplan.deleteSocialServices();
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
*/
	it('Should add Appointment ', () => {
		patientList.selectPatient('6');
		viewCareplan.clickOnAddAppointment();
		viewCareplan.setAppointmentFor('THIS IS A TEST');
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});

	it('Should add and delete Other Notes ', () => {
		patientList.selectPatient('5');
		viewCareplan.addOtherNotes('THIS IS A NOTE');
		viewCareplan.deleteOtherNotes();
		cy.reload();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});

	it('Should verify floating "+" button functionality', () => {
		patientList.selectPatient('9');
		floater.triggerHoverOnFloater();
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
});
