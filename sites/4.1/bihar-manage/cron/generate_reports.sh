#!/bin/bash
reports="search_people employee_profile facility_selector_full facility_selector_limited staff_list Asha_Mamta_Staff-Directory contract_staff regular_staff facility_list staff_chart specialty education facility_staff district_staff specialists specialty"
for report in $reports
do
    echo Generating $report
    /usr/bin/php ../pages/index.php --page=/CustomReports/generate/$report --nocheck=1
done
