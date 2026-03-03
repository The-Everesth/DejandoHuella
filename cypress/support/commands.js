// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add('login', () => {
    // Ingresar email y password
  const email = Cypress.env('USER_EMAIL')
  const password = Cypress.env('USER_PASSWORD')
    // Visitar la página de login
  cy.visit('/login')
    // Ingresar email y password
  cy.get('[data-cy="email"], input[name="email"]').type(email)
  cy.get('[data-cy="password"], input[name="password"]')
    .type(password, { log: false })
  cy.get('[data-cy="login-submit"], button[type="submit"]').click()
    // Validar que no se redirija a la página de login
  cy.url().should('not.include', '/login')

})