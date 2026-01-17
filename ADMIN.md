# Page d'Administration

## Accès

Pour accéder à la page d'administration, ajoutez `#admin` à l'URL :
```
http://localhost:3000/#admin
```

## Authentification

- **Mot de passe par défaut** : `admin`
- Le mot de passe est défini dans `src/config/admin.js`
- Le mot de passe est stocké dans le localStorage du navigateur
- La session est également stockée dans le localStorage

### Changer le mot de passe

**Méthode 1 : Modifier le fichier de configuration (recommandé)**

Éditez le fichier `src/config/admin.js` et modifiez la valeur de `defaultPassword` :

```javascript
export const adminConfig = {
  defaultPassword: 'MelEtCyp',
  // ...
}
```

**Méthode 2 : Via la console du navigateur**

Ouvrez la console du navigateur et exécutez :

```javascript
localStorage.setItem('admin_password_hash', 'votre_nouveau_mot_de_passe')
```

Puis reconnectez-vous avec le nouveau mot de passe.

## Fonctionnalités

### 1. Vue d'ensemble
- Liste de tous les invités avec leurs statuts d'invitation
- Statistiques : nombre d'invités, invitations, réponses, présences
- Recherche par nom, prénom ou email

### 2. Gestion des invitations
- **Créer une invitation** : Cliquez sur le bouton "+" à côté d'un invité ou sur "Créer une invitation"
- Sélectionnez l'invité et l'événement
- L'invitation est créée avec `attending = false` par défaut

### 3. Statuts des invitations
- **Badge vert** : L'invité a répondu "Oui"
- **Badge rouge** : L'invité a répondu "Non"
- **Badge jaune** : Invitation créée mais pas encore de réponse
- **Tiret (-)** : Pas d'invitation pour cet événement

## Permissions Supabase requises

Assurez-vous d'avoir exécuté ces politiques RLS dans Supabase :

```sql
-- Permettre la création d'invitations
CREATE POLICY "Allow public insert on invitations" ON invitations
  FOR INSERT WITH CHECK (true);

-- Permettre la mise à jour de l'email des invités
CREATE POLICY "Allow public update email on guests" ON guests
  FOR UPDATE USING (true)
  WITH CHECK (true);
```

## Sécurité

⚠️ **Note importante** : Cette page d'administration utilise une authentification basique avec mot de passe stocké en local. Pour un environnement de production, vous devriez :

1. Utiliser une authentification Supabase plus sécurisée
2. Implémenter des rôles utilisateurs
3. Restreindre les politiques RLS selon les rôles
4. Utiliser des variables d'environnement pour le mot de passe
