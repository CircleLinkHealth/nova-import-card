import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import { ADMIN_USERNAME, ADMIN_PASSWORD } from '../../support/config';

describe('Tests Medication Group Mapping', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	function alertMessage(message) {
		cy.on('window:alert', str => {
			expect(str).to.equal(message);
		});
	}

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(ADMIN_USERNAME, ADMIN_PASSWORD);
		cy.visit('/admin/medication-groups-maps');
		cy.get('.form-control').as('medNameInputField');
		cy.get('.select2-hidden-accessible').as('medTypeSelect');
		cy.get('.col-md-3 > .btn').as('Store');
	});

	it('should add medication group map', () => {
		cy.contains('h3', 'Add new Medication Group Map');
		cy.get('@Store').should('have.attr', 'disabled');
		cy.get('@medNameInputField').type('TEST MED GROUP MAP');
		cy.get('@medTypeSelect').select('Blood Pressure Meds', {
			force: true,
		});
		cy.get('@Store').click();
		alertMessage('Successfully stored mapping');
	});

	it('should delete medication group map', () => {
		cy.get('#app').should('contain', 'TEST MED GROUP MAP');
		cy.contains('TEST MED GROUP MAP')
			.parent('li')
			.within(() => {
				cy.get('.btn > span > .glyphicon').click();
			});
		cy.contains('TEST MED GROUP MAP').should('not.exist');
	});
});
