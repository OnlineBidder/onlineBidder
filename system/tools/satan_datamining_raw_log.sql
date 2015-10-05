CREATE TABLE `satan_datamining_raw_log` (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    campaign_id MEDIUMINT UNSIGNED NOT NULL,
    `platform` ENUM('3000', '3001', '3002', '3003') NOT NULL,
    `ad_title` MEDIUMINT UNSIGNED NOT NULL,
    `ad_image` MEDIUMINT UNSIGNED NOT NULL,
    `ad_text` MEDIUMINT UNSIGNED NOT NULL,
    `age` TINYINT UNSIGNED NOT NULL,
    `sex` ENUM('4000', '4001', '4002') NOT NULL,
    `price` INT UNSIGNED NOT NULL,
    PRIMARY KEY(id),
    INDEX `datamining_raw_log_campaign_id` (`campaign_id`)
) ENGINE=InnoDB;