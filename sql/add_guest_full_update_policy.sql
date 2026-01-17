-- Politique RLS pour permettre la mise à jour complète des invités (nom, prénom, email)
-- À exécuter dans l'éditeur SQL de Supabase

-- Supprime l'ancienne politique si elle existe (optionnel, pour éviter les doublons)
DROP POLICY IF EXISTS "Allow public update email on guests" ON guests;

-- Crée une nouvelle politique pour permettre la mise à jour de tous les champs
CREATE POLICY "Allow public update on guests" ON guests
  FOR UPDATE USING (true)
  WITH CHECK (true);
