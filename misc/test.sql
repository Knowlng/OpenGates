CREATE TABLE IF NOT EXISTS users (
    id serial PRIMARY KEY,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    email character varying(100) NOT NULL UNIQUE,
    balance numeric(10,2) DEFAULT 11,
    CONSTRAINT users_username_key UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS services (
    id serial PRIMARY KEY,
    user_id integer NOT NULL REFERENCES users(id),
    service_name character varying(255) NOT NULL,
    cost numeric(10,2) NOT NULL,
    purchase_date timestamp DEFAULT CURRENT_TIMESTAMP,
    vmid integer,
    ssh_string character varying(255) NOT NULL
);
