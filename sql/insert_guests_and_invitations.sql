-- Script SQL pour insérer des invités et créer leurs invitations
-- pour le Cocktail et le Diner & Soirée

-- IMPORTANT: Assurez-vous que les événements existent dans la table events
-- Si ce n'est pas le cas, exécutez d'abord:
-- INSERT INTO events (name, description) VALUES 
--   ('Cérémonie & Cocktail', '16h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins'),
--   ('Diner & Soirée', '20h00 - Domaine Le Saint Léonard, 6 Rue de l''Église, 77151 Montceaux-lès-Provins');

-- Étape 1: Insérer les invités
-- Remplacez les valeurs ci-dessous par vos données réelles
INSERT INTO guests (name, firstname, lastname, email) VALUES
('Cléophée Schuester','Cléophée','Schuester',NULL),
('Candice Fatome','Candice','Fatome',NULL),
('Neela Fatome','Neela','Fatome',NULL),
('Malo Fatome','Malo','Fatome',NULL),
('Avril Gilbert','Avril','Gilbert',NULL),
('Solal Gilbert','Solal','Gilbert',NULL),
('Luis Gilbert','Luis','Gilbert',NULL),
('Taia Gilbert','Taia','Gilbert',NULL),
('Zélie Gilbert','Zélie','Gilbert',NULL),
('Théo Gilbert','Théo','Gilbert',NULL),
('Loï Gilbert','Loï','Gilbert',NULL),
('Oscar Dreier','Oscar','Dreier',NULL),
('Alix Dreier','Alix','Dreier',NULL),
('Lilou Dreier','Lilou','Dreier',NULL),
('Agathe Barre','Agathe','Barre',NULL),
('Blanche ','Blanche','',NULL),
('Léa Ribet','Léa','Ribet',NULL),
('Camille Moeniclay','Camille','Moeniclay',NULL),
('Nicolas Sanchez','Nicolas','Sanchez',NULL),
('Eva Lannois','Eva','Lannois',NULL),
('Alexis Daubenfield','Alexis','Daubenfield',NULL),
('Alexandra Tibar','Alexandra','Tibar',NULL),
('Léo Lagros','Léo','Lagros',NULL),
('Cécile Gruson','Cécile','Gruson',NULL),
('Nadir Halitim','Nadir','Halitim',NULL),
('Julie Seeleuhtner','Julie','Seeleuhtner',NULL),
('Audrey Chabod','Audrey','Chabod',NULL),
('Xavier Notat','Xavier','Notat',NULL),
('Lyes Herbi','Lyes','Herbi',NULL),
('Laurent Batier','Laurent','Batier',NULL),
('Vincent Robert','Vincent','Robert',NULL),
('Christophe Truchon','Christophe','Truchon',NULL),
('Camille Rouchaud','Camille','Rouchaud',NULL),
('Jessica Malbet','Jessica','Malbet',NULL),
('Anais Malbet','Anais','Malbet',NULL),
('Ulysse François ','Ulysse','François ',NULL),
('Diane Thebault ','Diane','Thebault ',NULL),
('Véronique ','Véronique','',NULL),
('Annick ','Annick','',NULL),
('Colette Gilbert','Colette','Gilbert',NULL),
('Dominique Gilbert','Dominique','Gilbert',NULL),
('Joëlle Boibineuf','Joëlle','Boibineuf',NULL),
('Serge Seiler','Serge','Seiler',NULL),
('Cécile Seiler','Cécile','Seiler',NULL),
('Brigitte Seiler','Brigitte','Seiler',NULL),
('Patrick Seiler','Patrick','Seiler',NULL),
('Christine Boulay','Christine','Boulay',NULL),
('Véronique Estorges','Véronique','Estorges',NULL),
('Brigitte Lavorel','Brigitte','Lavorel',NULL),
('Roselyne Lefèvre','Roselyne','Lefèvre',NULL),
('Claude Lefèvre','Claude','Lefèvre',NULL),
('Florence Lange','Florence','Lange',NULL),
('Rayner Lange','Rayner','Lange',NULL),
('Marc  Seiler','Marc ','Seiler',NULL),
('Lorenzo Seiler','Lorenzo','Seiler',NULL),
('Germain Seiler','Germain','Seiler',NULL),
('Ninon Seiler','Ninon','Seiler',NULL),
('Martin Seiler','Martin','Seiler',NULL),
('Alexandre Seiler','Alexandre','Seiler',NULL),
('Christophe Boibineuf','Christophe','Boibineuf',NULL),
('Meuf christophe','Meuf','christophe',NULL),
('Etienne Lavorel','Etienne','Lavorel',NULL),
('Charles Seiler','Charles','Seiler',NULL),
('Marie Lefèvre','Marie','Lefèvre',NULL),
('Thibaut Lefèvre','Thibaut','Lefèvre',NULL),
('Marc Lavorel','Marc','Lavorel',NULL),
('Sabrina Lavorel','Sabrina','Lavorel',NULL),
('Christophe Lavorel','Christophe','Lavorel',NULL),
('Stéphanie Lavorel','Stéphanie','Lavorel',NULL),
('William Almo','William','Almo',NULL),
('Laurent Roset','Laurent','Roset',NULL),
('jaiuntrou Roset','jaiuntrou','Roset',NULL),
('Aela Gilbert','Aela','Gilbert',NULL),
('Bruno  Burger','Bruno ','Burger',NULL),
('Jérome Lange','Jérome','Lange',NULL),
('Femme Jérome Lange','Femme Jérome','Lange',NULL),
('Cédric Lange','Cédric','Lange',NULL),
('Femme Cédric Lange','Femme Cédric','Lange',NULL),
('Evangéline Estorges','Evangéline','Estorges',NULL),
('Femme Raphaël Estorges','Femme Raphaël','Estorges',NULL),
('Bouty Ballan','Bouty','Ballan',NULL),
('Paul  Vamour','Paul ','Vamour',NULL),
('Clémentine  Lefèvre Flahaut','Clémentine ','Lefèvre Flahaut',NULL),
('Alain Lefèvre Flahaut','Alain','Lefèvre Flahaut',NULL),
('Catherine Barthe','Catherine','Barthe',NULL),
('Christian Barthe','Christian','Barthe',NULL),
('Arlette Hurtaut','Arlette','Hurtaut',NULL),
('Thierry Hurtaut','Thierry','Hurtaut',NULL),
('Béatrice  Hamet','Béatrice ','Hamet',NULL),
('Bruno Hamet','Bruno','Hamet',NULL),
('Christine Lourdin','Christine','Lourdin',NULL),
('J'aiun trou 2 Lourdin','J'aiun trou 2','Lourdin',NULL),
('Andrée Lepage','Andrée','Lepage',NULL)
  -- Ajoutez autant de lignes que nécessaire
