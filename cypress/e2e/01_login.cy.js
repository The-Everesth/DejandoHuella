describe('Login ciudadano', () => {

  it('inicia sesión correctamente', () => {

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

})