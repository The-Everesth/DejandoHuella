describe('Solicitud de cita', () => {
    //Iniciar sesión como ciudadano
  beforeEach(() => cy.login())

  it('solicita cita y redirige a Mis Citas', () => {
    const d = new Date()
    d.setDate(d.getDate() + 1)
    const date = d.toISOString().slice(0, 10)
    // Visitar la página de citas
    cy.visit(`/appointments/create/c_7/srv_consulta?date=${date}`)

    //Se ingresa al formulario de citas
    const apptForm = () =>
      cy.get('form[method="POST"]')
        .filter(':has(input[name="clinic_id"])')
        .filter(':has(input[name="medical_service_id"])')
        .first()

    // Interceptar el POST del form de citas
    apptForm().then($form => {
      const action = $form.attr('action')
      expect(action, 'action del form de citas').to.be.a('string').and.not.be.empty
      cy.intercept('POST', action).as('storeAppointment')
    })

    // Validar horarios disponibles
    cy.get('select[name="start_at"] option').then($opts => {
      const values = [...$opts].map(o => o.value)
      if (values.length === 1 && (values[0] === '' || values[0] == null)) {
        throw new Error('No hay horarios disponibles para la fecha elegida.')
      }
    })

    // Llenar campos requeridos
    cy.get('select[name="pet_id"]').select(0)
    cy.get('input[name="contact"]').clear().type(`cypress_${Date.now()}@mail.com`)
    cy.get('select[name="start_at"]').select(0)
    cy.get('textarea[name="notes"]').type('Cita generada por Cypress.')

    // Submit del form de citas
    apptForm().within(() => {
      cy.contains('button', /Solicitar cita/i).click()
    })

    // Esperar el POST y luego validar redirect
    cy.wait('@storeAppointment', { timeout: 15000 })
      .its('response.statusCode')
      .should('be.oneOf', [200, 201, 302])

    cy.location('pathname', { timeout: 15000 }).should('eq', '/my/appointments')
  })
})