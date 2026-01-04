# Site de Mariage - Vue.js + Supabase

Application Vue.js pour la gestion des invitations de mariage, connectée à Supabase.

## 🚀 Installation

1. **Installer les dépendances**
```bash
npm install
```

2. **Configurer Supabase**

   - Créez un projet sur [Supabase](https://supabase.com)
   - Récupérez votre URL de projet et votre clé anonyme (anon key)
   - Créez un fichier `.env` à la racine du projet :
   ```env
   VITE_SUPABASE_URL=https://votre-projet.supabase.co
   VITE_SUPABASE_ANON_KEY=votre_cle_anonyme
   ```

3. **Créer les tables dans Supabase**

   Exécutez ces requêtes SQL dans l'éditeur SQL de Supabase :

   ```sql
   -- Table des invités
   CREATE TABLE guests (
     id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
     firstname TEXT NOT NULL,
     lastname TEXT NOT NULL,
     email TEXT UNIQUE NOT NULL,
     created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
   );

   -- Table des événements
   CREATE TABLE events (
     id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
     name TEXT NOT NULL,
     description TEXT,
     created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
   );

   -- Table des invitations (relation entre invités et événements)
   CREATE TABLE invitations (
     id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
     guest_id UUID REFERENCES guests(id) ON DELETE CASCADE,
     event_id UUID REFERENCES events(id) ON DELETE CASCADE,
     attending BOOLEAN DEFAULT false,
     person_count INTEGER DEFAULT 1,
     responded_at TIMESTAMP WITH TIME ZONE,
     created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
     updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
     UNIQUE(guest_id, event_id)
   );

   -- Index pour améliorer les performances
   CREATE INDEX idx_invitations_guest_id ON invitations(guest_id);
   CREATE INDEX idx_invitations_event_id ON invitations(event_id);

   -- Fonction pour mettre à jour updated_at automatiquement
   CREATE OR REPLACE FUNCTION update_updated_at_column()
   RETURNS TRIGGER AS $$
   BEGIN
     NEW.updated_at = NOW();
     RETURN NEW;
   END;
   $$ language 'plpgsql';

   -- Trigger pour updated_at
   CREATE TRIGGER update_invitations_updated_at 
     BEFORE UPDATE ON invitations 
     FOR EACH ROW 
     EXECUTE FUNCTION update_updated_at_column();

   -- Activer Row Level Security (RLS)
   ALTER TABLE guests ENABLE ROW LEVEL SECURITY;
   ALTER TABLE events ENABLE ROW LEVEL SECURITY;
   ALTER TABLE invitations ENABLE ROW LEVEL SECURITY;

   -- Politiques RLS pour permettre la lecture publique
   CREATE POLICY "Allow public read access on guests" ON guests
     FOR SELECT USING (true);

   CREATE POLICY "Allow public read access on events" ON events
     FOR SELECT USING (true);

   CREATE POLICY "Allow public read access on invitations" ON invitations
     FOR SELECT USING (true);

   -- Politiques RLS pour permettre la mise à jour des invitations
   CREATE POLICY "Allow public update on invitations" ON invitations
     FOR UPDATE USING (true);

   -- Insérer les événements par défaut
   INSERT INTO events (name, description) VALUES 
     ('Mariage Civil', '15h00 - Mairie d''Esternay, 10 Place du Général de Gaulle, 51310 Esternay'),
     ('Cérémonie', '16h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins'),
     ('Cocktail', '18h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins');
   ```

   **Si vous avez déjà créé les tables, exécutez ces migrations :**

   ```sql
   -- Ajouter le champ description à la table events (si pas déjà fait)
   ALTER TABLE events ADD COLUMN IF NOT EXISTS description TEXT;
   
   -- Ajouter le champ responded_at à la table invitations
   ALTER TABLE invitations ADD COLUMN IF NOT EXISTS responded_at TIMESTAMP WITH TIME ZONE;
   
   -- Migrer de name vers firstname et lastname (si la table guests existe avec name)
   DO $$
   BEGIN
     -- Vérifier si la colonne name existe
     IF EXISTS (
       SELECT 1 FROM information_schema.columns 
       WHERE table_name = 'guests' AND column_name = 'name'
     ) THEN
       -- Ajouter les nouvelles colonnes si elles n'existent pas
       ALTER TABLE guests ADD COLUMN IF NOT EXISTS firstname TEXT;
       ALTER TABLE guests ADD COLUMN IF NOT EXISTS lastname TEXT;
       
       -- Diviser name en firstname et lastname (prendre le premier mot comme prénom, le reste comme nom)
       UPDATE guests 
       SET 
         firstname = SPLIT_PART(name, ' ', 1),
         lastname = SUBSTRING(name FROM LENGTH(SPLIT_PART(name, ' ', 1)) + 2)
       WHERE firstname IS NULL OR lastname IS NULL;
       
       -- Supprimer l'ancienne colonne name
       ALTER TABLE guests DROP COLUMN IF EXISTS name;
       
       -- Rendre les colonnes obligatoires
       ALTER TABLE guests ALTER COLUMN firstname SET NOT NULL;
       ALTER TABLE guests ALTER COLUMN lastname SET NOT NULL;
     END IF;
   END $$;
   
   -- Mettre à jour les événements existants avec des descriptions (optionnel)
   UPDATE events SET description = '15h00 - Mairie d''Esternay, 10 Place du Général de Gaulle, 51310 Esternay' WHERE name = 'Mariage Civil' AND description IS NULL;
   UPDATE events SET description = '16h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins' WHERE name = 'Cérémonie' AND description IS NULL;
   UPDATE events SET description = '18h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins' WHERE name = 'Cocktail' AND description IS NULL;
   ```

4. **Insérer des données de test (optionnel)**

   ```sql
   -- Insérer un invité de test
   INSERT INTO guests (firstname, lastname, email) VALUES 
     ('Test', 'Invité', 'test@example.com');

   -- Récupérer l'ID de l'invité et des événements
   -- Puis créer les invitations
   INSERT INTO invitations (guest_id, event_id, attending, person_count)
   SELECT 
     (SELECT id FROM guests WHERE email = 'test@example.com'),
     id,
     false,
     1
   FROM events;
   ```

## 🏃 Développement

```bash
npm run dev
```

L'application sera accessible sur `http://localhost:3000`

## 📦 Build pour production

```bash
npm run build
```

Les fichiers de production seront générés dans le dossier `dist/`

## 📝 Structure du projet

```
mariage/
├── public/                # Assets statiques (servis à la racine)
│   └── assets/           # Images et autres fichiers statiques
│       └── pasted-20260104-105018-5b917867.png
├── src/
│   ├── components/        # Composants Vue.js
│   │   ├── HeroSection.vue
│   │   ├── InfoSection.vue
│   │   └── RSVPSection.vue
│   ├── services/          # Services pour Supabase
│   │   ├── supabase.js
│   │   └── guests.js
│   ├── assets/
│   │   └── css/
│   │       └── custom.css
│   ├── App.vue
│   └── main.js
├── index.html
├── package.json
├── vite.config.js
└── .env
```

**Note:** Les fichiers dans le dossier `public/` sont servis à la racine. Par exemple, `public/assets/image.png` sera accessible via `/assets/image.png`.

## 🔐 Sécurité Supabase

Les politiques RLS (Row Level Security) sont configurées pour :
- Permettre la lecture publique des tables (guests, events, invitations)
- Permettre la mise à jour des invitations (pour le RSVP)

Pour un environnement de production, vous devriez :
- Restreindre les politiques RLS selon vos besoins
- Utiliser des fonctions Supabase pour les opérations sensibles
- Implémenter une authentification si nécessaire

## 📋 Fonctionnalités

- ✅ Connexion par email pour trouver son invitation
- ✅ Affichage des événements auxquels l'invité est convié
- ✅ Mise à jour du statut de présence (RSVP)
- ✅ Gestion du nombre de personnes
- ✅ Session persistante via localStorage
- ✅ Interface responsive avec Bootstrap

## 🎨 Personnalisation

Les styles sont définis dans `src/assets/css/custom.css` avec des variables CSS pour faciliter la personnalisation.

## 📸 Images

Placez vos images dans le dossier `public/assets/`. L'image de l'en-tête (hero) doit être nommée `pasted-20260104-105018-5b917867.png` ou vous pouvez modifier le chemin dans `src/components/HeroSection.vue`.

Si vous avez déjà une image dans l'ancien projet PHP, copiez-la dans `public/assets/` :

```bash
cp assets/pasted-20260104-105018-5b917867.png public/assets/
```

