import LoginPage from "../../page-objects/pages/LoginPage";
import BasePage from "../../page-objects/BasePage";
import {
  ADMIN_USERNAME,
  ADMIN_PASSWORD,
  PRACTICE_SLUG,
} from "../../support/config";
import faker from "faker";

describe("Tests that Admin can create new practice staff user and then delete", () => {
  const basePage = new BasePage();
  const loginPage = new LoginPage();

  beforeEach(function() {
    basePage.setLargeDesktopViewport();
    cy.visit("/");
    loginPage.login(ADMIN_USERNAME, ADMIN_PASSWORD);
    cy.visit("/practices/" + PRACTICE_SLUG + "/staff");
  });

  it("Should Create New Practice Staff User", () => {
    cy.get("#submit").click();

    cy.get("#first_name").type("Kokos");
    cy.get("#last_name").type("Vrakas");
    cy.get("#email").type(faker.internet.exampleEmail());
    cy.get("#canApproveAllCareplans")
      .should("not.be.checked")
      .check({ force: true })
      .should("be.checked");
    cy.contains("Select Clinical Level");
    cy.get(
      ":nth-child(1) > :nth-child(3) > .select-wrapper > input.select-dropdown"
    ).click();
    cy.get("#suffix")
      .children()
      .should("have.length", 11)
      .and(
        "contain",
        "Select Clinical Level",
        "Non-clinical",
        "MD",
        "DO",
        "NP",
        "PA",
        "RN",
        "LPN",
        "PN",
        "CNA",
        "MA"
      );
    cy.contains("MD").click();
    cy.get(":nth-child(6) > .col > .green").click();
    cy.contains("Kokos Vrakas was successfully updated");
    basePage.logMessage("Successfully Added New Practice Staff Role");
    cy.wait(4000);
    cy.contains("td", "Kokos Vrakas") // gives you the cell
      .siblings(".td-trash") // gives you the cell with class .td-trash in the row
      .click();

    cy.get("table").should("not.contain", "td", "Kokos Vrakas");
    basePage.logMessage("Successfully Deleted Newly Added Practice Staff Role");
  });
});
