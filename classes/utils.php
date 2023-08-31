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
namespace bbbext_flexurl;

use core_course\external\course_module_summary_exporter;
use core_course\external\course_summary_exporter;

/**
 * Utility class
 *
 * @package   bbbext_flexurl
 * @copyright 2023 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Laurent David (laurent@call-learning.fr)
 */
class utils {
    /**
     * Types of additional parameters
     */
    public const PARAM_TYPES = [
        'eventtype' => PARAM_ALPHA,
        'paramname' => PARAM_ALPHA,
        'paramvalue' => PARAM_RAW,
    ];

    /**
     * Get option group for parameters.
     *
     * @return array
     */
    public static function get_options_for_parameters(): array {
        $parametertypes = self::get_parameter_types();
        $options = [];
        $selectedptypes = explode(',', get_config('bbbext_flexurl', 'available_info'));
        foreach ($parametertypes as $key => $value) {
            if (in_array($key, $selectedptypes)) {
                $options[$value] = self::get_fields_for_parameter($key);
            }
        }
        return $options;
    }

    /**
     * Get parameter types
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_parameter_types(): array {
        return [
            'activityinfo' => get_string('activity_info', 'bbbext_flexurl'),
            'courseinfo' => get_string('course_info', 'bbbext_flexurl'),
            'userinfo' => get_string('user_info', 'bbbext_flexurl'),
        ];
    }

    public static function get_fields_for_parameter(string $key): array {
        if (method_exists(self::class, 'get_' . $key . '_fields')) {
            return call_user_func(self::class . '::get_' . $key . '_fields');
        } else {
            return [];
        }
    }

    /**
     * Get user fields prefixed by user.
     * @return string[]
     */
    public static function get_userinfo_fields() {
        $userfields = \core_user\fields::get_identity_fields(\context_system::instance());
        $userfields = array_merge($userfields, \core_user\fields::get_name_fields());
        sort($userfields);
        return array_map(
            function($field) {
                return "user.{$field}";
            },
            $userfields
        );
    }

    /**
     * Course information
     *
     * @return string[]
     */
    public static function get_courseinfo_fields() {
        return array_map(
            function($field) {
                return "courseinfo.{$field}";
            },
            array_keys(course_summary_exporter::read_properties_definition())
        );
    }

    /**
     * Activity information
     *
     * @return string[]
     */
    public static function get_activityinfo_fields() {
        return array_map(
            function($field) {
                return "activityinfo.{$field}";
            },
            array_keys(course_module_summary_exporter::read_properties_definition())
        );
    }

    /**
     * Type of event : create or join
     *
     * @return array
     */
    public static function get_option_for_eventtype() {
        return [
                1 => get_string('event_join', 'bbbext_flexurl'),
                2 => get_string('event_create', 'bbbext_flexurl'),
        ];
    }
}
