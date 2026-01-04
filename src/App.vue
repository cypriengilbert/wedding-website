<template>
  <div>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
      <div class="container">
        <a class="navbar-brand" href="#hero">M&C</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="#hero">Accueil</a></li>
            <li class="nav-item"><a class="nav-link" href="#info">Le programme</a></li>
            <li class="nav-item"><a class="nav-link" href="#rsvp">Confirmer ma présence</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <HeroSection />

    <!-- Main Content -->
    <main>
      <!-- Info Section -->
      <InfoSection />

      <!-- RSVP Section -->
      <RSVPSection 
        :guest="guest"
        :events="events"
        :error-message="errorMessage"
        :success-message="successMessage"
        @search="handleSearch"
        @logout="handleLogout"
        @update-rsvp="handleUpdateRSVP"
      />
    </main>

    <!-- Footer -->
    <footer>
      <div class="container">
        <p>Fait avec amour par Mélanie &amp; Cyprien</p>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import HeroSection from './components/HeroSection.vue'
import InfoSection from './components/InfoSection.vue'
import RSVPSection from './components/RSVPSection.vue'
import { findGuestByEmail, findGuestByName, getGuestInvitations, updateRSVP } from './services/guests'

export default {
  name: 'App',
  components: {
    HeroSection,
    InfoSection,
    RSVPSection
  },
  setup() {
    const guest = ref(null)
    const events = ref([])
    const errorMessage = ref('')
    const successMessage = ref('')

    // Vérifie si un invité est déjà en session (localStorage)
    const checkSession = () => {
      const guestId = localStorage.getItem('guest_id')
      if (guestId) {
        loadGuestData(guestId)
      }
    }

    // Charge les données de l'invité
    const loadGuestData = async (guestId) => {
      try {
        const guestData = await findGuestByEmail(guestId) // On utilise l'email stocké comme ID temporaire
        if (guestData) {
          guest.value = guestData
          const invitations = await getGuestInvitations(guestData.id)
          events.value = invitations
        } else {
          localStorage.removeItem('guest_id')
        }
      } catch (error) {
        console.error('Erreur lors du chargement des données:', error)
        localStorage.removeItem('guest_id')
      }
    }

    // Gère la recherche (par email ou par nom)
    const handleSearch = async (mode, value1, value2) => {
      errorMessage.value = ''
      successMessage.value = ''
      
      try {
        let guestData = null
        
        if (mode === 'email') {
          guestData = await findGuestByEmail(value1)
          if (!guestData) {
            errorMessage.value = "Désolé, votre email n'a pas été trouvé. Veuillez vérifier l'adresse ou nous contacter."
            return
          }
        } else if (mode === 'name') {
          // value1 = firstName, value2 = lastName
          guestData = await findGuestByName(value1, value2)
          if (!guestData) {
            errorMessage.value = "Désolé, votre nom n'a pas été trouvé. Veuillez vérifier l'orthographe ou nous contacter."
            return
          }
        }
        
        if (guestData) {
          guest.value = guestData
          // Stocke l'identifiant pour la session (email si recherche par email, sinon email de l'invité trouvé)
          localStorage.setItem('guest_id', guestData.email)
          const invitations = await getGuestInvitations(guestData.id)
          events.value = invitations
        }
      } catch (error) {
        console.error('Erreur lors de la recherche:', error)
        errorMessage.value = "Une erreur s'est produite. Veuillez réessayer."
      }
    }

    // Gère la déconnexion
    const handleLogout = () => {
      guest.value = null
      events.value = []
      localStorage.removeItem('guest_id')
      errorMessage.value = ''
      successMessage.value = ''
      window.location.hash = '#rsvp'
    }

    // Gère la mise à jour du RSVP
    const handleUpdateRSVP = async (eventsData, personCount) => {
      errorMessage.value = ''
      successMessage.value = ''
      
      try {
        await updateRSVP(guest.value.id, eventsData, personCount)
        successMessage.value = "Merci ! Votre réponse a été enregistrée."
        
        // Recharge les données pour afficher les mises à jour
        const invitations = await getGuestInvitations(guest.value.id)
        events.value = invitations
      } catch (error) {
        console.error('Erreur lors de la mise à jour:', error)
        errorMessage.value = "Une erreur s'est produite lors de l'enregistrement. Veuillez réessayer."
      }
    }

    onMounted(() => {
      checkSession()
    })

    return {
      guest,
      events,
      errorMessage,
      successMessage,
      handleSearch,
      handleLogout,
      handleUpdateRSVP
    }
  }
}
</script>

