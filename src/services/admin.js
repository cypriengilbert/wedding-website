import { supabase } from './supabase'

/**
 * Récupère tous les invités avec leurs invitations
 */
export async function getAllGuestsWithInvitations() {
  const { data: guests, error: guestsError } = await supabase
    .from('guests')
    .select('*')
    .order('lastname', { nullsFirst: false })
    .order('firstname', { nullsFirst: false })

  if (guestsError) {
    console.error('Erreur lors de la récupération des invités:', guestsError)
    throw guestsError
  }

  console.log('Invités récupérés:', guests)

  // Récupère tous les événements
  const { data: events, error: eventsError } = await supabase
    .from('events')
    .select('*')
    .order('id')

  if (eventsError) {
    console.error('Erreur lors de la récupération des événements:', eventsError)
    throw eventsError
  }

  console.log('Événements récupérés:', events)

  // Récupère toutes les invitations
  const { data: invitations, error: invitationsError } = await supabase
    .from('invitations')
    .select(`
      id,
      guest_id,
      event_id,
      attending,
      person_count,
      responded_at
    `)

  if (invitationsError) {
    console.error('Erreur lors de la récupération des invitations:', invitationsError)
    throw invitationsError
  }

  console.log('Invitations récupérées:', invitations)

  // S'assure que les données sont des tableaux
  const guestsList = Array.isArray(guests) ? guests : (guests ? [guests] : [])
  const eventsList = Array.isArray(events) ? events : (events ? [events] : [])
  const invitationsList = Array.isArray(invitations) ? invitations : (invitations ? [invitations] : [])

  // Combine les données
  const guestsWithInvitations = guestsList.map(guest => {
    const guestInvitations = invitationsList.filter(inv => inv.guest_id === guest.id)
    
    const eventsStatus = eventsList.map(event => {
      const invitation = guestInvitations.find(inv => inv.event_id === event.id)
      const eventData = {
        event_id: event.id,
        event_name: event.name,
        has_invitation: !!invitation,
        invitation_id: invitation?.id || null,
        attending: invitation?.attending ?? null, // Utilise ?? pour préserver false
        person_count: invitation?.person_count || null,
        responded_at: invitation?.responded_at || null
      }
      // Log pour déboguer
      if (invitation && invitation.responded_at) {
        console.log('Invitation avec réponse:', {
          event: event.name,
          attending: invitation.attending,
          attendingType: typeof invitation.attending,
          responded_at: invitation.responded_at,
          eventData
        })
      }
      return eventData
    })

    return {
      ...guest,
      events: eventsStatus
    }
  })

  return {
    guests: guestsWithInvitations || [],
    events: eventsList || []
  }
}

/**
 * Crée une invitation pour un invité et un événement
 */
export async function createInvitation(guestId, eventId) {
  // Vérifie si l'invitation existe déjà
  const { data: existing } = await supabase
    .from('invitations')
    .select('id')
    .eq('guest_id', guestId)
    .eq('event_id', eventId)
    .maybeSingle()

  if (existing) {
    throw new Error('Cette invitation existe déjà')
  }

  const { data, error } = await supabase
    .from('invitations')
    .insert({
      guest_id: guestId,
      event_id: eventId,
      attending: null,
      person_count: 1
    })
    .select()
    .single()

  if (error) {
    throw error
  }

  return data
}

/**
 * Supprime une invitation
 */
export async function deleteInvitation(invitationId) {
  console.log('Tentative de suppression de l\'invitation:', invitationId)
  
  const { data, error } = await supabase
    .from('invitations')
    .delete()
    .eq('id', invitationId)
    .select()

  if (error) {
    console.error('Erreur Supabase lors de la suppression:', error)
    throw error
  }

  console.log('Invitation supprimée avec succès:', data)
  return { success: true, data }
}

/**
 * Recherche des invités
 */
export async function searchGuests(query) {
  const searchTerm = `%${query}%`
  
  const { data, error } = await supabase
    .from('guests')
    .select('*')
    .or(`firstname.ilike.${searchTerm},lastname.ilike.${searchTerm},email.ilike.${searchTerm}`)
    .order('lastname')
    .order('firstname')

  if (error) {
    throw error
  }

  return data
}

/**
 * Met à jour les informations d'un invité
 */
export async function updateGuest(guestId, updates) {
  const { data, error } = await supabase
    .from('guests')
    .update(updates)
    .eq('id', guestId)
    .select()
    .single()

  if (error) {
    throw error
  }

  return data
}

/**
 * Crée un nouvel invité
 */
export async function createGuest(firstname, lastname, email) {
  const { data, error } = await supabase
    .from('guests')
    .insert({
      firstname: firstname.trim(),
      lastname: lastname.trim(),
      email: email ? email.trim() : null
    })
    .select()
    .single()

  if (error) {
    throw error
  }

  return data
}

/**
 * Supprime un invité
 */
export async function deleteGuest(guestId) {
  const { error } = await supabase
    .from('guests')
    .delete()
    .eq('id', guestId)

  if (error) {
    throw error
  }

  return { success: true }
}

/**
 * Met à jour le statut d'une invitation
 */
export async function updateInvitationStatus(invitationId, attending, personCount = null) {
  // S'assure que attending est un booléen strict (true ou false, pas null)
  const attendingValue = attending === true ? true : (attending === false ? false : null)
  
  const updateData = {
    attending: attendingValue,
    responded_at: new Date().toISOString()
  }
  
  if (personCount !== null) {
    updateData.person_count = personCount
  }

  console.log('Mise à jour invitation:', { invitationId, attending, attendingValue, updateData })

  const { data, error } = await supabase
    .from('invitations')
    .update(updateData)
    .eq('id', invitationId)
    .select()
    .single()

  if (error) {
    console.error('Erreur Supabase:', error)
    throw error
  }

  console.log('Invitation mise à jour avec succès:', data)
  return data
}
