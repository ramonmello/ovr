<?php

defined('MOODLE_INTERNAL') || die();

/**
 * 
 *
 * @param int $oldversion
 * @param object $block
 * @return bool
 */
function xmldb_block_ovr_upgrade($oldversion, $block) {
    global $DB;

    if ($oldversion < 2015030300) {
        // Drop the mirror table.
        $dbman = $DB->get_manager();

        // Define table to be dropped.
        $table = new xmldb_table('ovr_cmc_mirror');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2015030300, 'block', 'ovr');
    }

    if ($oldversion < 2015051800) {
        $criteria = array(
            'plugin' => 'block_ovr',
            'name' => 'lastcomputedid'
        );

        $DB->delete_records('config_plugins', $criteria);

        upgrade_plugin_savepoint(true, 2015051800, 'block', 'ovr');
    }

    return true;
}
