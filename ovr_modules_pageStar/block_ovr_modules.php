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
 * This file contains the Activity modules block.
 *@autor(a) : Marluce Ap. Vitor
 * @package    block_ovr_modules
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_ovr_modules extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_ovr_modules');
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT;

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

       /* foreach($modinfo->cms as $cm) {
            // Exclude activities which are not visible or have no link (=label)
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
            if (array_key_exists($cm->modname, $modfullnames)) {
                continue;
            }
            if (!array_key_exists($cm->modname, $archetypes)) {
                $archetypes[$cm->modname] = plugin_supports('mod', $cm->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
            }
            if ($archetypes[$cm->modname] == MOD_ARCHETYPE_RESOURCE) {
                if (!array_key_exists('resources', $modfullnames)) {
                    $modfullnames['resources'] = get_string('resources');
                }
            } else {
                $modfullnames[$cm->modname] = $cm->modplural;
            }
        }

        core_collator::asort($modfullnames);*/
               
 $listcourse = array_keys($section);
   
//print_r($listcourse);


/*for(int i=0; i<=5;i++){$escolha =($listcourse[0]);
};*/

  
 // echo "O Valor de I = ".$i;
//for($i =0; $i <sizeof($listcourse); $i++){
 //}

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

 
//$escolha =$_POST['submit'];
//    echo '<br />The ' . $_POST['submit'].'<br />';
                /*$this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/ovr_modules/index.php?id='.$course->id.'&section='.$escolha.'">Recomendar VideoAulas</a>';*/


               /* //ver como colocar para pegar a seção ??????
                if ($ADMIN->fulltree) {
                      $settings->add(new admin_setting_configselect('block_ovr_modules/list_course', get_string('list_course', 'block_ovr_modules'), get_string('select_course', 'block_ovr_modules'),'0',$listcourse));
            
        }*/
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


