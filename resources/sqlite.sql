-- #!sqlite

-- #{ portals
-- #{ createTable
CREATE TABLE IF NOT EXISTS portals (
    id INTEGER PRIMARY KEY AUTOINCREMENT, -- Unique ID for each entry
    name TEXT NOT NULL UNIQUE, -- Portal name (must be unique)
    owner TEXT NOT NULL, -- Owner of the portal (player name)
    data TEXT NOT NULL -- JSON-encoded portal data (worldName, pos1, pos2, message, cmd, etc.)
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

-- #{ exists
-- # :name string
SELECT COUNT(*) AS count FROM portals WHERE name = :name;
-- #}

-- #{ delete
-- # :name string
DELETE FROM portals WHERE name = :name;
-- #}

-- #}