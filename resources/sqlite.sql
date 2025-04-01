-- #!sqlite

-- #{ portals
-- #{ createTable
CREATE TABLE IF NOT EXISTS portals (
    id INTEGER PRIMARY KEY AUTOINCREMENT, -- Unique ID for each entry
    name TEXT NOT NULL, -- Portal name
    owner TEXT NOT NULL, -- Owner of the portal (player name)
    data TEXT NOT NULL, -- JSON-encoded portal data (worldName, pos1, pos2, message, cmd, etc.)
    UNIQUE(name, owner) -- Make name unique per owner
);
-- #}

-- #{ save
-- # :name string
-- # :owner string
-- # :data string
INSERT INTO portals (name, owner, data)
VALUES (:name, :owner, :data);
-- #}

-- #{ update
-- # :name string
-- # :data string
UPDATE portals SET data = :data WHERE name = :name;
-- #}

-- #{ fetchByOwner
-- # :owner string
SELECT * FROM portals WHERE owner = :owner;
-- #}

-- #{ fetchByName
-- # :name string
SELECT * FROM portals WHERE name = :name;
-- #}

-- #{ fetchAll
SELECT * FROM portals;
-- #}

-- #{ exists
-- # :name string
-- # :owner string
SELECT COUNT(*) AS count FROM portals WHERE name = :name AND owner = :owner;
-- #}

-- #{ delete
-- # :name string
DELETE FROM portals WHERE name = :name;
-- #}

-- #}