CREATE DATABASE IF NOT EXISTS `nette_orm_test`;

USE `nette_orm_test`;
DROP TABLE IF EXISTS `participant`;
DROP TABLE IF EXISTS `event`;

CREATE TABLE `event`
(
    `event_id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `begin`    DATETIME                       NOT NULL,
    `end`      DATETIME                       NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `participant`
(
    `participant_id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `event_id`       INT                            NOT NULL,
    `status`         ENUM ('applied','cancelled')   NOT NULL DEFAULT 'applied',
    `name`           VARCHAR(64),
    CONSTRAINT `fk_participant_event`
        FOREIGN KEY (`event_id`)
            REFERENCES `event` (`event_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
) ENGINE = InnoDB;
