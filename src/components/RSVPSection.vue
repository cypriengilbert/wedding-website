<template>
  <section id="rsvp" class="section">
    <div class="container">
      <h2 class="section-title">Confirmation de votre présence</h2>
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
          <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

          <div v-if="!guest">
            <!-- Search Form -->
            <p class="text-center lead mb-4">Veuillez confirmer votre présence avant le 15 avril 2026.</p>
            
            <!-- Tabs pour choisir le mode de recherche -->
            <ul class="nav nav-tabs mb-3 justify-content-center" role="tablist">
              <li class="nav-item" role="presentation">
                <button 
                  class="nav-link" 
                  :class="{ active: searchMode === 'email' }"
                  @click="searchMode = 'email'"
                  type="button"
                >
                  Par email
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button 
                  class="nav-link" 
                  :class="{ active: searchMode === 'name' }"
                  @click="searchMode = 'name'"
                  type="button"
                >
                  Par nom et prénom
                </button>
              </li>
            </ul>

            <form @submit.prevent="handleSearchSubmit">
              <div v-if="searchMode === 'email'" class="mb-3">
                <label for="email" class="form-label visually-hidden">Email</label>
                <input 
                  type="email" 
                  class="form-control form-control-lg" 
                  id="email" 
                  v-model="searchValue" 
                  placeholder="Entrez votre adresse email" 
                  
                >
              </div>
              <template v-else>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="firstname" class="form-label">Prénom</label>
                    <input 
                      type="text" 
                      class="form-control form-control-lg" 
                      id="firstname" 
                      v-model="firstName" 
                      placeholder="Votre prénom" 
                      required
                    >
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="lastname" class="form-label">Nom</label>
                    <input 
                      type="text" 
                      class="form-control form-control-lg" 
                      id="lastname" 
                      v-model="lastName" 
                      placeholder="Votre nom" 
                      required
                    >
                  </div>
                </div>
              </template>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Trouver mon invitation</button>
              </div>
            </form>
          </div>

          <div v-else>
            <!-- RSVP Form -->
            <div class="text-center">
              <span>Bonjour, {{ getFullName(guest) }} !</span>
              <p>Nous sommes ravis de vous inviter aux événements suivants. Merci de nous donner votre réponse pour chacun.</p>
              <p><a href="#" @click.prevent="handleLogout">Ce n'est pas vous ?</a></p>
            </div>

            <form @submit.prevent="handleRSVPSubmit">
              <!-- Champ email si l'invité n'a pas d'email -->
              <div v-if="!guest.email || guest.email === ''" class="mb-3 p-3 border rounded bg-light">
                <label for="guest_email" class="form-label">Votre adresse email </label>
                <input 
                  type="email" 
                  class="form-control" 
                  id="guest_email" 
                  v-model="guestEmail" 
                  placeholder="votre.email@example.com" 
                  required
                >
                <small class="form-text text-muted">Nous avons besoin de votre email pour vous contacter si nécessaire.</small>
              </div>

              <div v-for="event in events" :key="event.id" class="mb-3 p-3 border rounded" :class="{ 'border-success': event.has_responded }">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h5 class="mb-0">{{ event.name }}</h5>
                  <span v-if="event.has_responded" class="badge bg-success">Répondu</span>
                </div>
                <p v-if="event.description" class="text-muted mb-3">{{ event.description }}</p>
                <div class="form-check form-check-inline">
                  <input 
                    class="form-check-input" 
                    type="radio" 
                    :name="`event-${event.id}`" 
                    :id="`event-yes-${event.id}`" 
                    :value="'yes'" 
                    v-model="rsvpData.events[event.id]"
                    required
                  >
                  <label class="form-check-label" :for="`event-yes-${event.id}`">Oui, je serai présent(e)</label>
                </div>
                <div class="form-check form-check-inline">
                  <input 
                    class="form-check-input" 
                    type="radio" 
                    :name="`event-${event.id}`" 
                    :id="`event-no-${event.id}`" 
                    :value="'no'" 
                    v-model="rsvpData.events[event.id]"
                    required
                  >
                  <label class="form-check-label" :for="`event-no-${event.id}`">Non, je ne pourrai pas venir</label>
                </div>
              </div>

              <div class="mb-3">
                <label for="person_count" class="form-label">Nombre de personnes au total (vous inclus)</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="person_count" 
                  v-model.number="rsvpData.person_count" 
                  min="1" 
                  max="10"
                >
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Valider ma réponse</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, watch, onMounted } from 'vue'

export default {
  name: 'RSVPSection',
  props: {
    guest: {
      type: Object,
      default: null
    },
    events: {
      type: Array,
      default: () => []
    },
    errorMessage: {
      type: String,
      default: ''
    },
    successMessage: {
      type: String,
      default: ''
    }
  },
  emits: ['login', 'logout', 'update-rsvp', 'search', 'update-email'],
  setup(props, { emit }) {
    const searchMode = ref('email') // 'email' ou 'name'
    const searchValue = ref('')
    const firstName = ref('')
    const lastName = ref('')
    const guestEmail = ref('')
    const rsvpData = ref({
      events: {},
      person_count: 1
    })

    // Initialise les données RSVP avec les valeurs existantes
    const initializeRSVP = () => {
      if (props.events && props.events.length > 0) {
        const eventsObj = {}
        props.events.forEach(event => {
          eventsObj[event.id] = event.attending ? 'yes' : 'no'
        })
        rsvpData.value.events = eventsObj
        rsvpData.value.person_count = props.events[0]?.person_count || 1
      }
    }

    // Surveille les changements d'événements pour réinitialiser le formulaire
    watch(() => props.events, () => {
      initializeRSVP()
    }, { immediate: true })

    // Initialise l'email du guest s'il n'en a pas
    watch(() => props.guest, (newGuest) => {
      if (newGuest && (!newGuest.email || newGuest.email === '')) {
        guestEmail.value = ''
      } else if (newGuest && newGuest.email) {
        guestEmail.value = newGuest.email
      }
    }, { immediate: true })

    const handleSearchSubmit = () => {
      if (searchMode.value === 'email') {
        emit('search', searchMode.value, searchValue.value)
        searchValue.value = ''
      } else {
        emit('search', searchMode.value, firstName.value, lastName.value)
        firstName.value = ''
        lastName.value = ''
      }
    }

    const handleLogout = () => {
      emit('logout')
    }

    const handleRSVPSubmit = () => {
      // Si l'invité n'a pas d'email et qu'un email a été saisi, on le passe avec le RSVP
      const emailToUpdate = ((!props.guest.email || props.guest.email === '') && guestEmail.value && guestEmail.value.trim()) 
        ? guestEmail.value.trim() 
        : null
      
      emit('update-rsvp', rsvpData.value.events, rsvpData.value.person_count, emailToUpdate)
    }

    // Fonction pour obtenir le nom complet
    const getFullName = (guest) => {
      if (!guest) return ''
      if (guest.firstname && guest.lastname) {
        return `${guest.firstname} ${guest.lastname}`
      }
      // Fallback pour compatibilité avec l'ancien format
      return guest.name || ''
    }

    onMounted(() => {
      initializeRSVP()
    })

    return {
      searchMode,
      searchValue,
      firstName,
      lastName,
      guestEmail,
      rsvpData,
      handleSearchSubmit,
      handleLogout,
      handleRSVPSubmit,
      getFullName
    }
  }
}
</script>

