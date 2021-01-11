import BasePage from '../BasePage';

export default class Under20MinutesReportPage extends BasePage {
	clickOnGo() {
		cy.get('#find').click();
	}
	selectMonth() {
		cy.get('#selectMonth').click();
	}
}
