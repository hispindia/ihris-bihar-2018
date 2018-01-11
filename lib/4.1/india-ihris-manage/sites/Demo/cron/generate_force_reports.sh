#!/bin/bash
reports="search_people contract_staff regular_staff training staff_chart service person_education Current_posting"
for report in $reports
do
    echo Generating $report
    /usr/bin/php ../pages/index.php --page=/CustomReports/generate_force/$report --nocheck=1
done
