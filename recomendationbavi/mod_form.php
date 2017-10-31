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
 * The main recomendationbavi configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_recomendationbavi
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
$PAGE->requires->js_init_call('M.mod_recomendationbavi.init');
/**
 * Module instance settings form
 *
 * @package    mod_recomendationbavi
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_recomendationbavi_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('recomendationbavi', 'recomendationbavi'), array('size' => '64'));
      //  $mform->addElement('text', 'name', get_string('title', 'title'), array('size' => '64'));
        $mform->addElement('button', 'intro', get_string("search"));
      //  print_object ($mform);
                $mform->addHelpButton('intro', 'recomendationbaviname', 'recomendationbavi');

      /*  $query= new BAViQuery;
        $query->put("content", format_string($recomendationbavi->name));
        $query->put("idservice", "Teste");
        $query->put("op", "mediaresource");

        $api= new BAViClient("http://138.121.71.4:8082/","ServidorRest");
        $resultado =  $api->query($query,1);
        $arrayResult =  json_decode($resultado ,true);
        	foreach($arrayResult["results"]["bindings"] as $key => $value){
        		echo $OUTPUT->heading(format_string($arrayResult["results"]["bindings"][$key]["link"]["value"]."<br>"));
          }
*/

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'recomendationbaviname', 'recomendationbavi');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of recomendationbavi settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('static', 'label1', 'recomendationbavisetting1', 'Your recomendationbavi fields go here. Replace me!');

        $mform->addElement('header', 'recomendationbavifieldset', get_string('recomendationbavifieldset', 'recomendationbavi'));
        $mform->addElement('static', 'label2', 'recomendationbavisetting2', 'Your recomendationbavi fields go here. Replace me!');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
