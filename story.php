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

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

defined('MOODLE_INTERNAL') || die();

//core_question\local\bank\helper::require_plugin_enabled('qbank_importquestions');
//$edittab = "genai";
// use "import" for edittab now since capabilities do not exist yet:

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) = question_edit_setup('import', '/question/bank/aigen/story.php');

list($catid, $catcontext) = explode(',', $pagevars['cat']);
if (!$category = $DB->get_record("question_categories", ['id' => $catid])) {
    throw new moodle_exception('nocategory', 'question');
}

$categorycontext = context::instance_by_id($category->contextid);
$category->context = $categorycontext;

// This page can be called without courseid or cmid in which case.
// We get the context from the category object.
if ($contexts === null) { // Need to get the course from the chosen category.
    $contexts = new core_question\local\bank\question_edit_contexts($categorycontext);
    $thiscontext = $contexts->lowest();
    if ($thiscontext->contextlevel == CONTEXT_COURSE) {
        require_login($thiscontext->instanceid, false);
    } else if ($thiscontext->contextlevel == CONTEXT_MODULE) {
        list($module, $cm) = get_module_from_cmid($thiscontext->instanceid);
        require_login($cm->course, false, $cm);
    }
    $contexts->require_one_edit_tab_cap($edittab);
}

$PAGE->set_url($thispageurl);

require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/locallib.php');

// $PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'qbank_genai'));
$PAGE->set_title(get_string('pluginname', 'qbank_genai'));
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('qbank_genai/state');

echo $OUTPUT->header();

$mform = new \qbank_genai\story_form(null, ['contexts' => $contexts]);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/course/view.php?id=' . $courseid);
} else if ($data = $mform->get_data()) {

    // Call the adhoc task.
    $task = new \qbank_genai\task\questions();
    if ($task) {
        $uniqid = uniqid($USER->id, true);
        $preset = $data->preset;
        $primer = 'primer' . $preset;
        $instructions = 'instructions' . $preset;
        $example = 'example' . $preset;
        $task->set_custom_data(['category' => $data->category,
                                'primer' => $data->$primer,
                                'instructions' => $data->$instructions,
                                'example' => $data->$example,
                                'story' => $data->story,
                                'numofquestions' => $data->numofquestions,
                                'addidentifier' => $data->addidentifier,
                                'courseid' => $data->courseid,
                                'userid' => $USER->id,
                                'uniqid' => $uniqid ]);
        \core\task\manager::queue_adhoc_task($task);
        $success = get_string('tasksuccess', 'qbank_genai');

    } else {
        $error = get_string('taskerror', 'qbank_genai');
    }
    // Check if the cron is overdue.
    $lastcron = get_config('tool_task', 'lastcronstart');
    $cronoverdue = ($lastcron < time() - 3600 * 24);

    // Prepare the data for the template.
    $datafortemplate = [
        'courseid' => $courseid,
        'wwwroot' => $CFG->wwwroot,
        'uniqid' => $uniqid,
        'userid' => $USER->id,
        'cron' => $cronoverdue,
    ];
    // Load the ready template.
    echo $OUTPUT->render_from_template('qbank_genai/loading', $datafortemplate);
} else {
    $mform->display();
}

echo $OUTPUT->footer();
