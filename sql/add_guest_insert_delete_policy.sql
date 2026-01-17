-- Politiques RLS pour permettre la création et la suppression d'invités
-- À exécuter dans l'éditeur SQL de Supabase

-- Politique pour permettre la création d'invités
CREATE POLICY "Allow public insert on guests" ON guests
  FOR INSERT WITH CHECK (true);

-- Politique pour permettre la suppression d'invités
CREATE POLICY "Allow public delete on guests" ON guests
  FOR DELETE USING (true);
