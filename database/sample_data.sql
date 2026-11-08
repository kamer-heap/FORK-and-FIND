USE fork_and_find;

INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Admin','admin@forkfind.test','$2y$10$rlsPKdgFpNK5g1T3fwgpE.9NEwGbs8k6tlVAYFmadhM1CKQY2Fba.','admin',1);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Bilal (owner)','bilal@forkfind.test','$2y$10$jTffag9uCG4toxLHD1/5R.vTBcnsJXrbP1Ei5WP5Xl4lvhWuejEVW','owner',1);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Sara (owner)','sara@forkfind.test','$2y$10$uf/w8aenPsQ7/7f9v.Nb1ehiOYTrcTwpzfOmCr2CXFV5rmuQxJY06','owner',1);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Usman (owner)','usman@forkfind.test','$2y$10$eXFlkx42yU3sPoGcJiL7j.NxWOWTfzKTLeYSYi//WvgWNJiJ7CSN.','owner',1);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Newowner','pending@forkfind.test','$2y$10$3qoyRIfDhbfTBLeOIb/PuuWvEgaq6h16GF1X9IgRiXpbTdhFBiVOu','owner',0);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Ayesha','ayesha@forkfind.test','$2y$10$DIctMySohVvDdsmdy4uwBe12Z6pcMhFUOCd/BX8qKYiek0SkvNtPm','student',1);
INSERT INTO Users (name,email,password,role,is_approved) VALUES ('Hamza','hamza@forkfind.test','$2y$10$0vjQ55Wsrgn5UJLkpf.l4OVAXHXxxQ5dNvGMaH6.Noz8N9QCak2XK','student',1);

-- user_id: 1 admin, 2 Bilal, 3 Sara, 4 Usman, 5 pending owner, 6 Ayesha, 7 Hamza
INSERT INTO Cafeterias (name, department, location, timings, days, description, image, owner_id) VALUES
 ('Biryani Point', 'Computer Science', 'CS Block, Ground Floor', '8:00 AM - 5:00 PM', 'Monday-Friday', NULL, NULL, 2),
 ('Dragon Wok',    'Management Sciences', 'Business Block, 1st Floor', '9:00 AM - 4:00 PM', 'Monday-Saturday', NULL, NULL, 3),
 ('Chai Adda',     'Mathematics', 'Math Dept Lawn', '8:00 AM - 6:00 PM', 'Monday-Sunday', NULL, NULL, 4),
 ('Green Bites',   'Botany', 'Botany Garden', '9:00 AM - 3:00 PM', 'Saturday-Sunday', NULL, NULL, NULL);

INSERT INTO Menu_Items (cafe_id, name, price, category, description, image, available) VALUES
 (1, 'Chicken Biryani', 250, 'Rice', NULL, '4a18594531bcc9a43a95838b.jpg', 1),
 (1, 'Beef Pulao',      300, 'Rice', NULL, '5c02978f015a304859fe327c.jpg', 1),
 (1, 'Raita',            40, 'Sides', NULL, '8b03da0b5852dc516fc7995e.webp', 1),
 (2, 'Chicken Chowmein',280, 'Chinese', NULL, '47e27ba45b6365a36ae7fd43.jpg', 1),
 (2, 'Chicken Biryani', 320, 'Rice', NULL, '90fe5b7a11e52c723ab3370d.jpg', 1),
 (2, 'Fried Rice',      240, 'Chinese', NULL, 'a84baa191e68d255ad52757e.webp', 0),
 (3, 'Doodh Patti Chai', 60, 'Drinks', NULL, 'ab9bc7fe061e87e48aae73f8.jpg', 1),
 (3, 'Paratha Roll',    180, 'Snacks', NULL, 'b31f1bf222fadf265837a17f.jpg', 1),
 (3, 'Samosa',           30, 'Snacks', NULL, 'b216e00b86bf24c1e44a11e1.jpg', 1),
 (4, 'Fruit Chaat',     150, 'Healthy', NULL, 'c3ef12459b334a8e368fb065.jpg', 1);

INSERT INTO Reviews (item_id, user_id, rating, comment) VALUES
 (1, 6, 5, 'Best biryani on campus, great portion.'),
 (1, 7, 4, 'Very good, a bit spicy.'),
 (5, 6, 3, 'Okay but pricey.'),
 (4, 7, 5, 'Proper chowmein, loved it.'),
 (7, 6, 5, 'Perfect chai.'),
 (8, 7, 4, 'Filling and cheap.');
