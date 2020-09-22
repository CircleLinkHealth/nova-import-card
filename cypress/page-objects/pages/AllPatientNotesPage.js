import BasePage from '../BasePage';

export default class AllPatientNotesPage extends BasePage {
	clickOnGo() {
		cy.get('#find').click();
	}
	checkOnlyForwardedNotes() {
		cy.get('#mail_filter').check();
	}
	checkAllForwardedNotesForAllPractices() {
		cy.get('#admin_filter').check();
	}
	selectRange(value) {
		cy.get('#range').select(value);
	}
	selectPracticeOrProvider() {
		cy.get('.select2-search__field').click();
		cy.get('#select2-getNotesFor-results');
	}
}
