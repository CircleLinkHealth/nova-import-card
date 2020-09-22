import LoginPage from '../../page-objects/pages/LoginPage';
import BasePage from '../../page-objects/BasePage';
import {
	ADMIN_USERNAME,
	ADMIN_PASSWORD,
	TESTER_PRACTICE_ID,
} from '../../support/config';
import CreateNewUserPage from '../../page-objects/pages/CreateNewUserPage';
import faker from 'faker';

describe('Tests New User Page', () => {
	const loginPage = new LoginPage();
	const basePage = new BasePage();
	const createNewUserPage = new CreateNewUserPage();

	beforeEach(function () {
		basePage.setLargeDesktopViewport();
		cy.visit('/');
		loginPage.login(ADMIN_USERNAME, ADMIN_PASSWORD);
	});

	it('Should make assertions on New User page', () => {
		cy.visit('/admin/users/create');
		cy.isVisible('h1').should('contain', 'Create New User');
		cy.isVisible('.col-md-12'); // Create New User Form
		cy.isVisible('.btn-danger'); // Cancel btn
		cy.isVisible('#togglePrograms');
		cy.isVisible('.btn-success'); // Create btn
	});

	it('Should create new user with CLH Care Coach role', () => {
		cy.visit('/admin/users/create');
		createNewUserPage.enterUsername(faker.internet.userName());
		createNewUserPage.enterEmail(faker.internet.exampleEmail());
		createNewUserPage.enterPassword('qwerty123!@#');
		createNewUserPage.confirmPassword('qwerty123!@#');
		createNewUserPage.enterFirstName('myTest');
		createNewUserPage.enterLastName('User');
		createNewUserPage.selectPractice(TESTER_PRACTICE_ID);
		createNewUserPage.selectRole('CLH Care Coach');
		createNewUserPage.clickOnCreateUser();
		cy.url('contains', '/edit');
	});

	it('Should delete new user with CLH Care Coach role', () => {
		cy.visit('/admin/users');
		cy.get('tbody > :nth-child(1) > :nth-child(2)')
			.parent('tr')
			.within(() => {
				// all searches are automatically rooted to the found tr element
				cy.contains('Mytest User');
				cy.contains('CLH Care Coach');
				cy.get('.btn-danger').click();
				cy.contains('Mytest User').should('not.exist');
			});
	});
});
