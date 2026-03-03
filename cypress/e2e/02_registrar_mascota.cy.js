describe('Registro de mascota', () => {
    //Iniciar sesión como ciudadano
  beforeEach(() => {
    cy.login()
  })
  //Registro de mascota
  it('el ciudadano registra una mascota', () => {
    
    const petName = `Mascota Cypress`

    cy.visit('/my/pets/create')
    // Llenar el formulario de registro de mascota
    cy.get('input[name="name"]').type(petName)
    cy.get('select[name="species"]').select('perro')
    cy.get('select[name="sex"]').select('macho')
    cy.get('input[name="breed"]').type('Labrador')
    cy.get('input[name="ageYears"]').type('2')
    cy.get('textarea[name="notes"]').type('Registro Cypress')
    cy.get('button[type="submit"]').click()
    cy.visit('/my/pets')

    // Validar que la mascota se haya registrado correctamente
    cy.contains(petName).should('exist')

  })

})