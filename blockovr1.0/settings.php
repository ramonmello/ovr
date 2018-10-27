<?php

/*
  @author Marluce Ap. Vitor
  
*/

defined('MOODLE_INTERNAL') || die();


$settings->add(new admin_setting_heading('sampleheader',
                                         get_string('headerconfig', 'block_ovr'),
                                         get_string('descconfig', 'block_ovr')));


$settings->add(new admin_setting_configtext('block_ovr/base',
                                            get_string('base', 'block_ovr'), 
                                            get_string('baseDESC', 'block_ovr'),
                                            'Teste', PARAM_TEXT));	
        	

$settings->add(new admin_setting_configtext('block_ovr/protocol',
                                            get_string('protocol', 'block_ovr'), 
                                            get_string('protocolDESC', 'block_ovr'),
                                            'http', PARAM_TEXT));


$settings->add(new admin_setting_configtext('block_ovr/url', 
                                        get_string('url', 'block_ovr'), 
                                        get_string('urlDESC', 'block_ovr'),
                                         '138.121.71.4', PARAM_TEXT));	

$settings->add(new admin_setting_configtext('block_ovr/port',
                                            get_string('port', 'block_ovr'), 
                                            get_string('portDESC', 'block_ovr'),
                                            '8082', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_ovr/cache', 
                                        get_string('cache', 'block_ovr'), 
                                        get_string('cacheDESC', 'block_ovr'),
                                         '/var/www/moodledata/cache', PARAM_TEXT));




