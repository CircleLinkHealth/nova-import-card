import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import { ADMIN_USERNAME, ADMIN_PASSWORD } from '../../support/config';
import PatientList from '../../page-objects/pages/PatientList';

describe('Tests CP PDF upload', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const patientList = new PatientList();

	before(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(ADMIN_USERNAME, ADMIN_PASSWORD);
		cy.visit('/manage-patients/listing');
		patientList.selectPatient('1');
	});

	it('Should upload pdf care plan and Should revert to editable, web care plan', () => {
		const filePath = 'sample.pdf';
		cy.contains('Upload PDF').click();
		cy.get('#upload-pdf-dropzone').attachFile(filePath, {
			subjectType: 'drag-n-drop',
		});
		cy.contains('REVERT TO EDITABLE CAREPLAN FROM CCD/PATIENT DATA');
		cy.isVisible('.pdf-title > a'); // CarePlan uploaded on mm/dd/yyyy at hh:mm:ss
		cy.isVisible('span > .glyphicon');
		cy.get('.col-md-5 > .btn').click(); // clicks on REVERT TO EDITABLE CAREPLAN FROM CCD/PATIENT DATA
		cy.isVisible('#v-pdf-careplans');
		cy.contains('Upload PDF');
		cy.contains('Care Plan');
		cy.contains('Print This Page');
	});
});
