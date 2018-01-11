DELIMITER //
CREATE FUNCTION getDistrict( search VARCHAR(128) )
RETURNS VARCHAR(128)
BEGIN
    DECLARE search_form VARCHAR(128);
    DECLARE result VARCHAR(128);
    SET search_form = SUBSTRING_INDEX( search, '|', 1 );
    WHILE search_form <> 'district' DO
        CASE search_form
            WHEN 'position' THEN
                SELECT facility INTO result FROM hippo_position WHERE id = search;
            WHEN 'facility' THEN
                SELECT location INTO result FROM hippo_facility WHERE id = search;
            WHEN 'county' THEN
                SELECT district INTO result FROM hippo_county WHERE id = search;
            ELSE RETURN NULL;
        END CASE;
        SET search = result;
        SET search_form = SUBSTRING_INDEX( search, '|', 1 );
    END WHILE;
    IF search_form = 'district' THEN RETURN search;
    END IF;
    RETURN NULL;
END//
DROP FUNCTION IF EXISTS getDistrictStartDate//
CREATE FUNCTION getDistrictStartDate ( search_person VARCHAR(128), search_district VARCHAR(128) )
RETURNS DATETIME
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur_start_date DATETIME;
    DECLARE cur_district VARCHAR(128);
    DECLARE result DATETIME;
    DECLARE pers_pos CURSOR FOR SELECT getDistrict(position), start_date FROM hippo_person_position WHERE parent = search_person ORDER BY start_date DESC;
    DECLARE serv_hist CURSOR FOR SELECT getDistrict(district), from_date FROM hippo_service_history WHERE parent = search_person ORDER BY from_date DESC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET result = NULL;
    OPEN pers_pos;
    pers_pos_loop: LOOP
        FETCH pers_pos INTO cur_district, cur_start_date;
        IF done THEN
            LEAVE pers_pos_loop;
        END IF;
        IF cur_district = search_district THEN
            SET result = cur_start_date;
        ELSE
            RETURN result;
        END IF;
    END LOOP;

    SET done = FALSE;
    OPEN serv_hist;
    serv_hist_loop: LOOP
        FETCH serv_hist INTO cur_district, cur_start_date;
        IF done THEN
            LEAVE serv_hist_loop;
        END IF;
        IF cur_district = search_district THEN
            SET result = cur_start_date;
        ELSE
            RETURN result;
        END IF;
    END LOOP;
    RETURN result;
END//
