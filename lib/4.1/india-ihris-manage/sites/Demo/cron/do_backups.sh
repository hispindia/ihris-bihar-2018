#!/bin/bash

DATABASE=
BACKUP_DIR=
DB_USER=backup
DB_PASS=


SUFFIX=`date +%F`

cd $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DATABASE > backup_${DATABASE}_${SUFFIX}.sql
bzip2 -f backup_${DATABASE}_${SUFFIX}.sql

#find $BACKUP_DIR -name "backup_${DATABASE}*.sql.bz2" -mtime +7 -not -name "backup_${DATABASE}*-01.sql.bz2" -exec rm {} \;
