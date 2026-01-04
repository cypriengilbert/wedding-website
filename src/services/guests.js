import { supabase } from './supabase'

/**
 * Trouve un invité par son email
 */
export async function findGuestByEmail(email) {
  const { data, error } = await supabase
    .from('guests')
    .select('*')
    .eq('email', email)
    .single()

  if (error && error.code !== 'PGRST116') {
    throw error
  }

  return data
}

/**
 * Trouve un invité par son prénom et nom
 */
export async function findGuestByName(firstName, lastName) {
  let query = supabase
    .from('guests')
    .select('*')

  // Recherche dans firstname et lastname
  if (firstName && firstName.trim()) {
    query = query.ilike('firstname', `%${firstName.trim()}%`)
  }
  
  if (lastName && lastName.trim()) {
    query = query.ilike('lastname', `%${lastName.trim()}%`)
  }

  const { data, error } = await query

  if (error) {
    throw error
  }

  // Si plusieurs résultats, retourne le premier
  // Vous pourriez aussi retourner tous les résultats et laisser l'utilisateur choisir
  return data && data.length > 0 ? data[0] : null
}

/**
 * Récupère les invitations d'un invité avec les événements associés
 */
export async function getGuestInvitations(guestId) {
  const { data, error } = await supabase
    .from('invitations')
    .select(`
      id,
      attending,
      person_count,
      responded_at,
      events (
        id,
        name,
        description
      )
    `)
    .eq('guest_id', guestId)

  if (error) {
    throw error
  }

  if (!data || data.length === 0) {
    return []
  }

  // Transforme les données pour correspondre au format attendu
  return data.map(inv => ({
    id: inv.events?.id,
    name: inv.events?.name,
    description: inv.events?.description || '',
    attending: inv.attending || false,
    person_count: inv.person_count || 1,
    responded_at: inv.responded_at || null,
    has_responded: !!inv.responded_at,
    invitation_id: inv.id
  })).filter(inv => inv.id) // Filtre les invitations sans événement
}

/**
 * Met à jour les réponses RSVP d'un invité
 */
export async function updateRSVP(guestId, events, personCount) {
  const updates = []

  for (const [eventId, status] of Object.entries(events)) {
    const attending = status === 'yes' ? true : false
    
    // Trouve l'invitation correspondante
    const { data: invitation, error: findError } = await supabase
      .from('invitations')
      .select('id')
      .eq('guest_id', guestId)
      .eq('event_id', eventId)
      .maybeSingle()

    if (findError && findError.code !== 'PGRST116') {
      throw findError
    }

    if (invitation) {
      const update = {
        id: invitation.id,
        attending,
        person_count: attending ? personCount : null,
        responded_at: new Date().toISOString() // Marque la réponse avec la date actuelle
      }
      updates.push(update)
    }
  }

  // Met à jour toutes les invitations
  for (const update of updates) {
    const { id, ...updateData } = update
    const { error } = await supabase
      .from('invitations')
      .update(updateData)
      .eq('id', id)

    if (error) {
      throw error
    }
  }

  return { success: true }
}

/**
 * Met à jour l'email d'un invité
 */
export async function updateGuestEmail(guestId, email) {
  const { error } = await supabase
    .from('guests')
    .update({ email })
    .eq('id', guestId)

  if (error) {
    throw error
  }

  return { success: true }
}

