<?php
/*
  @author Marluce Ap. Vitor
  
*/

defined('MOODLE_INTERNAL') || die();

class block_ovr extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_ovr');
    }

    function get_content() {
      //  global $CFG, $OUTPUT;
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->content->text .= "site context";
        }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

//pega todas as informações da página
        $course = $this->page->course;

       // print_r($course->id);


        $section = $DB->get_records_sql("select section from {course_sections} where course=".$course->id);
        //print_r($section);


        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);
        $modfullnames = array();

        $archetypes = array();

               
 $listcourse = array_keys($section);
   

 $courseContext = context_course::instance($COURSE->id);

 $sql = "select roleid from {role_assignments} where contextid=".$courseContext->id . " AND userid=:user";

 $params['user'] = $USER->id;
 $context = $DB->get_record_sql($sql, $params);
  
  if($context->roleid != 5){
    $this->content->items[] = '<b>Recomendar VideoAulas</b>';
    $combox = '<form action="" method="post">';
    $combox.= '<label for="section">Qual semana deseja inserir a recomendação?</label><select id = "t" onchange="location = this.value;"> ';
    $combox.='  <option value="" >--Selecione--</option>';
    for($i =0; $i <sizeof($listcourse); $i++){
    $combox.='  <option value="'.$CFG->wwwroot.'/blocks/ovr/index.php?id='.$course->id.'&section='.$i.'" >'.$i.'</option>';
    }
    $combox.='</select>';
    $combox.='</form>';
    $this->content->items[] = $combox;
}
 

 



        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
