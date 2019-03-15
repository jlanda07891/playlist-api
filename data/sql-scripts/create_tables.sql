-- -----------------------------------------------------
-- Schema playlist
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `playlist` DEFAULT CHARACTER SET utf8 ;
USE `playlist` ;

DROP TABLE IF EXISTS `playlist`.`video`;
DROP TABLE IF EXISTS `playlist`.`playlist`;
DROP TABLE IF EXISTS `playlist`.`playlist_video`;

-- -----------------------------------------------------
-- Table `playlist`.`video`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `playlist`.`video` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `thumbnail` VARCHAR(2083) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `playlist`.`playlist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `playlist`.`playlist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `playlist`.`playlist_video`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `playlist`.`playlist_video` (
  `idplaylist_video` INT NOT NULL AUTO_INCREMENT,
  `video_id` INT NOT NULL,
  `video_order` INT NOT NULL,
  `playlist_id` INT NOT NULL,
  PRIMARY KEY (`idplaylist_video`),
  INDEX `fk_playlist_video_video_idx` (`video_id` ASC),
  INDEX `fk_playlist_video_playlist1_idx` (`playlist_id` ASC))
ENGINE = InnoDB;
