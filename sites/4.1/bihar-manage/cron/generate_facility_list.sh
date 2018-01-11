#!/bin/bash
reports="facility_tree facility_selector_limited facility_selector_full facility_list"
for report in $reports
do
    echo Generating $report >> /tmp/report_cron.log
    date >> /tmp/report_cron.log
    /usr/bin/php ../pages/index.php --page=/CustomReports/generate_force/$report --nocheck=1
    date >> /tmp/report_cron.log
done
