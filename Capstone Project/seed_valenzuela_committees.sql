-- -------------------------------------------------------------
-- VALENZUELA CITY SANGGUNIAN PANLUNGSOD DATABASE SEED SCRIPT
-- -------------------------------------------------------------
-- Contains 24 real standing committees of Valenzuela City.
-- All email domains use a safe, non-existent local test domain (@valenzuela.local)
-- default password for accounts is: 'WelcomeLGU2026!'
-- -------------------------------------------------------------

-- 1. INSERT COUNCILORS (USERS Roster)
-- Safe fake email format: name@valenzuela.local
INSERT INTO users (first_name, last_name, email, password, role_id, position, is_active)
VALUES
('Katherine', 'Galang-Coseteng', 'kate.coseteng.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Niña Shiela', 'Lopez', 'ninang.lopez.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Gerald', 'Esplana', 'gerry.esplana.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Jennifer', 'Pingree-Esplana', 'jenny.esplana.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Marlon Paulo', 'Nolasco', 'mar.nolasco.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Ramon', 'Encarnacion', 'ramon.encarnacion.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Ricardo', 'Ricart', 'riki.ricart.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Aloysius Arthur', 'Herrera', 'art.herrera.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Joseph Albert', 'Templonuevo', 'jobo.templonuevo.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Christian', 'Feliciano', 'ian.feliciano.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Carlito', 'De Guzman', 'lito.deguzman.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1),
('Mickey', 'Pineda', 'mickey.pineda.test@gmail.com', '$2y$10$tM2a6oI4r2wR1h1L.QG2aO87W7xO7687J6Yy3g2W4779.6y.2.822', 3, 'Councilor', 1)
ON DUPLICATE KEY UPDATE position='Councilor';


-- 2. INSERT 24 REAL STANDING COMMITTEES OF VALENZUELA
INSERT INTO committees (committee_name, committee_type, description, jurisdiction, chairperson_id, vice_chair_id, secretary_id, is_active)
VALUES 
-- 1. Laws
(
  'Committee on Laws, Rules and Internal Reorganization', 'Standing', 
  'Reviews all draft resolutions, municipal ordinances, code revisions, and structural LGU changes.',
  'Local laws, legal reviews, rules of procedure, and organizational reform.',
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1), 1
),
-- 2. Appropriations
(
  'Committee on Appropriations, Ways and Means', 'Standing', 
  'Evaluates the budget of the city, taxation policies, and financial grants.',
  'City budget, allocations, revenue generation, and financial accountability.',
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Pingree-Esplana' LIMIT 1), 1
),
-- 3. Health
(
  'Committee on Health and Sanitation', 'Standing', 
  'Monitors hospitals, healthcare programs, and sanitation conditions in Valenzuela City.',
  'Hospitals, community health centers, waste hygiene, and medical ordinances.',
  (SELECT user_id FROM users WHERE last_name = 'Pingree-Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Pineda' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1), 1
),
-- 4. Education
(
  'Committee on Education', 'Standing', 
  'Oversight of public schools, Pamantasan ng Lungsod ng Valenzuela (PLV), and educational programs.',
  'Scholarships, school facilities, teaching quality, and student development.',
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Ricart' LIMIT 1), 1
),
-- 5. Public Works
(
  'Committee on Public Works and Infrastructure', 'Standing', 
  'Reviews plans for construction of public buildings, road improvements, and drainage.',
  'Road structures, public school buildings, parks, and city construction permits.',
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1), 1
),
-- 6. Blue Ribbon
(
  'Committee on Blue Ribbon and Government Roster', 'Standing', 
  'Investigates anomalies, malfeasance, and nonfeasance of local officers or departments.',
  'Ethics investigations, city program audits, and official complaints.',
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1), 1
),
-- 7. Social Services
(
  'Committee on Social Services and Senior Citizens', 'Standing', 
  'Handles programs for elderly support, PWD grants, orphanages, and poverty alleviation.',
  'Senior privileges, local shelters, disaster relief programs, and OSCA coordination.',
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1), 1
),
-- 8. Environmental Protection
(
  'Committee on Environmental Protection and Climate Change', 'Standing', 
  'Formulates policies on air and water quality, tree planting, and climate adaptation.',
  'Flooding strategies, recycling campaigns, industrial emissions control.',
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Pineda' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1), 1
),
-- 9. Trade, Commerce and Industry
(
  'Committee on Trade, Commerce and Industry', 'Standing', 
  'Promotes business investments, local business permits, and fair trading practices.',
  'Markets, business licensing, economic zones, and local entrepreneur support.',
  (SELECT user_id FROM users WHERE last_name = 'Ricart' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1), 1
),
-- 10. Agriculture and Fisheries
(
  'Committee on Agriculture and Fisheries', 'Standing', 
  'Oversight of urban farming, livestock health, fish pens in local waterways, and agricultural loans.',
  'Urban gardens, local crop production, fishponds, and coordination with Dept of Agriculture.',
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1), 1
),
-- 11. Transportation and Communication
(
  'Committee on Transportation and Communication', 'Standing', 
  'Reviews tricycle routes, jeepney terminals, traffic flow patterns, and public transport fares.',
  'Tricycle franchising, loading bays, city traffic signs, and local cellular cell site permits.',
  (SELECT user_id FROM users WHERE last_name = 'Pineda' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1), 1
),
-- 12. Public Safety and Order
(
  'Committee on Public Safety and Order', 'Standing', 
  'Oversight of city fire protection, police assistance, barangay tanods, and emergency response.',
  'CCTV monitoring, disaster evacuation plans, traffic regulation enforcement.',
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1), 1
),
-- 13. Human Rights and Justice
(
  'Committee on Human Rights and Justice', 'Standing', 
  'Handles matters relating to legal assistance to citizens, jail conditions, and human rights protection.',
  'Jail operations, public defender coordination, human rights awareness campaigns.',
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Ricart' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1), 1
),
-- 14. Tourism and Cultural Affairs
(
  'Committee on Tourism and Cultural Affairs', 'Standing', 
  'Promotes Valenzuela history (e.g., Pio Valenzuela museum), local festivals, and heritage sites.',
  'Local heritage landmarks, historical celebrations, tourism business accreditation.',
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1), 1
),
-- 15. Youth and Sports Development
(
  'Committee on Youth and Sports Development', 'Standing', 
  'Monitors sports complexes, local basketball leagues, and SK projects across all barangays.',
  'SK federation bylaws, sports tournaments, youth rehabilitation centers.',
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Pineda' LIMIT 1), 1
),
-- 16. Barangay Affairs
(
  'Committee on Barangay Affairs', 'Standing', 
  'Liaison between the City Council and the 33 Barangays of Valenzuela.',
  'Barangay annual budgets, dispute resolutions, and coordination with ABC president.',
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1), 1
),
-- 17. Women and Family Relations
(
  'Committee on Women, Family and Family Relations', 'Standing', 
  'Oversight of gender development, family counselling, VAWC desk operations, and child care.',
  'VAWC policies, day care centers, single parent benefits, family welfare.',
  (SELECT user_id FROM users WHERE last_name = 'Pingree-Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1), 1
),
-- 18. Urban Planning, Housing and Resettlement
(
  'Committee on Urban Planning, Housing and Resettlement', 'Standing', 
  'Reviews land conversions, housing projects for informal settlers, and relocation zones.',
  'Disiplina Village housing projects, zoning board reviews, subdivision approvals.',
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1), 1
),
-- 19. Labor, Employment and Manpower
(
  'Committee on Labor, Employment and Manpower Development', 'Standing', 
  'Oversight of local hiring policies, PESO programs, and job fair sponsorships.',
  'PESO job matching databases, local labor standards, livelihood training centers.',
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Ricart' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1), 1
),
-- 20. Cooperatives and Livelihood
(
  'Committee on Cooperatives and Livelihood', 'Standing', 
  'Supports development of credit cooperatives, transport cooperatives, and micro-financing.',
  'Cooperative registrations, small business grants, local livelihood products.',
  (SELECT user_id FROM users WHERE last_name = 'Ricart' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Lopez' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1), 1
),
-- 21. Veterans and Military Affairs
(
  'Committee on Veterans and Military Affairs', 'Standing', 
  'Liaisons with military reserves, veterans, police force, and coordinating commemorative ceremonies.',
  'Veterans benefits, military service support, and local civic-military affairs.',
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1), 1
),
-- 22. Information and Communications Technology (ICT)
(
  'Committee on Information and Communications Technology', 'Standing', 
  'Oversees Valenzuela LGU digital transformation, city wifi, and online services.',
  'Valenzuela Citizen App operations, municipal data centers, smart city initiatives.',
  (SELECT user_id FROM users WHERE last_name = 'Feliciano' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Nolasco' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Pingree-Esplana' LIMIT 1), 1
),
-- 23. Disaster Risk Reduction
(
  'Committee on Disaster Risk Reduction and Management', 'Standing', 
  'Oversight of flood emergency response, VCDRRMO funding, and rescue equipment acquisitions.',
  'Alert sirens, evacuation drills, post-typhoon relief efforts.',
  (SELECT user_id FROM users WHERE last_name = 'Templonuevo' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'De Guzman' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Esplana' LIMIT 1), 1
),
-- 24. Ethics and Privileges
(
  'Committee on Ethics and Privileges', 'Standing', 
  'Handles conflicts of interest, internal rules of conduct, and disciplinary actions of councilors.',
  'Ethics disclosures, complaints against councilors, attendance reviews.',
  (SELECT user_id FROM users WHERE last_name = 'Encarnacion' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Herrera' LIMIT 1),
  (SELECT user_id FROM users WHERE last_name = 'Galang-Coseteng' LIMIT 1), 1
) ON DUPLICATE KEY UPDATE committee_type='Standing';


-- 3. LINK CHAIRS, VICE-CHAIRS, AND SECRETARIES TO ROSTER
-- This maps the positions into the committee_members table so they display in the membership layout of each committee.
INSERT INTO committee_members (committee_id, user_id, position, join_date, is_active)
SELECT committee_id, chairperson_id, 'Chairperson', NOW(), 1 FROM committees WHERE chairperson_id IS NOT NULL
UNION ALL
SELECT committee_id, vice_chair_id, 'Vice-Chairperson', NOW(), 1 FROM committees WHERE vice_chair_id IS NOT NULL
UNION ALL
SELECT committee_id, secretary_id, 'Secretary', NOW(), 1 FROM committees WHERE secretary_id IS NOT NULL;

