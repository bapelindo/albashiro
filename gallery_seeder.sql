-- 1. Insert 5 Categories
INSERT INTO gallery_categories (name) VALUES 
('Fasilitas Klinik'),
('Kegiatan Terapi'),
('Pelatihan & Seminar'),
('Testimoni Pasien'),
('Liputan Media');

-- 2. Insert Sample Images for EACH Category (Ensuring all are used)
-- Using subqueries to get dynamic IDs based on names
INSERT INTO galleries (category_id, image_url) VALUES 
-- Fasilitas Klinik
((SELECT id FROM gallery_categories WHERE name='Fasilitas Klinik' LIMIT 1), 'dummy_fasilitas_1.jpg'),
((SELECT id FROM gallery_categories WHERE name='Fasilitas Klinik' LIMIT 1), 'dummy_fasilitas_2.jpg'),

-- Kegiatan Terapi
((SELECT id FROM gallery_categories WHERE name='Kegiatan Terapi' LIMIT 1), 'dummy_terapi_1.jpg'),
((SELECT id FROM gallery_categories WHERE name='Kegiatan Terapi' LIMIT 1), 'dummy_terapi_2.jpg'),

-- Pelatihan & Seminar
((SELECT id FROM gallery_categories WHERE name='Pelatihan & Seminar' LIMIT 1), 'dummy_seminar_1.jpg'),
((SELECT id FROM gallery_categories WHERE name='Pelatihan & Seminar' LIMIT 1), 'dummy_seminar_2.jpg'),

-- Testimoni Pasien
((SELECT id FROM gallery_categories WHERE name='Testimoni Pasien' LIMIT 1), 'dummy_testi_1.jpg'),

-- Liputan Media
((SELECT id FROM gallery_categories WHERE name='Liputan Media' LIMIT 1), 'dummy_media_1.jpg');
