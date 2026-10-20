CREATE TABLE tbl_users (
  id_users INT AUTO_INCREMENT PRIMARY KEY,
  name_users VARCHAR(100) NOT NULL,
  username_users VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE tbl_friend_request (
  id_request INT AUTO_INCREMENT PRIMARY KEY,
  id_sender INT NOT NULL,
  id_receiver INT NOT NULL,
  status ENUM('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (id_sender, id_receiver),
  FOREIGN KEY (id_sender)   REFERENCES tbl_users(id_users) ON DELETE CASCADE,
  FOREIGN KEY (id_receiver) REFERENCES tbl_users(id_users) ON DELETE CASCADE,
  CHECK (id_sender <> id_receiver)
);

CREATE TABLE tbl_messages (
  id_message INT AUTO_INCREMENT PRIMARY KEY,
  id_sender INT NOT NULL,
  id_receiver INT NOT NULL,
  content varchar(250) NOT NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_sender)   REFERENCES tbl_users(id_users) ON DELETE CASCADE,
  FOREIGN KEY (id_receiver) REFERENCES tbl_users(id_users) ON DELETE CASCADE
);