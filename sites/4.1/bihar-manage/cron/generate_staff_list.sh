#!/bin/bash
#reports="staff_list"
reports="search_people otherdirectory directory2 doctorregularstaff"
for report in $reports
do
    echo Generating $report >> /tmp/report_cron.log
    date >> /tmp/report_cron.log
    /usr/bin/php ../pages/index.php --page=/CustomReports/generate_force/$report --nocheck=1
    date >> /tmp/report_cron.log
done
