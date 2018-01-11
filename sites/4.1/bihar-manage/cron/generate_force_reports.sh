#!/bin/bash
#reports="facility_tree search_people employee_profile facility_selector_full facility_selector_limited staff_list Asha_Mamta_Staff-Directory contract_staff regular_staff facility_list staff_chart prof_qual specialty education facility_staff district_staff specialists specialty doctorregularstaff MedicalEducation facility_specialist directory2"
#reports="otherdirectory directory2 search_people"
#for report in $reports
#do
    #echo Generating $report
    #/usr/bin/php ../pages/index.php --page=/CustomReports/generate_force/$report --nocheck=1
#done
echo Generating All reports >> /tmp/report_all_cron.log
date >> /tmp/report_all_cron.log
/usr/bin/php ../pages/index.php --page=/CustomReports/generate_force --nocheck=1
date >> /tmp/report_all_cron.log
