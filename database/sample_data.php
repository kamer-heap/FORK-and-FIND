<?php
// Generates database/sample_data.sql with correct password hashes. Run: php database/sample_data.php
$pw = fn(string $plain) => password_hash($plain, PASSWORD_DEFAULT);
$q  = fn(string $s) => "'" . str_replace("'", "''", $s) . "'";

$users = [
  ['Admin',          'admin@forkfind.test',   'Admin@1234',   'admin',   1],
  ['Bilal (owner)',  'bilal@forkfind.test',   'Owner@1234',   'owner',   1],
  ['Sara (owner)',   'sara@forkfind.test',    'Owner@1234',   'owner',   1],
  ['Usman (owner)',  'usman@forkfind.test',   'Owner@1234',   'owner',   1],
  ['Newowner',       'pending@forkfind.test', 'Owner@1234',   'owner',   0],
  ['Ayesha',         'ayesha@forkfind.test',  'Student@1234', 'student', 1],
  ['Hamza',          'hamza@forkfind.test',   'Student@1234', 'student', 1],
];
$out = "USE fork_and_find;\n\n";
foreach ($users as $u) {
    $out .= sprintf("INSERT INTO Users (name,email,password,role,is_approved) VALUES (%s,%s,%s,%s,%d);\n",
        $q($u[0]), $q($u[1]), $q($pw($u[2])), $q($u[3]), $u[4]);
}
$out .= <<<'SQL'

-- user_id: 1 admin, 2 Bilal, 3 Sara, 4 Usman, 5 pending owner, 6 Ayesha, 7 Hamza
INSERT INTO Cafeterias (name, department, location, timings, owner_id) VALUES
 ('Biryani Point', 'Computer Science', 'CS Block, Ground Floor', '8:00 AM - 5:00 PM', 2),
 ('Dragon Wok',    'Management Sciences', 'Business Block, 1st Floor', '9:00 AM - 4:00 PM', 3),
 ('Chai Adda',     'Mathematics', 'Math Dept Lawn', '8:00 AM - 6:00 PM', 4),
 ('Green Bites',   'Botany', 'Botany Garden', '9:00 AM - 3:00 PM', NULL);

INSERT INTO Menu_Items (cafe_id, name, price, category, available) VALUES
 (1, 'Chicken Biryani', 250, 'Rice', 1),
 (1, 'Beef Pulao',      300, 'Rice', 1),
 (1, 'Raita',            40, 'Sides', 1),
 (2, 'Chicken Chowmein',280, 'Chinese', 1),
 (2, 'Chicken Biryani', 320, 'Rice', 1),
 (2, 'Fried Rice',      240, 'Chinese', 0),
 (3, 'Doodh Patti Chai', 60, 'Drinks', 1),
 (3, 'Paratha Roll',    180, 'Snacks', 1),
 (3, 'Samosa',           30, 'Snacks', 1),
 (4, 'Fruit Chaat',     150, 'Healthy', 1);

INSERT INTO Reviews (item_id, user_id, rating, comment) VALUES
 (1, 6, 5, 'Best biryani on campus, great portion.'),
 (1, 7, 4, 'Very good, a bit spicy.'),
 (5, 6, 3, 'Okay but pricey.'),
 (4, 7, 5, 'Proper chowmein, loved it.'),
 (7, 6, 5, 'Perfect chai.'),
 (8, 7, 4, 'Filling and cheap.');
SQL;
file_put_contents(__DIR__ . '/sample_data.sql', $out . "\n");
echo "sample_data.sql written\n";
