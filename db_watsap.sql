CREATE DATABASE db_watsap;
USE db_watsap;

CREATE TABLE tbl_users (
  id_users INT AUTO_INCREMENT PRIMARY KEY,
  name_users VARCHAR(100) NOT NULL,
  username_users VARCHAR(50) NOT NULL UNIQUE,
  email_users VARCHAR(100) NOT NULL UNIQUE,
  password_users VARCHAR(100) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  -- Guarda la ruta de la foto en disco, no los bytes de la imagen.
  profile_image_path VARCHAR(255) DEFAULT NULL
);

CREATE TABLE tbl_friend_request (
  id_request INT AUTO_INCREMENT PRIMARY KEY,
  id_sender INT NOT NULL,
  id_receiver INT NOT NULL,
  status ENUM('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (id_sender, id_receiver),
  FOREIGN KEY (id_sender)   REFERENCES tbl_users(id_users) ON DELETE RESTRICT,
  FOREIGN KEY (id_receiver) REFERENCES tbl_users(id_users) ON DELETE RESTRICT,
  CHECK (id_sender <> id_receiver)
);

CREATE TABLE tbl_messages (
  id_message INT AUTO_INCREMENT PRIMARY KEY,
  id_sender INT NOT NULL,
  id_receiver INT NOT NULL,
  -- Un archivo puede ser un mensaje sin texto, por eso content admite vacio.
  content varchar(250) NOT NULL DEFAULT '',
  -- Indica si el mensaje es texto, una imagen o un archivo descargable.
  message_type ENUM('text','image','file') NOT NULL DEFAULT 'text',
  -- Ruta del archivo guardado en la carpeta archivos_chat.
  file_path VARCHAR(255) DEFAULT NULL,
  -- Nombre que se muestra al receptor; el archivo se guarda con nombre aleatorio.
  original_file_name VARCHAR(255) DEFAULT NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_sender)   REFERENCES tbl_users(id_users) ON DELETE RESTRICT,
  FOREIGN KEY (id_receiver) REFERENCES tbl_users(id_users) ON DELETE RESTRICT
);