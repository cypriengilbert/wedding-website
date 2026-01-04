-- Script pour ajouter la politique RLS permettant la mise à jour de l'email dans la table guests
-- À exécuter dans l'éditeur SQL de Supabase si vous avez déjà créé les tables

-- Politique RLS pour permettre la mise à jour de l'email des invités
CREATE POLICY "Allow public update email on guests" ON guests
  FOR UPDATE USING (true)
  WITH CHECK (true);

-- Vérification : vous pouvez tester avec cette requête (remplacez l'ID et l'email)
-- UPDATE guests SET email = 'test@example.com' WHERE id = 'votre-id-guest';

