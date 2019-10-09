<?php

function block_student_performance_get_performance_factor($courseid, $userid){

    $enrolinfo = block_student_performance_get_enrol_info($courseid, $userid);

    $gradefactor = block_student_performance_get_grades_factor($courseid, $userid, $enrolinfo);
    $activitiesfactor = block_student_performance_get_activities_factor($courseid, $userid, $enrolinfo);

    return $gradefactor*0.5 + $activitiesfactor*0.5;
}

function block_student_performance_get_grades_factor($courseid, $userid, $enrolinfo){
    /*
      Factor(F) is given by:

      F =  CG * 10 / TG

      CG = (Current final grade / Days of enrolment)
      TG = (Max final grade / Course duration in days)

    */

    // TODO

    return 0;
}

function block_student_performance_get_activities_factor($courseid, $userid, $enrolinfo){
    /*
      Factor(F) is given by:

      F =  AC * 10 / AT

      AC = (Activities completed by student / Days of enrolment)
      TA = (Total gradable activities / Course duration in days)

    */

    // Gradable activities count
    $items = block_student_performance_get_grade_items($courseid);
    $itemscompleted = block_student_performance_get_items_completed($courseid, $userid);

    // Variables for enrolment factor calculation
    $itemsperday = $items / block_student_performance_get_course_duration($enrolinfo);
    $completedperday = $itemscompleted / block_student_performance_get_days_enrolled($enrolinfo);

    // Enrolment factor formula
    $factor = $completedperday * 10 / (float)$itemsperday;

    return $factor;

}

function block_student_performance_get_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items}
            WHERE courseid=? AND itemtype!='course'";

    $count = $DB->count_records_sql($sql, [$courseid]);

    return $count;
}

function block_student_performance_get_items_completed($courseid, $userid){
    global $DB;

    $sql = "SELECT COUNT(*)
            FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=?
            AND itemtype='mod' AND g.aggregationstatus='used'";

    $count = $DB->count_records_sql($sql, [$courseid, $userid]);

    return $count;
}

function block_student_performance_get_enrol_info($courseid, $userid){
    global $DB;

    $sql = "SELECT ue.timestart, ue.timeend, e.enrolperiod
           FROM {user_enrolments} ue, {enrol} e
           WHERE ue.userid=? AND ue.enrolid=e.id AND e.courseid=?";

    $enrolinfo = $DB->get_record_sql($sql, [$userid, $courseid]);

    return $enrolinfo;

}

function block_student_performance_get_days_enrolled($enrolinfo){
    return (float) ceil(time() - $enrolinfo->timestart / 86400);
}

function block_student_performance_get_course_duration($enrolinfo){
    return (float) ceil(($enrolinfo->timeend - $enrolinfo->timestart) / 86400);
}
