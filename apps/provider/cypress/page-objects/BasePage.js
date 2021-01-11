export default class BasePage {
	pause(ms) {
		cy.wait(ms);
	}

	logMessage(message) {
		cy.log(message);
	}

	setMobileViewport() {
		cy.viewport('iphone-x');
	}

	setSmallDesktopViewport() {
		cy.viewport('macbook-13');
	}

	setLargeDesktopViewport() {
		cy.viewport('macbook-15');
	}
}
