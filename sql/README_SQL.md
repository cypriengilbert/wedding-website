# Scripts SQL pour Supabase

## Insertion d'invités et invitations

### Utilisation du script `insert_guests_and_invitations.sql`

1. **Prérequis** : Assurez-vous que les événements existent dans la table `events`
   - Si les événements n'existent pas, créez-les d'abord avec les noms exacts :
     - "Cérémonie & Cocktail"
     - "Diner & Soirée"

2. **Modifier le script** :
   - Ouvrez `insert_guests_and_invitations.sql`
   - Remplacez les exemples `('Prénom1', 'Nom1', 'email1@example.com')` par vos vraies données
   - Ajoutez une ligne par invité

3. **Exécuter dans Supabase** :
   - Allez dans l'éditeur SQL de Supabase
   - Copiez-collez le script modifié
   - Exécutez-le

### Format des données

Chaque ligne doit suivre ce format :
```sql
('Prénom', 'Nom', 'email@example.com'),
```

Exemple :
```sql
INSERT INTO guests (firstname, lastname, email) VALUES
  ('Jean', 'Dupont', 'jean.dupont@example.com'),
  ('Marie', 'Martin', 'marie.martin@example.com'),
  ('Pierre', 'Durand', 'pierre.durand@example.com')
ON CONFLICT (email) DO NOTHING;
```

### Vérification

Après l'exécution, vous pouvez vérifier les données avec :

```sql
-- Voir tous les invités
SELECT * FROM guests ORDER BY lastname, firstname;

-- Voir toutes les invitations créées
SELECT 
  g.firstname || ' ' || g.lastname AS nom_complet,
  g.email,
  e.name AS evenement,
  i.attending,
  i.person_count
FROM invitations i
JOIN guests g ON i.guest_id = g.id
JOIN events e ON i.event_id = e.id
WHERE e.name IN ('Cérémonie & Cocktail', 'Diner & Soirée')
ORDER BY g.lastname, g.firstname, e.name;
```

### Notes importantes

- Le script utilise `ON CONFLICT (email) DO NOTHING` pour éviter les doublons
- Les invitations sont créées automatiquement pour tous les invités
- Par défaut, `attending` est à `false` (pas encore de réponse)
- `person_count` est initialisé à 1 par défaut

