/*

SET ENVIRONMENT VARIABLES IN cypress.env.json BEFORE RUNNING TESTS!!

*/

export const ADMIN_USERNAME = Cypress.env("adminUsername");
export const ADMIN_PASSWORD = Cypress.env("adminPassword");
export const NURSE_USERNAME = Cypress.env("nurseUsername");
export const NURSE_PASSWORD = Cypress.env("nursePassword");
export const PROVIDER_USERNAME = Cypress.env("providerUsername");
export const PROVIDER_PASSWORD = Cypress.env("providerPassword");
export const CARE_AMBASSADOR_USERNAME = Cypress.env("careAmbassadorUsername");
export const CARE_AMBASSADOR_PASSWORD = Cypress.env("careAmbassadorPassword");
export const TESTER_GMAIL = Cypress.env("testerGmail");
export const TESTER_PROVIDER = Cypress.env("testerProvider");
export const TESTER_CA = Cypress.env("testerCareAmbassador");
export const TESTER_PRACTICE_ID = Cypress.env("testerPracticeId");
export const PRACTICE_SLUG = Cypress.env("practiceSlug");
