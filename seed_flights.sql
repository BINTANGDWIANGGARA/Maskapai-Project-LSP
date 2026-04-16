-- Tikecting.yuu - Additional Flight Data Seeder
-- Updated: 2026-04-16

START TRANSACTION;

-- Clear old sample data first to avoid duplicates
DELETE FROM flights;

INSERT INTO `flights` (`flight_code`, `airline_name`, `origin`, `origin_code`, `destination`, `dest_code`, `depart_date`, `depart_time`, `arrive_time`, `duration`, `transit`, `price`, `class`, `is_promo`, `promo_badge`, `baggage_capacity`, `rating`, `delay_history`) VALUES
-- Jakarta (CGK) Routes
('TY-101', 'Tikecting Air', 'Jakarta', 'CGK', 'Surabaya', 'SUB', '2026-05-01', '08:00:00', '09:35:00', '1j 35m', 'direct', 850000.00, 'economy', 1, 'Best Deal', '20kg', 4.8, 'On Time'),
('TY-102', 'Tikecting Air', 'Jakarta', 'CGK', 'Surabaya', 'SUB', '2026-05-01', '13:00:00', '14:35:00', '1j 35m', 'direct', 920000.00, 'economy', 0, NULL, '20kg', 4.7, 'On Time'),
('TY-103', 'Tikecting Air', 'Jakarta', 'CGK', 'Surabaya', 'SUB', '2026-05-01', '18:30:00', '20:05:00', '1j 35m', 'direct', 880000.00, 'economy', 0, NULL, '20kg', 4.6, 'Slight Delay'),

('TY-201', 'Tikecting Air', 'Jakarta', 'CGK', 'Bali', 'DPS', '2026-05-02', '06:00:00', '08:50:00', '1j 50m', 'direct', 1250000.00, 'economy', 1, 'Holiday Promo', '20kg', 4.9, 'On Time'),
('TY-202', 'Tikecting Air', 'Jakarta', 'CGK', 'Bali', 'DPS', '2026-05-02', '10:00:00', '12:50:00', '1j 50m', 'direct', 1450000.00, 'business', 0, NULL, '30kg', 4.8, 'On Time'),
('TY-203', 'Tikecting Air', 'Jakarta', 'CGK', 'Bali', 'DPS', '2026-05-02', '15:30:00', '18:20:00', '1j 50m', 'direct', 1100000.00, 'economy', 0, NULL, '20kg', 4.7, 'On Time'),

('TY-301', 'Tikecting Air', 'Jakarta', 'CGK', 'Medan', 'KNO', '2026-05-03', '07:30:00', '09:50:00', '2j 20m', 'direct', 1350000.00, 'economy', 0, NULL, '20kg', 4.5, 'On Time'),
('TY-302', 'Tikecting Air', 'Jakarta', 'CGK', 'Medan', 'KNO', '2026-05-03', '14:00:00', '16:20:00', '2j 20m', 'direct', 2500000.00, 'business', 1, 'Luxury Trip', '40kg', 4.9, 'On Time'),

('TY-401', 'Tikecting Air', 'Jakarta', 'CGK', 'Makassar', 'UPG', '2026-05-04', '05:00:00', '08:25:00', '2j 25m', 'direct', 1400000.00, 'economy', 0, NULL, '20kg', 4.6, 'On Time'),
('TY-402', 'Tikecting Air', 'Jakarta', 'CGK', 'Makassar', 'UPG', '2026-05-04', '20:00:00', '23:25:00', '2j 25m', 'direct', 1200000.00, 'economy', 1, 'Late Night', '20kg', 4.4, 'On Time'),

('TY-501', 'Tikecting Air', 'Jakarta', 'CGK', 'Yogyakarta', 'YIA', '2026-05-05', '09:00:00', '10:15:00', '1j 15m', 'direct', 750000.00, 'economy', 1, 'Culture Trip', '20kg', 4.8, 'On Time'),

-- Surabaya (SUB) Routes
('TY-601', 'Tikecting Air', 'Surabaya', 'SUB', 'Jakarta', 'CGK', '2026-05-01', '06:00:00', '07:35:00', '1j 35m', 'direct', 890000.00, 'economy', 0, NULL, '20kg', 4.7, 'On Time'),
('TY-602', 'Tikecting Air', 'Surabaya', 'SUB', 'Bali', 'DPS', '2026-05-02', '11:00:00', '12:00:00', '1j 00m', 'direct', 650000.00, 'economy', 1, 'Island Hop', '20kg', 4.8, 'On Time'),

-- Bali (DPS) Routes
('TY-701', 'Tikecting Air', 'Bali', 'DPS', 'Jakarta', 'CGK', '2026-05-03', '19:00:00', '20:50:00', '1j 50m', 'direct', 1300000.00, 'economy', 0, NULL, '20kg', 4.6, 'Slight Delay'),
('TY-702', 'Tikecting Air', 'Bali', 'DPS', 'Surabaya', 'SUB', '2026-05-04', '08:00:00', '09:00:00', '1j 00m', 'direct', 700000.00, 'economy', 0, NULL, '20kg', 4.7, 'On Time'),

-- Nusantara (IKN) / Balikpapan (BPN) Routes
('TY-801', 'Tikecting Air', 'Jakarta', 'CGK', 'Nusantara', 'IKN', '2026-05-01', '10:00:00', '13:10:00', '2j 10m', 'direct', 1850000.00, 'business', 1, 'Capital Promo', '35kg', 5.0, 'On Time'),
('TY-802', 'Tikecting Air', 'Jakarta', 'CGK', 'Nusantara', 'IKN', '2026-05-02', '14:00:00', '17:10:00', '2j 10m', 'direct', 1250000.00, 'economy', 0, NULL, '20kg', 4.8, 'On Time');

COMMIT;
