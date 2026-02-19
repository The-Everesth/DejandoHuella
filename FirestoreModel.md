# Firestore Domain Model

La fuente de verdad del dominio pasará a Firestore. A continuación se describe la estructura de colecciones y prefijos de documento que utilizaremos.

- **users** – documentos por usuario del sistema (se mantiene MySQL para auth)
  - ID: `u_{userId}`
  - campos básicos: name, email, roles, timestamps
  - subcolección `notifications/items/{notifId}` para notificaciones de usuario

- **clinics** – clínicas veterinarias
  - ID: `c_{clinicId}`
  - fields: userId (dueño/veterinario), name, phone, email, address, description, opening_hours, website, is_public, created_at, updated_at

- **refuges** – refugios (similar a clinics pero con prefijo r_)
  - ID: `r_{refugioId}`

- **pets** – mascotas
  - ID: `p_{petId}`
  - fields: ownerId, name, species, breed, sex, birth_date, color, is_sterilized, is_vaccinated, description, photo_path, status (publicada|en_proceso|adoptada|oculta), timestamps

- **adoptionRequests** – solicitudes de adopción
  - ID: `ar_{requestId}`
  - fields: adoptionPostId, applicantId, message, status, timestamps

- **appointments** – citas médicas
  - ID: `a_{appointmentId}`
  - fields: clinicId, medicalServiceId, petId, ownerId, vetUserId (igual clinic.user_id), scheduled_at, status, notes, timestamps

- **supportTickets** – tickets de soporte
  - ID: `t_{ticketId}`
  - campos como en MySQL (userId, subject, priority, message, status, etc.)

Las demás colecciones (e.g. medicalServices, adoptionPosts) pueden crearse con prefijos propios.

---

Este documento sirve de referencia para los servicios de Firestore que se implementarán en `app/Services/Firestore`.
