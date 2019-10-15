<?php
class block_student_performance extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_student_performance');
    }

    public function applicable_formats() {
        return array('course-view' => true, 'site' => false);
    }

    public function get_content() {
        global $CFG;
        global $COURSE;
        global $USER;

        require_once($CFG->dirroot . '/blocks/student_performance/locallib.php');

        // Hide the block to non-logged in users and guests
        if ($this->content !== null || !isloggedin() || isguestuser()) {
          return $this->content;
        }

        // JavaScript
        $this->page->requires->jquery();
        $this->page->requires->js("/blocks/student_performance/js/gauge.min.js");
        $this->page->requires->js("/blocks/student_performance/js/gauge.js");

        // Calculation
        $enrolinfo = block_student_performance_get_enrol_info($COURSE->id, $USER->id);

        $activitiesfactor = block_student_performance_get_activities_factor($COURSE->id, $USER->id, $enrolinfo);
        $courseaverage = block_student_performance_get_course_average_factor($COURSE->id, $USER->id, $enrolinfo->timestart);
        $performancefactor = $activitiesfactor*0.9 + $courseaverage*0.1;

        $feedback = block_student_performance_get_feedback($performancefactor, $activitiesfactor, $courseaverage);

        // HTML
        $this->content         =  new stdClass;

        $this->content->text   = html_writer::tag(
            'canvas',
            '',
            array(
                'id' => 'gauge',
                'data-perf' => $performancefactor,
                'style' => 'max-width:200px;max-height:200px;'
            )
        );
        $this->content->text   .= html_writer::tag(
            'p',
            $feedback,
            array('style'=> 'text-align:center;position:relative;bottom:20px;')
        );

        $this->content->footer = '';

        return $this->content;
    }

}
