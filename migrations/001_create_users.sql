CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('admin', 'client')),
    password_hash TEXT NOT NULL,
    activated_at TEXT NOT NULL,
    created_at TEXT NOT NULL
);

CREATE INDEX users_role_index ON users (role);
