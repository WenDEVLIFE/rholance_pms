-- This script seeds all the requested accounts
-- Default Password: 123456
-- Hash used: $2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS

INSERT INTO users (name, email, password, role, branch_id, is_verified, status)
VALUES
-- Branch 1 Welders
('Dondon Cruz', 'dondon.cruz@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),
('Ryan Vulin', 'ryan.vulin@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),
('Jovie Santos', 'jovie.santos@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),
('Robert Mangubat', 'robert.mangubat@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),
('Ryan Delfin', 'ryan.delfin@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),
('Marlon Sica', 'marlon.sica@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 1, 1, 'active'),

-- Branch 1 Staff
('Nicole Sanchez', 'nicole.sanchez@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 1, 1, 'active'),
('Lhoraine Leonado', 'lhoraine.leonado@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 1, 1, 'active'),

-- Branch 2 Welders
('Raymond Reves', 'raymond.reves@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Manly Maascardo', 'manly.maascardo@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Jeffrey Billon', 'jeffrey.billon@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Allan Berting', 'allan.berting@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Berto Gimbla', 'berto.gimbla@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Paul Terez', 'paul.terez@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Mundo Lins', 'mundo.lins@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Itlog Treces', 'itlog.treces@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Jared Makabinta', 'jared.makabinta@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Mac Allunos', 'mac.allunos@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Mekaniko Seta', 'mekaniko.seta@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Derick Vulla', 'derick.vulla@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Bilog Villaluz', 'bilog.villaluz@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Tiki Mani', 'tiki.mani@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),
('Chris Malinao', 'chris.malinao@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'welder', 2, 1, 'active'),

-- Branch 2 Staff
('Lea Auther', 'lea.auther@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 2, 1, 'active'),
('Ella Sivla', 'ella.sivla@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 2, 1, 'active'),
('Edrelyn Rufuela', 'edrelyn.rufuela@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 2, 1, 'active'),
('Nicole Thunder', 'nicole.thunder@rholance.com', '$2a$12$P1PjEBBEkSLMaIppOlGT3eFqLFoyUCrMXflpJgUlJAhY.tLK6mHTS', 'staff', 2, 1, 'active')

ON DUPLICATE KEY UPDATE 
    password = VALUES(password),
    role = VALUES(role),
    branch_id = VALUES(branch_id),
    is_verified = 1,
    status = 'active';
