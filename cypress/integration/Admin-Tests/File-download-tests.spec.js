// ********************************************************************************
// This test suite should be expanded to cover file downloads (pdf/csv/excel) for the following pages:
//  - Care Plan Print List
//  - Under 20 Minutes Report
//  - Ops Dashboard
//  - Nurse Performance Report
//  - Billable Patients Report
//  - Approve Billable Patients
//  - Patient List
// ********************************************************************************

/// <reference types="cypress" />

import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import { ADMIN_USERNAME, ADMIN_PASSWORD } from '../../support/config';

describe('Downloads files', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const todaysDate = Cypress.moment().format('YYYY-MM-DD');

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.Uilogin(ADMIN_USERNAME, ADMIN_PASSWORD);
	});

	/*it('Should download PDF file from Patient List page', () => {
		cy.visit('/manage-patients/listing');
		cy.downloadFile(
			Cypress.config().baseUrl + '/manage-patients/listing/pdf',
			'File-Downloads',
			'patientList.pdf'
		);
	});
*/
	it('Should download CSV file from Ops Dashboard page', () => {
		cy.visit('/ops-dashboard/index');
		cy.downloadFile(
			Cypress.config().baseUrl +
			'/ops-dashboard/index/csv' +
			'?date=' +
			todaysDate,
			'File-Downloads',
			'Ops-Dashboard.csv'
		);
	});

	it('Should download Excel file from Nurse Performance Report page', () => {
		cy.visit('/reports/nurse/weekly');
		cy.downloadFile(
			Cypress.config().baseUrl +
			'/reports/nurse/weekly/excel' +
			'?start_date=' +
			todaysDate +
			'&end_date=' +
			todaysDate,
			'File-Downloads',
			'Nurse-performance-report.xlsx'
		);
	});
});
