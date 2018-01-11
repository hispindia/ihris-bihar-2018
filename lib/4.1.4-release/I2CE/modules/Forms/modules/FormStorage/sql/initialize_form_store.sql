CREATE TABLE IF NOT EXISTS form_history (
       `formid` TEXT NOT NULL ,
       `history` BLOB NOT NULL ,
       `date` TIMESTAMP NOT NULL
) ENGINE = InnoDB;
