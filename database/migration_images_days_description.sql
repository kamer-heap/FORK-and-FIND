USE fork_and_find;

ALTER TABLE Cafeterias
    ADD COLUMN days VARCHAR(30) NULL AFTER timings,
    ADD COLUMN description VARCHAR(500) NULL AFTER days,
    ADD COLUMN image VARCHAR(100) NULL AFTER description;

ALTER TABLE Menu_Items
    ADD COLUMN description VARCHAR(500) NULL AFTER category,
    ADD COLUMN image VARCHAR(100) NULL AFTER description;
