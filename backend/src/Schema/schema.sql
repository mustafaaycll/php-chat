PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    username TEXT PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    created_by TEXT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(username) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sent_by TEXT NOT NULL,
    sent_to INTEGER NOT NULL,
    sent_at INTEGER NOT NULL,
    content TEXT NOT NULL,
    FOREIGN KEY (sent_by) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (sent_to) REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS group_users (
    group_id INTEGER NOT NULL,
    user_id TEXT NOT NULL,
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(username) ON DELETE CASCADE
);