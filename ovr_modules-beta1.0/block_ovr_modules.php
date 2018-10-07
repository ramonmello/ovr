<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *@autor(a) : Marluce Ap. Vitor 
 * @package    block_ovr_modules
 * @copyright  2018 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_ovr_modules extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_ovr_modules');
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';


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
 $context = $DB->get_record_sql("select roleid from {role_assignments} where contextid=".$courseContext->id . " AND userid=". $USER->id);
  
  if($context->roleid != 5){
    $this->content->items[] = '<b>Recomendar VideoAulas</b>';
    $combox = '<form action="" method="post">';
    $combox.= '<label for="section">Qual semana deseja inserir a recomendação?</label><select id = "t" onchange="location = this.value;"> ';
    $combox.='  <option value="" >--Selecione--</option>';
    for($i =0; $i <sizeof($listcourse); $i++){
    $combox.='  <option value="'.$CFG->wwwroot.'/blocks/ovr_modules/index.php?id='.$course->id.'&section='.$i.'" >'.$i.'</option>';
    }
    $combox.='</select>';
    $combox.='</form>';
    $this->content->items[] = $combox;
}
 

 

        return $this->content;
    }



    /**
     * Returns the role that best describes this blocks contents.
     *
     * This returns 'navigation' as the blocks contents is a list of links to activities and resources.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }
}


