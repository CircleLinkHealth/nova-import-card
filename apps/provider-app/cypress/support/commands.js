// ***********************************************
// The following custom commands can be called from
// within the .spec.js test files
// ************************************************

Cypress.Commands.add("login", (email, password) => {
  cy.clearCookies;
  cy.clearLocalStorage;
  cy.get('input[name = "email"]').type(email);
  cy.get('input[name = "password"]').type(password);
  cy.get("#login-submit-button").click();
});

Cypress.Commands.add("isVisible", (selector) => {
  cy.get(selector).should("be.visible");
});

Cypress.Commands.add("isHidden", (selector) => {
  cy.get(selector).should("not.exist");
});

Cypress.Commands.add("setViewport", (size) => {
  if (Cypress._.isArray(size)) {
    cy.viewport(size[0], size[1]);
  } else {
    cy.viewport(size);
  }
});

Cypress.Commands.add("Uilogin", (username, password) => {
  cy.get("#email").type(username, {
    log: false,
  });
  cy.get("#password").type(password, {
    log: false,
  });
  cy.get("#login-submit-button").click();
});

require("cypress-downloadfile/lib/downloadFileCommand");

import "cypress-file-upload";
