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

/**
 * Plugin administration pages are defined here.
 *
 * @package     qbank_genai
 * @category    admin
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('qbank_genai_settings', new lang_string('pluginname', 'qbank_genai'));

    // Language model provider.
    $provideroptions = [
        'OpenAI' => 'OpenAI',
        'Azure' => 'Azure',
    ];
    $settings->add(new admin_setting_configselect(
        'qbank_genai/provider',
        get_string('provider', 'qbank_genai'),
        get_string('providerdesc', 'qbank_genai'),
        'OpenAI',
        $provideroptions,
    ));

    // Azure endpoint.

    $settings->add(new admin_setting_configtext(
        'qbank_genai/azure_api_endpoint',
        get_string('azureapiendpoint', 'qbank_genai'),
        get_string('azureapiendpointdesc', 'qbank_genai'),
        '',
        PARAM_URL
    ));


    // OpenAI key.
    $settings->add(new admin_setting_configpasswordunmask(
        'qbank_genai/key',
        get_string('openaikey', 'qbank_genai'),
        get_string('openaikeydesc', 'qbank_genai'),
        '',
        PARAM_TEXT,
        50
    ));

    // Model.
    $options = [
        'gpt-3.5-turbo' => 'gpt-3.5-turbo',
        'gpt-4' => 'gpt-4',
        'gpt-4o' => 'gpt-4o',
    ];
    $settings->add(new admin_setting_configselect(
        'qbank_genai/model',
        get_string('model', 'qbank_genai'),
        get_string('openaikeydesc', 'qbank_genai'),
        'gpt-3.5-turbo',
        $options,
    ));

    // Number of tries.
    $settings->add(new admin_setting_configtext(
        'qbank_genai/numoftries',
        get_string('numoftriesset', 'qbank_genai'),
        get_string('numoftriesdesc', 'qbank_genai'),
        10,
        PARAM_INT,
        10
    ));

    // Presets
    $settings->add(new admin_setting_heading(
        'qbank_genai/presets',
        get_string('presets', 'qbank_genai'),
        get_string('presetsdesc', 'qbank_genai') .
            get_string('shareyourprompts', 'qbank_genai'),
    ));

    for ($i = 1; $i <= 10; $i++) {

        // Preset header.
        $settings->add(new admin_setting_heading(
            'qbank_genai/preset' . $i,
            get_string('preset', 'qbank_genai') . " $i",
            null
        ));

        // Preset name.
        $settings->add(new admin_setting_configtext(
            'qbank_genai/presetname' . $i,
            get_string('presetname', 'qbank_genai'),
            get_string('presetnamedesc', 'qbank_genai'),
            get_string('presetnamedefault' . $i, 'qbank_genai'),
        ));

        // Preset primer.
        $settings->add(new admin_setting_configtextarea(
            'qbank_genai/presettprimer' . $i,
            get_string('presetprimer', 'qbank_genai'),
            get_string('primer_help', 'qbank_genai'),
            get_string('presetprimerdefault' . $i, 'qbank_genai'),
            PARAM_TEXT,
            4000
        ));

        // Preset instructions.
        $settings->add(new admin_setting_configtextarea(
            'qbank_genai/presetinstructions' . $i,
            get_string('presetinstructions', 'qbank_genai'),
            get_string('instructions_help', 'qbank_genai'),
            get_string('presetinstructionsdefault' . $i, 'qbank_genai'),
            PARAM_TEXT,
            4000
        ));

        // Preset format.
        $formatoptions = [
            'gift' => 'GIFT format',
            'moodlexml' => 'Moodle XML format',
        ];
        $settings->add( new admin_setting_configselect(
            'qbank_genai/presetformat' . $i,
            get_string('presetformat', 'qbank_genai'),
            get_string('presetformatdesc', 'qbank_genai'),
            'gift',
            $formatoptions
        ));

        // Preset example.
        $settings->add(new admin_setting_configtextarea(
            'qbank_genai/presetexample' . $i,
            get_string('presetexample', 'qbank_genai'),
            get_string('example_help', 'qbank_genai'),
            get_string('presetexampledefault' . $i, 'qbank_genai'),
            PARAM_TEXT,
            4000
        ));
    }

    $ADMIN->add('localplugins', $settings);

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.
    }
}
