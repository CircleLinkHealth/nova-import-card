import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import PatientList from '../../page-objects/pages/PatientList';
import { PROVIDER_USERNAME, PROVIDER_PASSWORD } from '../../support/config';
import Floater from '../../page-objects/components/Floater';
import ObservationsPage from '../../page-objects/pages/ObservationsPage';

describe('Tests provider Ability to Input Observations on Patient', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const patientList = new PatientList();
	const floater = new Floater();
	const observationsPage = new ObservationsPage();

	beforeEach(() => {
		basePage.setLargeDesktopViewport();
		cy.visit('/auth/login');
		loginPage.Uilogin(PROVIDER_USERNAME, PROVIDER_PASSWORD);
		cy.visit('/manage-patients/listing');
		patientList.selectPatient('5');
		cy.wait(3000);
		floater.addObservation();
	});

	it('Should add Biometrics - Blood Pressure (mmHg)', () => {
		observationsPage.addObservation(
			'Blood Pressure (mmHg)',
			'device',
			'120/90'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for blood pressure');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Biometrics - Weight (lb)', () => {
		observationsPage.addObservation('Weight (lb)', 'Patient Reported', '250');
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for weight');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Biometrics - Blood Sugar (mm/dL)', () => {
		observationsPage.addObservation('Blood Sugar (mg/dL)', 'Lab Test', '200');
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for blood sugar');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Biometrics - A1c(%)', () => {
		observationsPage.addObservation(
			'A1c (%)',
			'Office Visit (OV) reading',
			'7.4'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for A1c');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Biometrics - Smoking(# per day', () => {
		observationsPage.addObservation(
			'Smoking (# per day)',
			'Patient Reported',
			'10'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for cigarette count');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Biometrics - Blood Pressure Meds', () => {
		observationsPage.addObservation(
			'Blood Pressure meds',
			'Patient Reported',
			'Y'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage(
			'Successfully added observation for blood pressure meds'
		);
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Medication - Cholesterol Meds', () => {
		observationsPage.addObservation(
			'Cholesterol meds',
			'Patient Reported',
			'Y'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for cholesterol meds');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Medication - Oral Diabetes Meds', () => {
		observationsPage.addObservation(
			'Oral diabetes meds',
			'Patient Reported',
			'N'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage(
			'Successfully added observation for oral diabetes meds meds'
		);
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Medication - Breathing Meds', () => {
		observationsPage.addObservation('Breathing meds', 'Patient Reported', 'Y');
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for breathing meds');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Symptom - Shortness of Breath', () => {
		observationsPage.addObservation(
			'Shortness of breath',
			'Patient Reported',
			'5'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage(
			'Successfully added observation for shortness of breath'
		);
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Symptom - Chest Pain or Chest Tightness', () => {
		observationsPage.addObservation(
			'Chest pain or chest tightness',
			'Patient Reported',
			'9'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage(
			'Successfully added observation for chest pain or chest tightness'
		);
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Symptom - Fatigue', () => {
		observationsPage.addObservation('Fatigue', 'Patient Reported', '1');
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for fatigue');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Lifestyle - Exercise 20 Minutes', () => {
		observationsPage.addObservation(
			'Exercise 20 minutes',
			'Patient Reported',
			'Y'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for Exercise');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Lifestyle - Following Healthy Diet', () => {
		observationsPage.addObservation(
			'Following Healthy Diet',
			'Patient Reported',
			'Y'
		);
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for Healthy Diet');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
	it('Should add Lifestyle - Diabetes Diet', () => {
		observationsPage.addObservation('Diabetes diet', 'Patient Reported', 'N');
		cy.url().should('contain', '/summary');
		basePage.logMessage('Successfully added observation for Diabetes diet');
		cy.get(':nth-child(5) > .dropdown-toggle').click();
		cy.contains('Logout').click();
	});
});
