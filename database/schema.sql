CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL CHECK (role IN ('admin', 'specialist', 'customer')),
    password_hash TEXT NOT NULL,
    specialty TEXT,
    phone TEXT,
    bio TEXT,
    avatar TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE working_hours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    specialist_id INTEGER NOT NULL,
    weekday INTEGER NOT NULL CHECK (weekday BETWEEN 1 AND 7),
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    UNIQUE (specialist_id, weekday),
    FOREIGN KEY (specialist_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE blocked_slots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    specialist_id INTEGER NOT NULL,
    slot_start TEXT NOT NULL,
    slot_end TEXT NOT NULL,
    reason TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (specialist_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    specialist_id INTEGER NOT NULL,
    service_id INTEGER,
    customer_name TEXT,
    customer_phone TEXT,
    service_name TEXT,
    service_price REAL NOT NULL DEFAULT 0,
    slot_start TEXT NOT NULL,
    slot_end TEXT NOT NULL,
    note TEXT,
    status TEXT NOT NULL DEFAULT 'booked' CHECK (status IN ('booked', 'cancelled', 'completed')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialist_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    key TEXT PRIMARY KEY,
    value TEXT
);

CREATE INDEX idx_appointments_specialist_start ON appointments (specialist_id, slot_start);
CREATE INDEX idx_blocked_slots_specialist_start ON blocked_slots (specialist_id, slot_start);
