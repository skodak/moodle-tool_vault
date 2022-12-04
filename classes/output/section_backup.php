<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_vault\output;

use tool_vault\api;
use tool_vault\constants;
use tool_vault\form\general_settings_form;
use tool_vault\local\helpers\ui;
use tool_vault\local\models\backup_model;
use tool_vault\local\models\operation_model;

/**
 * Tab backup
 *
 * @package     tool_vault
 * @copyright   2022 Marina Glancy <marina.glancy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_backup extends section_base implements \templatable {

    /**
     * Process tab actions
     */
    public function process() {
        global $PAGE, $DB;
        $action = optional_param('action', null, PARAM_ALPHANUMEXT);

        if ($action === 'startbackup' && confirm_sesskey()) {
            $backup = \tool_vault\site_backup::schedule([
                'passphrase' => optional_param('passphrase', null, PARAM_RAW),
                'description' => optional_param('description', null, PARAM_NOTAGS),
            ]);
            redirect(ui::progressurl(['accesskey' => $backup->get_model()->accesskey]));
        }

        if ($action === 'forgetapikey' && confirm_sesskey()) {
            api::set_api_key(null);
            redirect($PAGE->url);
        }
    }

    /**
     * Export for output
     *
     * @param \tool_vault\output\renderer $output
     * @return false[]
     */
    public function export_for_template($output): array {
        global $CFG, $USER;
        $activeprocesses = operation_model::get_active_processes(true);
        $lastbackup = backup_model::get_last();
        $result = [
            'canstartbackup' => empty($activeprocesses),
            'lastoperation' => ($lastbackup && $lastbackup->show_as_last_operation()) ?
                (new last_operation($lastbackup))->export_for_template($output) : null,
        ];

        $result['startbackupurl'] = ui::backupurl(['action' => 'startbackup', 'sesskey' => sesskey()])->out(false);
        $result['defaultbackupdescription'] = $CFG->wwwroot.' by '.fullname($USER); // TODO string?

        if (!api::is_registered()) {
            $form = new general_settings_form(false);
            $result['registrationform'] = $form->render();
            $result['canstartbackup'] = false;
        }

        $backups = backup_model::get_records(null, null, 1, 20);
        $result['backups'] = [];
        foreach ($backups as $backup) {
            $result['backups'][] = (new past_backup($backup))->export_for_template($output);
        }
        $result['haspastbackups'] = !empty($result['backups']);
        $result['restoreallowed'] = api::are_restores_allowed();
        return $result;
    }
}
