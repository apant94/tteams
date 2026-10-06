CREATE TABLE conversations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL UNIQUE,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    CHECK (admin_id <> client_id),
    FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE INDEX conversations_admin_updated_index
    ON conversations (admin_id, updated_at DESC);

CREATE TABLE messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    conversation_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    body TEXT NOT NULL CHECK (length(trim(body)) > 0),
    created_at TEXT NOT NULL,
    read_at TEXT,
    FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE RESTRICT
);

CREATE INDEX messages_conversation_id_index
    ON messages (conversation_id, id);
