#!/bin/bash

DATABASE=ihris_manage
#BACKUP_DIR=/var/lib/iHRIS/sites/backups
#BACKUP_DIR=/mnt/backups
BACKUP_DIR=/Health-Society/Backup/iHRIS_DB
#MYCNF=/var/lib/iHRIS/sites/4.1/bihar-manage/cron/local/my.cnf
DB_USER=ihris_manage
DB_PASS=ihris@manage5683
DB_HOST=Egpf-Maintenance


SUFFIX=`date +%F`

cd $BACKUP_DIR
mysqldump -u $DB_USER -h $DB_HOST -p$DB_PASS $DATABASE | bzip2 - > backup_${DATABASE}_${SUFFIX}.sql.bz2
#bzip2 -f backup_${DATABASE}_${SUFFIX}.sql

find $BACKUP_DIR -name "backup_${DATABASE}*.sql.bz2" -mtime +7 -not -name "backup_${DATABASE}*-01.sql.bz2" -exec rm {} \;
