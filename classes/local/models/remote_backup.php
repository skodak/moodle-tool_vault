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

namespace tool_vault\local\models;

/**
 * Model for remote backup
 *
 * @package     tool_vault
 * @copyright   2022 Marina Glancy <marina.glancy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @property-read int $timecreated
 * @property-read string $backupkey
 * @property-read int $timefinished
 * @property-read string $status
 * @property-read array $info
 */
class remote_backup {
    /** @var array */
    protected $data;

    /**
     * Constructor
     *
     * @param array $b
     */
    public function __construct(array $b) {
        if (!empty($b['metadata'])) {
            foreach ($b['metadata'] as $k => $v) {
                $b[$k] = $v;
            }
        }
        unset($b['metadata']);
        $this->data = $b;
    }

    /**
     * Magic getter
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name) {
        return $this->data[$name] ?? null;
    }
}
