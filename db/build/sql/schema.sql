
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(250) NOT NULL,
    `email` VARCHAR(250) NOT NULL,
    `pass_hash` CHAR(60) NOT NULL,
    `pass_salt` CHAR(8) NOT NULL,
    `ap` INTEGER DEFAULT 0 NOT NULL,
    `ep` INTEGER DEFAULT 0 NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `users_U_1` (`name`),
    UNIQUE INDEX `users_U_2` (`email`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- activities
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `activities`;

CREATE TABLE `activities`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(250) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `activities_U_1` (`name`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- user_activity_timelog
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `user_activity_timelog`;

CREATE TABLE `user_activity_timelog`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `user_id` INTEGER NOT NULL,
    `activity_id` INTEGER NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `user_activity_timelog_FI_1` (`user_id`),
    INDEX `user_activity_timelog_FI_2` (`activity_id`),
    CONSTRAINT `user_activity_timelog_FK_1`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`),
    CONSTRAINT `user_activity_timelog_FK_2`
        FOREIGN KEY (`activity_id`)
        REFERENCES `activities` (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- sessions
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions`
(
    `id` VARCHAR(255) NOT NULL,
    `data` TEXT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
