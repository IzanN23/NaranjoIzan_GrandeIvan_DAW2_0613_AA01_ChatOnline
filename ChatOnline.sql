CREATE DATABASE chat_online;
USE chat_online;

CREATE TABLE `tbl_users` (
  `id_users` INT NOT NULL AUTO_INCREMENT,
  `name_users` VARCHAR(100) NOT NULL,
  `username_users` VARCHAR(50) NOT NULL,
  `email_users` VARCHAR(254) NOT NULL,
  `password_users` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_users`),
  UNIQUE (`username_users`),
  UNIQUE (`email_users`)
) ENGINE=InnoDB;

CREATE TABLE `tbl_friend_request` (
  `id_request` INT NOT NULL AUTO_INCREMENT,
  `id_sender` INT NOT NULL,
  `id_receiver` INT NOT NULL,
  `status` ENUM('pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'pendiente',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_request`),
  UNIQUE (`id_sender`, `id_receiver`)
) ENGINE=InnoDB;

CREATE TABLE `tbl_messages` (
  `id_message` INT NOT NULL AUTO_INCREMENT,
  `id_sender` INT NOT NULL,
  `id_receiver` INT NOT NULL,
  `content` VARCHAR(250) NOT NULL,
  `message_type` ENUM('texto', 'imagen', 'archivo') NOT NULL DEFAULT 'texto',
  `file_path` VARCHAR(255) NULL,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`)
) ENGINE=InnoDB;

-- Claves foraneas
ALTER TABLE `tbl_friend_request`
  ADD CONSTRAINT `fk_request_sender`
  FOREIGN KEY (`id_sender`) REFERENCES `tbl_users` (`id_users`);

ALTER TABLE `tbl_friend_request`
  ADD CONSTRAINT `fk_request_receiver`
  FOREIGN KEY (`id_receiver`) REFERENCES `tbl_users` (`id_users`);

ALTER TABLE `tbl_messages`
  ADD CONSTRAINT `fk_message_sender`
  FOREIGN KEY (`id_sender`) REFERENCES `tbl_users` (`id_users`);

ALTER TABLE `tbl_messages`
  ADD CONSTRAINT `fk_message_receiver`
  FOREIGN KEY (`id_receiver`) REFERENCES `tbl_users` (`id_users`);
