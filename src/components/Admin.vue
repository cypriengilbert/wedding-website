<template>
  <div class="admin-page">
    <!-- Page de connexion -->
    <div v-if="!isAuthenticated" class="admin-login">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <h2 class="card-title text-center mb-4">Administration</h2>
                <form @submit.prevent="handleLogin">
                  <div class="mb-3">
                    <label for="admin-password" class="form-label">Mot de passe</label>
                    <input
                      type="password"
                      class="form-control"
                      id="admin-password"
                      v-model="password"
                      placeholder="Entrez le mot de passe"
                      required
                    >
                  </div>
                  <div v-if="loginError" class="alert alert-danger">{{ loginError }}</div>
                  <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                  </div>
                </form>
                <div class="mt-3 text-center">
                  <a href="#" @click.prevent="goHome">Retour au site</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Interface d'administration -->
    <div v-else class="admin-interface">
      <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
          <span class="navbar-brand">Administration - Mariage</span>
          <div>
            <button class="btn btn-outline-light btn-sm" @click="refreshData">
              <span v-if="loading">Chargement...</span>
              <span v-else>Actualiser</span>
            </button>
            <button class="btn btn-outline-light btn-sm ms-2" @click="handleLogout">
              Déconnexion
            </button>
          </div>
        </div>
      </nav>

      <div class="container-fluid mt-4">
        <!-- Recherche -->
        <div class="row mb-4">
          <div class="col-md-6">
            <input
              type="text"
              class="form-control"
              v-model="searchQuery"
              placeholder="Rechercher un invité (nom, prénom, email)..."
              @input="handleSearch"
            >
          </div>
          <div class="col-md-6 text-end">
            <button class="btn btn-success me-2" @click="openCreateGuestModal">
              Ajouter un invité
            </button>
            <button class="btn btn-primary" @click="showCreateInvitationModal = true">
              Créer une invitation
            </button>
          </div>
        </div>

        

        <!-- Statistiques par événement -->
        <div class="row mb-4">
          <div v-for="event in (events || [])" :key="event.id" class="col-md-4 mb-3">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">{{ event.name }}</h6>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-4">
                    <div class="text-success">
                      <h5 class="mb-0">{{ getEventStats(event.id).attending }}</h5>
                      <small>Présents</small>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="text-danger">
                      <h5 class="mb-0">{{ getEventStats(event.id).notAttending }}</h5>
                      <small>Non présents</small>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="text-warning">
                      <h5 class="mb-0">{{ getEventStats(event.id).pending }}</h5>
                      <small>En attente</small>
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="text-muted">
                    Total: {{ getEventStats(event.id).total }} invitations
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tableau des invités -->
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th v-for="event in (events || [])" :key="event.id" class="text-center">
                  {{ event.name }}
                </th>
                <th class="text-center">Nombre de personnes</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!loading && (!filteredGuests || filteredGuests.length === 0)">
                <td :colspan="(events ? events.length : 0) + 5" class="text-center text-muted py-4">
                  <p v-if="loading">Chargement des données...</p>
                  <p v-else>Aucun invité trouvé. Les données sont peut-être en cours de chargement ou il n'y a pas encore d'invités dans la base de données.</p>
                </td>
              </tr>
              <tr v-for="guest in (filteredGuests || [])" :key="guest.id">
                <td
                  :class="{ 'editable-cell': true }"
                  @dblclick="startEdit(`lastname-${guest.id}`, guest.lastname || '')"
                >
                  <input
                    v-if="editingCell === `lastname-${guest.id}`"
                    type="text"
                    class="form-control form-control-sm"
                    v-model="editingValues[`lastname-${guest.id}`]"
                    @blur="saveGuestField(guest.id, 'lastname', editingValues[`lastname-${guest.id}`])"
                    @keyup.enter="saveGuestField(guest.id, 'lastname', editingValues[`lastname-${guest.id}`])"
                    @keyup.esc="cancelEdit(`lastname-${guest.id}`)"
                    ref="editingInput"
                  >
                  <span
                    v-else
                    class="editable-text"
                    title="Double-cliquez pour modifier"
                  >
                    {{ guest.lastname || '\u00A0' }}
                  </span>
                </td>
                <td
                  :class="{ 'editable-cell': true }"
                  @dblclick="startEdit(`firstname-${guest.id}`, guest.firstname || '')"
                >
                  <input
                    v-if="editingCell === `firstname-${guest.id}`"
                    type="text"
                    class="form-control form-control-sm"
                    v-model="editingValues[`firstname-${guest.id}`]"
                    @blur="saveGuestField(guest.id, 'firstname', editingValues[`firstname-${guest.id}`])"
                    @keyup.enter="saveGuestField(guest.id, 'firstname', editingValues[`firstname-${guest.id}`])"
                    @keyup.esc="cancelEdit(`firstname-${guest.id}`)"
                    ref="editingInput"
                  >
                  <span
                    v-else
                    class="editable-text"
                    title="Double-cliquez pour modifier"
                  >
                    {{ guest.firstname || '\u00A0' }}
                  </span>
                </td>
                <td
                  :class="{ 'editable-cell': true }"
                  @dblclick="startEdit(`email-${guest.id}`, guest.email || '')"
                >
                  <input
                    v-if="editingCell === `email-${guest.id}`"
                    type="email"
                    class="form-control form-control-sm"
                    v-model="editingValues[`email-${guest.id}`]"
                    @blur="saveGuestField(guest.id, 'email', editingValues[`email-${guest.id}`])"
                    @keyup.enter="saveGuestField(guest.id, 'email', editingValues[`email-${guest.id}`])"
                    @keyup.esc="cancelEdit(`email-${guest.id}`)"
                    ref="editingInput"
                  >
                  <span
                    v-else
                    class="editable-text"
                    title="Double-cliquez pour modifier"
                  >
                    {{ guest.email || '-' }}
                  </span>
                </td>
                <td v-for="event in (guest.events || [])" :key="event.event_id" class="text-center">
                  <div v-if="event.has_invitation" class="d-inline-flex align-items-center gap-1">
                    <div class="btn-group" role="group">
                      
                    </div>
                    <span 
                      class="badge" 
                      :class="getBadgeClass(event)"
                      @click="cycleInvitationStatus(event.invitation_id, guest.id, event.event_id, event.attending, event.responded_at)"
                      :disabled="updatingInvitations[`${guest.id}-${event.event_id}`]"
                      style="cursor: pointer;"
                      title="Cliquez pour changer le statut"
                    >
                      <span v-if="updatingInvitations[`${guest.id}-${event.event_id}`]">...</span>
                      <span v-else>{{ getStatusText(event) }}</span>
                    </span>
                    <button 
                      class="btn btn-sm btn-link text-danger p-0" 
                      @click="deleteInvitationDirectly(event.invitation_id, guest.id, event.event_id)"
                      :disabled="deletingInvitations[`${guest.id}-${event.event_id}`]"
                      title="Supprimer l'invitation"
                      style="line-height: 1; font-size: 0.9rem;"
                    >
                      <span v-if="deletingInvitations[`${guest.id}-${event.event_id}`]">...</span>
                      <span v-else>×</span>
                    </button>
                  </div>
                  <button 
                    v-else
                    class="btn btn-sm btn-outline-primary" 
                    @click="createInvitationDirectly(guest.id, event.event_id)"
                    :disabled="creatingInvitations[`${guest.id}-${event.event_id}`]"
                    title="Créer une invitation"
                  >
                    <span v-if="creatingInvitations[`${guest.id}-${event.event_id}`]">...</span>
                    <span v-else>+</span>
                  </button>
                </td>
                <td class="text-center">
                  {{ getTotalPersonCount(guest) }}
                </td>
                <td class="text-center">
                  <button
                    class="btn btn-sm btn-danger"
                    @click="deleteGuestDirectly(guest.id)"
                    :disabled="deletingGuests[guest.id]"
                    title="Supprimer l'invité"
                  >
                    <span v-if="deletingGuests[guest.id]">...</span>
                    <span v-else>🗑️</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal pour créer une invitation -->
      <div v-if="showCreateInvitationModal" class="modal show d-block" tabindex="-1" @click.self="closeModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Créer une invitation</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="modalError" class="alert alert-danger">{{ modalError }}</div>
              <div v-if="modalSuccess" class="alert alert-success">{{ modalSuccess }}</div>
              
              <div class="mb-3">
                <label class="form-label">Invité</label>
                <select class="form-select" v-model="selectedGuestId" :disabled="selectedGuestForInvitation">
                  <option value="">Sélectionner un invité</option>
                  <option v-for="guest in allGuests" :key="guest.id" :value="guest.id">
                    {{ guest.firstname }} {{ guest.lastname }} {{ guest.email ? `(${guest.email})` : '' }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Événement</label>
                <select class="form-select" v-model="selectedEventId">
                  <option value="">Sélectionner un événement</option>
                  <option v-for="event in (events || [])" :key="event.id" :value="event.id">
                    {{ event.name }}
                  </option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Annuler</button>
              <button type="button" class="btn btn-primary" @click="createInvitationModal" :disabled="!selectedGuestId || !selectedEventId">
                Créer l'invitation
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showCreateInvitationModal" class="modal-backdrop show"></div>

      <!-- Modal pour créer un invité -->
      <div v-if="showCreateGuestModal" class="modal show d-block" tabindex="-1" @click.self="closeCreateGuestModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Ajouter un invité</h5>
              <button type="button" class="btn-close" @click="closeCreateGuestModal"></button>
            </div>
            <div class="modal-body">
              <div v-if="createGuestError" class="alert alert-danger">{{ createGuestError }}</div>
              <div v-if="createGuestSuccess" class="alert alert-success">{{ createGuestSuccess }}</div>
              
              <div class="mb-3">
                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newGuest.firstname"
                  placeholder="Prénom"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newGuest.lastname"
                  placeholder="Nom"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  v-model="newGuest.email"
                  placeholder="email@example.com"
                >
                <small class="form-text text-muted">L'email est optionnel</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeCreateGuestModal">Annuler</button>
              <button type="button" class="btn btn-primary" @click="createGuestModal" :disabled="!newGuest.firstname || !newGuest.lastname || creatingGuest">
                <span v-if="creatingGuest">Création...</span>
                <span v-else>Créer l'invité</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showCreateGuestModal" class="modal-backdrop show"></div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue'
import { getAllGuestsWithInvitations, createInvitation, deleteInvitation, searchGuests, updateGuest, updateInvitationStatus, createGuest, deleteGuest } from '../services/admin'
import { adminConfig } from '../config/admin'

export default {
  name: 'Admin',
  setup() {
    const isAuthenticated = ref(false)
    const password = ref('')
    const loginError = ref('')
    const loading = ref(false)
    const guests = ref([])
    const events = ref([])
    const searchQuery = ref('')
    const allGuests = ref([])
    const showCreateInvitationModal = ref(false)
    const selectedGuestId = ref('')
    const selectedEventId = ref('')
    const selectedGuestForInvitation = ref(null)
    const modalError = ref('')
    const modalSuccess = ref('')
    const creatingInvitations = ref({})
    const deletingInvitations = ref({})
    const updatingInvitations = ref({})
    const deletingGuests = ref({})
    const editingCell = ref(null)
    const editingValues = ref({})
    const savingFields = ref({})
    const showCreateGuestModal = ref(false)
    const newGuest = ref({ firstname: '', lastname: '', email: '' })
    const createGuestError = ref('')
    const createGuestSuccess = ref('')
    const creatingGuest = ref(false)

    // Vérifie si l'utilisateur est déjà authentifié
    const checkAuth = () => {
      const session = localStorage.getItem(adminConfig.sessionStorageKey)
      if (session === 'true') {
        isAuthenticated.value = true
        loadData()
      }
    }

    // Initialise le mot de passe admin (à faire une seule fois)
    const initAdminPassword = () => {
      if (!localStorage.getItem(adminConfig.passwordStorageKey)) {
        localStorage.setItem(adminConfig.passwordStorageKey, adminConfig.defaultPassword)
      }
    }

    // Gère la connexion
    const handleLogin = () => {
      initAdminPassword()
      const storedPassword = localStorage.getItem(adminConfig.passwordStorageKey)
      
      if (password.value === storedPassword) {
        isAuthenticated.value = true
        localStorage.setItem(adminConfig.sessionStorageKey, 'true')
        loadData()
        loginError.value = ''
      } else {
        loginError.value = 'Mot de passe incorrect'
      }
    }

    // Gère la déconnexion
    const handleLogout = () => {
      isAuthenticated.value = false
      localStorage.removeItem(adminConfig.sessionStorageKey)
      password.value = ''
    }

    // Charge les données
    const loadData = async () => {
      loading.value = true
      try {
        const result = await getAllGuestsWithInvitations()
        console.log('Données chargées:', result)
        console.log('Nombre d\'invités:', result.guests?.length || 0)
        console.log('Nombre d\'événements:', result.events?.length || 0)
        guests.value = result.guests || []
        allGuests.value = result.guests || []
        events.value = result.events || []
        console.log('guests.value après assignation:', guests.value.length)
        console.log('events.value après assignation:', events.value.length)
      } catch (error) {
        console.error('Erreur lors du chargement:', error)
        alert('Erreur lors du chargement des données: ' + (error.message || error))
        // Initialise avec des tableaux vides en cas d'erreur
        guests.value = []
        allGuests.value = []
        events.value = []
      } finally {
        loading.value = false
      }
    }

    // Recherche
    const handleSearch = async () => {
      if (!searchQuery.value.trim()) {
        guests.value = allGuests.value
        return
      }

      try {
        const results = await searchGuests(searchQuery.value)
        // Reconstruire avec les événements
        const guestIds = results.map(g => g.id)
        guests.value = allGuests.value.filter(g => guestIds.includes(g.id))
      } catch (error) {
        console.error('Erreur lors de la recherche:', error)
      }
    }

    // Crée une invitation directement (sans modal)
    const createInvitationDirectly = async (guestId, eventId) => {
      const key = `${guestId}-${eventId}`
      creatingInvitations.value[key] = true
      
      try {
        await createInvitation(guestId, eventId)
        // Recharge les données immédiatement
        await loadData()
      } catch (error) {
        console.error('Erreur lors de la création:', error)
        alert('Erreur lors de la création de l\'invitation: ' + (error.message || error))
      } finally {
        creatingInvitations.value[key] = false
      }
    }

    // Supprime une invitation directement
    const deleteInvitationDirectly = async (invitationId, guestId, eventId) => {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cette invitation ?')) {
        return
      }

      const key = `${guestId}-${eventId}`
      deletingInvitations.value[key] = true
      
      try {
        console.log('Suppression de l\'invitation:', invitationId)
        const result = await deleteInvitation(invitationId)
        console.log('Résultat de la suppression:', result)
        // Recharge les données immédiatement
        await loadData()
      } catch (error) {
        console.error('Erreur lors de la suppression:', error)
        console.error('Détails de l\'erreur:', JSON.stringify(error, null, 2))
        alert('Erreur lors de la suppression de l\'invitation: ' + (error.message || error))
      } finally {
        deletingInvitations.value[key] = false
      }
    }

    // Met à jour le statut d'une invitation
    const updateInvitationStatusDirectly = async (invitationId, guestId, eventId, attending) => {
      const key = `${guestId}-${eventId}`
      updatingInvitations.value[key] = true
      
      try {
        console.log('Mise à jour du statut:', { invitationId, attending, type: typeof attending })
        // Récupère le person_count actuel pour le conserver
        const guest = guests.value.find(g => g.id === guestId)
        const event = guest?.events?.find(e => e.event_id === eventId)
        const personCount = event?.person_count || 1
        
        const result = await updateInvitationStatus(invitationId, attending, personCount)
        console.log('Résultat de la mise à jour:', result)
        // Recharge les données immédiatement
        await loadData()
      } catch (error) {
        console.error('Erreur lors de la mise à jour:', error)
        alert('Erreur lors de la mise à jour du statut: ' + (error.message || error))
      } finally {
        updatingInvitations.value[key] = false
      }
    }

    // Fait un cycle dans les statuts : En attente -> Oui -> Non -> Oui (pas de retour à En attente une fois répondu)
    const cycleInvitationStatus = async (invitationId, guestId, eventId, currentAttending, currentRespondedAt) => {
      let nextAttending
      // Si pas encore de réponse (responded_at est null), on passe à Oui
      if (!currentRespondedAt) {
        nextAttending = true // En attente -> Oui
      } else if (currentAttending === true) {
        nextAttending = false // Oui -> Non
      } else {
        nextAttending = true // Non -> Oui
      }
      
      await updateInvitationStatusDirectly(invitationId, guestId, eventId, nextAttending)
    }

    // Crée une invitation via le modal
    const createInvitationModal = async () => {
      modalError.value = ''
      modalSuccess.value = ''
      
      if (!selectedGuestId.value || !selectedEventId.value) {
        modalError.value = 'Veuillez sélectionner un invité et un événement'
        return
      }

      try {
        await createInvitation(selectedGuestId.value, selectedEventId.value)
        modalSuccess.value = 'Invitation créée avec succès !'
        
        // Recharge les données après 1 seconde
        setTimeout(() => {
          loadData()
          closeModal()
        }, 1000)
      } catch (error) {
        console.error('Erreur lors de la création:', error)
        modalError.value = error.message || 'Erreur lors de la création de l\'invitation'
      }
    }

    // Ouvre le modal pour créer une invitation
    const openCreateInvitationModal = (guest = null, eventId = null) => {
      selectedGuestForInvitation.value = guest
      selectedGuestId.value = guest ? guest.id : ''
      selectedEventId.value = eventId || ''
      modalError.value = ''
      modalSuccess.value = ''
      showCreateInvitationModal.value = true
    }

    // Ferme le modal
    const closeModal = () => {
      showCreateInvitationModal.value = false
      selectedGuestId.value = ''
      selectedEventId.value = ''
      selectedGuestForInvitation.value = null
      modalError.value = ''
      modalSuccess.value = ''
    }

    // Ouvre le modal pour créer un invité
    const openCreateGuestModal = () => {
      newGuest.value = { firstname: '', lastname: '', email: '' }
      createGuestError.value = ''
      createGuestSuccess.value = ''
      showCreateGuestModal.value = true
    }

    // Ferme le modal de création d'invité
    const closeCreateGuestModal = () => {
      showCreateGuestModal.value = false
      newGuest.value = { firstname: '', lastname: '', email: '' }
      createGuestError.value = ''
      createGuestSuccess.value = ''
    }

    // Crée un invité
    const createGuestModal = async () => {
      if (!newGuest.value.firstname || !newGuest.value.lastname) {
        createGuestError.value = 'Le prénom et le nom sont obligatoires'
        return
      }

      creatingGuest.value = true
      createGuestError.value = ''
      createGuestSuccess.value = ''

      try {
        await createGuest(
          newGuest.value.firstname,
          newGuest.value.lastname,
          newGuest.value.email || null
        )
        createGuestSuccess.value = 'Invité créé avec succès !'
        
        // Recharge les données après 1 seconde
        setTimeout(async () => {
          await loadData()
          closeCreateGuestModal()
        }, 1000)
      } catch (error) {
        console.error('Erreur lors de la création:', error)
        createGuestError.value = error.message || 'Erreur lors de la création de l\'invité'
      } finally {
        creatingGuest.value = false
      }
    }

    // Supprime un invité
    const deleteGuestDirectly = async (guestId) => {
      const guest = guests.value.find(g => g.id === guestId)
      const guestName = guest ? `${guest.firstname} ${guest.lastname}` : 'cet invité'
      
      if (!confirm(`Êtes-vous sûr de vouloir supprimer ${guestName} ? Cette action supprimera également toutes ses invitations.`)) {
        return
      }

      deletingGuests.value[guestId] = true
      
      try {
        await deleteGuest(guestId)
        // Recharge les données immédiatement
        await loadData()
      } catch (error) {
        console.error('Erreur lors de la suppression:', error)
        alert('Erreur lors de la suppression de l\'invité: ' + (error.message || error))
      } finally {
        deletingGuests.value[guestId] = false
      }
    }

    // Actualise les données
    const refreshData = () => {
      loadData()
    }

    // Retour à l'accueil
    const goHome = () => {
      window.location.hash = ''
      window.location.reload()
    }

    // Obtient la classe du badge selon le statut
    const getBadgeClass = (event) => {
      if (!event.has_invitation) return 'bg-secondary'
      // Si responded_at n'est pas défini, c'est "En attente"
      if (!event.responded_at) return 'bg-warning'
      // Si responded_at est défini, on regarde attending
      if (event.attending === true) return 'bg-success'
      if (event.attending === false) return 'bg-danger'
      return 'bg-warning'
    }

    // Obtient le texte du statut
    const getStatusText = (event) => {
      if (!event.has_invitation) return '-'
      // Si responded_at n'est pas défini, c'est "En attente"
      if (!event.responded_at) {
        return 'En attente'
      }
      // Si responded_at est défini, on regarde attending
      // Vérification stricte avec === pour éviter les problèmes de type
      if (event.attending === true) {
        return 'Oui'
      }
      if (event.attending === false) {
        return 'Non'
      }
      // Si responded_at est défini mais attending est null, c'est "En attente"
      return 'En attente'
    }

    // Obtient le nombre de personnes du premier événement
    const getTotalPersonCount = (guest) => {
      if (!guest.events || !Array.isArray(guest.events)) return '-'
      
      // Trouve le premier événement avec une invitation
      const firstEvent = guest.events.find(event => event.has_invitation && event.person_count)
      
      return firstEvent && firstEvent.person_count ? firstEvent.person_count : '-'
    }

    // Calcule les statistiques pour un événement donné
    const getEventStats = (eventId) => {
      if (!guests.value || !Array.isArray(guests.value)) {
        return { attending: 0, notAttending: 0, pending: 0, total: 0 }
      }

      let attending = 0
      let notAttending = 0
      let pending = 0
      let total = 0

      guests.value.forEach(guest => {
        if (!guest.events || !Array.isArray(guest.events)) return
        
        const event = guest.events.find(e => e.event_id === eventId)
        if (!event || !event.has_invitation) return

        total++
        
        if (!event.responded_at) {
          pending++
        } else if (event.attending === true) {
          attending++
        } else if (event.attending === false) {
          notAttending++
        } else {
          pending++
        }
      })

      return { attending, notAttending, pending, total }
    }

    // Démarre l'édition d'un champ
    const startEdit = async (cellKey, currentValue) => {
      editingCell.value = cellKey
      editingValues.value[cellKey] = currentValue || ''
      // Focus sur l'input après le prochain rendu
      await nextTick()
      // Trouve l'input qui vient d'être créé (celui qui est visible et dans le tableau)
      const inputs = document.querySelectorAll('table tbody input[type="text"], table tbody input[type="email"]')
      if (inputs.length > 0) {
        // Prend le dernier input créé (celui qui correspond à la cellule en cours d'édition)
        const lastInput = inputs[inputs.length - 1]
        if (lastInput && lastInput.value === (currentValue || '')) {
          lastInput.focus()
          lastInput.select()
        }
      }
    }

    // Annule l'édition
    const cancelEdit = (cellKey) => {
      editingCell.value = null
      delete editingValues.value[cellKey]
    }

    // Sauvegarde un champ d'un invité
    const saveGuestField = async (guestId, fieldName, newValue) => {
      if (editingCell.value === null) return

      const cellKey = `${fieldName}-${guestId}`
      const oldValue = editingValues.value[cellKey]
      
      // Si la valeur n'a pas changé, annule simplement l'édition
      const guest = guests.value.find(g => g.id === guestId)
      if (!guest) return

      const currentValue = guest[fieldName] || ''
      if (newValue === currentValue) {
        cancelEdit(cellKey)
        return
      }

      // Valide la nouvelle valeur
      if (fieldName === 'email' && newValue && !newValue.includes('@')) {
        alert('Veuillez entrer une adresse email valide')
        return
      }

      if ((fieldName === 'firstname' || fieldName === 'lastname') && !newValue.trim()) {
        alert(`${fieldName === 'firstname' ? 'Le prénom' : 'Le nom'} ne peut pas être vide`)
        return
      }

      savingFields.value[cellKey] = true

      try {
        await updateGuest(guestId, { [fieldName]: newValue.trim() })
        // Recharge les données pour mettre à jour l'affichage
        await loadData()
        cancelEdit(cellKey)
      } catch (error) {
        console.error('Erreur lors de la mise à jour:', error)
        alert('Erreur lors de la mise à jour: ' + (error.message || error))
        // Restaure l'ancienne valeur
        editingValues.value[cellKey] = oldValue
      } finally {
        savingFields.value[cellKey] = false
      }
    }

    // Computed properties
    const filteredGuests = computed(() => guests.value || [])

    const totalInvitations = computed(() => {
      if (!guests.value || !Array.isArray(guests.value)) return 0
      return guests.value.reduce((total, guest) => {
        if (!guest.events || !Array.isArray(guest.events)) return total
        return total + guest.events.filter(e => e.has_invitation).length
      }, 0)
    })

    const respondedCount = computed(() => {
      if (!guests.value || !Array.isArray(guests.value)) return 0
      return guests.value.reduce((total, guest) => {
        if (!guest.events || !Array.isArray(guest.events)) return total
        return total + guest.events.filter(e => e.has_invitation && e.responded_at).length
      }, 0)
    })

    const attendingCount = computed(() => {
      if (!guests.value || !Array.isArray(guests.value)) return 0
      return guests.value.reduce((total, guest) => {
        if (!guest.events || !Array.isArray(guest.events)) return total
        return total + guest.events.filter(e => e.has_invitation && e.attending === true).length
      }, 0)
    })

    onMounted(() => {
      checkAuth()
      initAdminPassword()
    })

    return {
      isAuthenticated,
      password,
      loginError,
      loading,
      guests,
      filteredGuests,
      events,
      searchQuery,
      allGuests,
      showCreateInvitationModal,
      selectedGuestId,
      selectedEventId,
      selectedGuestForInvitation,
      modalError,
      modalSuccess,
      handleLogin,
      handleLogout,
      handleSearch,
      createInvitationDirectly,
      createInvitationModal,
      openCreateInvitationModal,
      closeModal,
      openCreateGuestModal,
      closeCreateGuestModal,
      createGuestModal,
      deleteGuestDirectly,
      refreshData,
      goHome,
      getBadgeClass,
      getStatusText,
      getTotalPersonCount,
      getEventStats,
      totalInvitations,
      respondedCount,
      attendingCount,
      creatingInvitations,
      deletingInvitations,
      updatingInvitations,
      deletingGuests,
      deleteInvitationDirectly,
      updateInvitationStatusDirectly,
      cycleInvitationStatus,
      editingCell,
      editingValues,
      savingFields,
      startEdit,
      saveGuestField,
      cancelEdit,
      showCreateGuestModal,
      newGuest,
      createGuestError,
      createGuestSuccess,
      creatingGuest
    }
  }
}
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.admin-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding-top: 100px;
}

.admin-interface {
  min-height: 100vh;
}

.modal.show {
  display: block;
}

.modal-backdrop.show {
  opacity: 0.5;
}

.editable-cell {
  position: relative;
  min-height: 2em;
  padding: 0.375rem 0.75rem;
}

.editable-cell:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.editable-text {
  cursor: pointer;
  display: inline-block;
  min-height: 1.5em;
  width: 100%;
  padding: 2px 0;
}
</style>
