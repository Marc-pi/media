# Doc table
CREATE TABLE `{doc}`
(
    `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `path`         VARCHAR(255)        NOT NULL DEFAULT '',
    `filename`     VARCHAR(255)        NOT NULL DEFAULT '',
    `attributes`   TEXT,
    `mimetype`     VARCHAR(255)        NOT NULL DEFAULT '',
    `title`        VARCHAR(255)        NOT NULL DEFAULT '',
    `description`  VARCHAR(255)        NOT NULL DEFAULT '',
    `active`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `time_created` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_updated` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_deleted` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `appkey`       VARCHAR(64)         NOT NULL DEFAULT '',
    `uid`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `count`        INT(10)             NOT NULL DEFAULT '0',
    `season`       TINYINT(4)                   DEFAULT NULL,
    `updated_by`   INT(11)                      DEFAULT NULL,
    `license_type` VARCHAR(255)                 DEFAULT NULL,
    `copyright`    VARCHAR(255)                 DEFAULT NULL,
    `cropping`     TEXT,
    `featured`     TINYINT(4)          NOT NULL DEFAULT '0',
    `latitude`     VARCHAR(16)         NOT NULL DEFAULT '',
    `longitude`    VARCHAR(16)         NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `active` (`active`),
    KEY `uid` (`uid`),
    KEY `appkey` (`appkey`),
    FULLTEXT KEY `search_idx` (`title`, `description`),
    FULLTEXT KEY `search_2_idx` (`title`, `description`, `filename`),
    FULLTEXT KEY `search_title_idx` (`title`),
    FULLTEXT KEY `search_description_idx` (`title`, `description`)
);

# Test table
CREATE TABLE `{test}`
(
    `id`                INT          NOT NULL AUTO_INCREMENT,
    `title`             VARCHAR(255) NOT NULL,
    `main_image`        INT          NULL,
    `additional_images` VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
);

# Link table
CREATE TABLE `{link}`
(
    `id`          INT(10)     NOT NULL AUTO_INCREMENT,
    `module`      VARCHAR(20) NOT NULL DEFAULT '',
    `object_name` VARCHAR(50) NOT NULL DEFAULT '',
    `object_id`   INT(10)     NOT NULL DEFAULT 0,
    `field`       VARCHAR(50) NOT NULL DEFAULT '',
    `media_id`    INT(10)     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY (`media_id`)
);

# Extended meta for docs
CREATE TABLE `{meta}`
(
    `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `doc`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `name`  VARCHAR(64)      NOT NULL DEFAULT '',
    `value` TEXT,
    PRIMARY KEY (`id`),
    UNIQUE KEY `meta` (`doc`, `name`)
);

# Application table, for module management only
CREATE TABLE `{application}`
(
    `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `appkey` VARCHAR(64)               DEFAULT NULL,
    `title`  VARCHAR(255)     NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `appkey` (`appkey`)
);
