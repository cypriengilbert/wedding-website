-- Politique RLS pour permettre la suppression d'invitations
-- À exécuter dans l'éditeur SQL de Supabase

CREATE POLICY "Allow public delete on invitations" ON invitations
  FOR DELETE USING (true);