ON CONFLICT (email) DO NOTHING; -- Évite les doublons si l'email existe déjà

-- Étape 2: Créer les invitations pour le Cocktail et le Diner & Soirée
-- Cette requête crée automatiquement les invitations pour tous les invités
-- pour les événements "Cérémonie & Cocktail" et "Diner & Soirée"

INSERT INTO invitations (guest_id, event_id, attending, person_count)
SELECT 
  g.id AS guest_id,
  e.id AS event_id,
  false AS attending, -- Par défaut, pas encore de réponse
  1 AS person_count
FROM guests g
CROSS JOIN events e
WHERE e.name IN ('Cocktail', 'Diner & Soirée')
  AND NOT EXISTS (
    -- Évite de créer des invitations en double
    SELECT 1 FROM invitations i 
    WHERE i.guest_id = g.id AND i.event_id = e.id
  );

-- Vérification: Afficher les invitations créées
-- SELECT 
--   g.firstname || ' ' || g.lastname AS nom_complet,
--   g.email,
--   e.name AS evenement,
--   i.attending,
--   i.person_count
-- FROM invitations i
-- JOIN guests g ON i.guest_id = g.id
-- JOIN events e ON i.event_id = e.id
-- WHERE e.name IN ('Cérémonie & Cocktail', 'Diner & Soirée')
-- ORDER BY g.lastname, g.firstname, e.name;

