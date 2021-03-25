CREATE DATABASE IF NOT EXISTS `nette_orm_test`;

USE `nette_orm_test`;
CREATE TABLE IF NOT EXISTS `event`
(
    `event_id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `begin`    DATETIME                       NOT NULL,
    `end`      DATETIME                       NOT NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `participant`
(
    `participant_id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `event_id`       INT                            NOT NULL,
    `name`           VARCHAR(64),
    CONSTRAINT `fk_participant_event`
        FOREIGN KEY (`event_id`)
            REFERENCES `event` (`event_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
) ENGINE = InnoDB;
