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
 * Class to handle gift format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace qbank_genai\local;

/**
 * Class to handle gift format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gift {

    /**
     * Parse the gift questions.
     *
     * @param int $categoryid
     * @param object $llmresponse
     * @param int $numofquestions
     * @param int $userid
     * @param int $genaiid
     * @param bool $addidentifier
     * @return false|object[]
     */
    public static function parse_questions(
        int $categoryid,
        object $llmresponse,
        int $numofquestions,
        int $userid,
        bool $addidentifier,
        int $genaiid
    ) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/format/gift/format.php');

        $qformat = new \qformat_gift();

        $questions = explode("\n\n", $llmresponse->text);

        if (count($questions) != $numofquestions) {
            return false;
        }

        $createdquestions = []; // Array of objects of created questions.
        foreach ($questions as $question) {

            $singlequestion = explode("\n", $question);

            // Manipulating question text manually for question text field.
            $questiontext = explode('{', $singlequestion[0]);

            $questiontext = trim(preg_replace('/^.*::/', '', $questiontext[0]));

            $qtype = 'multichoice';
            $q = $qformat->readquestion($singlequestion);

            // Check if question is valid.
            if (!$q) {
                return false;
            }
            $q->category = $categoryid;
            $q->createdby = $userid;
            $q->modifiedby = $userid;
            $q->timecreated = time();
            $q->timemodified = time();
            $q->questiontext = ['text' => "<p>" . $questiontext . "</p>"];
            $q->questiontextformat = 1;
            if ($addidentifier == 1) {
                $q->name = "AI-created: " . $q->name; // Adds a "watermark" to the question
            }
            $created = \question_bank::get_qtype($qtype)->save_question($q, $q);

            $update = $DB->get_record('qbank_genai', ['id' => $genaiid]);
            $update->tries = $update->tries + 1;
            $update->success = 1;
            $update->datemodified = time();
            $DB->update_record('qbank_genai', $update);

            $createdquestions[] = $created;
        }

        return $createdquestions;
    }
}
