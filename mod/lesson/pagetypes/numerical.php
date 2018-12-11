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
 * Numerical
 *
 * @package mod_lesson
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

/** Numerical question type */
define("LESSON_PAGE_NUMERICAL",     "8");

class lesson_page_type_numerical extends lesson_page {

    protected $type = lesson_page::TYPE_QUESTION;
    protected $typeidstring = 'numerical';
    protected $typeid = LESSON_PAGE_NUMERICAL;
    protected $string = null;

    public function get_typeid() {
        return $this->typeid;
    }
    public function get_typestring() {
        if ($this->string===null) {
            $this->string = get_string($this->typeidstring, 'lesson');
        }
        return $this->string;
    }
    public function get_idstring() {
        return $this->typeidstring;
    }
    public function display($renderer, $attempt) {
        global $USER, $CFG, $PAGE;
        $mform = new lesson_display_answer_form_numerical($CFG->wwwroot . '/mod/lesson/continue.php',
            array('contents' => $this->get_contents(), 'lessonid' => $this->lesson->id));
        $data = new stdClass;
        $data->id = $PAGE->cm->id;
        $data->pageid = $this->properties->id;
        if (isset($USER->modattempts[$this->lesson->id])) {
            $data->answer = s($attempt->useranswer);
        }
        $mform->set_data($data);

        // Trigger an event question viewed.
        $eventparams = array(
            'context' => context_module::instance($PAGE->cm->id),
            'objectid' => $this->properties->id,
            'other' => array(
                    'pagetype' => $this->get_typestring()
                )
            );

        $event = \mod_lesson\event\question_viewed::create($eventparams);
        $event->trigger();
        return $mform->display();
    }
    public function check_answer() {
        global $CFG;
        $result = parent::check_answer();

        $mform = new lesson_display_answer_form_numerical($CFG->wwwroot.'/mod/lesson/continue.php',
            array('contents' => $this->get_contents()));
        $data = $mform->get_data();
        require_sesskey();

        $formattextdefoptions = new stdClass();
        $formattextdefoptions->noclean = true;
        $formattextdefoptions->para = false;

        // set defaults
        $result->response = '';
        $result->newpageid = 0;

        if (!isset($data->answer) || !is_numeric($data->answer)) {
            $result->noanswer = true;
            return $result;
        } else {
            $result->useranswer = format_float($data->answer, strlen($data->answer), true, true);
             // Set this to the 'proper format' as it overrides.
             // the useranswer in record_attempt before saving to the db.
            $result->userresponse = $data->answer;
        }
        $result->studentanswer = $result->useranswer;
        $answers = $this->get_answers();
        foreach ($answers as $answer) {
            $answer = parent::rewrite_answers_urls($answer);
            if (strpos($answer->answer, ':')) {
                // there's a pairs of values
                list($min, $max) = explode(':', $answer->answer);
                $minimum = (float) $min;
                $maximum = (float) $max;
            } else {
                // there's only one value
                $minimum = (float) $answer->answer;
                $maximum = $minimum;
            }
            if (($data->answer >= $minimum) && ($data->answer <= $maximum)) {
                $result->newpageid = $answer->jumpto;
                $result->response = format_text($answer->response, $answer->responseformat, $formattextdefoptions);
                if ($this->lesson->jumpto_is_correct($this->properties->id, $result->newpageid)) {
                    $result->correctanswer = true;
                }
                if ($this->lesson->custom) {
                    if ($answer->score > 0) {
                        $result->correctanswer = true;
                    } else {
                        $result->correctanswer = false;
                    }
                }
                $result->answerid = $answer->id;
                return $result;
            }
        }
        return $result;
    }

    public function display_answers(html_table $table) {
        $answers = $this->get_answers();
        $options = new stdClass;
        $options->noclean = true;
        $options->para = false;
        $i = 1;
        foreach ($answers as $answer) {
            $answer = parent::rewrite_answers_urls($answer, false);
            $cells = array();
            if ($this->lesson->custom && $answer->score > 0) {
                // if the score is > 0, then it is correct
                $cells[] = '<label class="correct">' . get_string('answer', 'lesson') . ' ' . $i . '</label>:';
            } else if ($this->lesson->custom) {
                $cells[] = '<label>' . get_string('answer', 'lesson') . ' ' . $i . '</label>:';
            } else if ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto)) {
                // underline correct answers
                $cells[] = '<span class="correct">' . get_string('answer', 'lesson') . ' ' . $i . '</span>:' . "\n";
            } else {
                $cells[] = '<label class="correct">' . get_string('answer', 'lesson') . ' ' . $i . '</label>:';
            }
            $formattedanswer = lesson_format_numeric_value($answer->answer);
            $cells[] = format_text($formattedanswer, $answer->answerformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = '<label>' . get_string('response', 'lesson') . ' ' . $i . '</label>:';
            $cells[] = format_text($answer->response, $answer->responseformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = '<label>' . get_string('score', 'lesson') . '</label>:';
            $cells[] = $answer->score;
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = '<label>' . get_string('jump', 'lesson') . '</label>:';
            $cells[] = $this->get_jump_name($answer->jumpto);
            $table->data[] = new html_table_row($cells);
            if ($i === 1){
                $table->data[count($table->data)-1]->cells[0]->style = 'width:20%;';
            }
            $i++;
        }
        return $table;
    }
    public function stats(array &$pagestats, $tries) {
        if(count($tries) > $this->lesson->maxattempts) { // if there are more tries than the max that is allowed, grab the last "legal" attempt
            $temp = $tries[$this->lesson->maxattempts - 1];
        } else {
            // else, user attempted the question less than the max, so grab the last one
            $temp = end($tries);
        }
        if (isset($pagestats[$temp->pageid][$temp->useranswer])) {
            $pagestats[$temp->pageid][$temp->useranswer]++;
        } else {
            $pagestats[$temp->pageid][$temp->useranswer] = 1;
        }
        if (isset($pagestats[$temp->pageid]["total"])) {
            $pagestats[$temp->pageid]["total"]++;
        } else {
            $pagestats[$temp->pageid]["total"] = 1;
        }
        return true;
    }

    public function report_answers($answerpage, $answerdata, $useranswer, $pagestats, &$i, &$n) {
        $answers = $this->get_answers();
        $formattextdefoptions = new stdClass;
        $formattextdefoptions->para = false;  //I'll use it widely in this page
        foreach ($answers as $answer) {
            if ($useranswer == null && $i == 0) {
                // I have the $i == 0 because it is easier to blast through it all at once.
                if (isset($pagestats[$this->properties->id])) {
                    $stats = $pagestats[$this->properties->id];
                    $total = $stats["total"];
                    unset($stats["total"]);
                    foreach ($stats as $valentered => $ntimes) {
                        $data = '<input class="form-control" type="text" size="50" ' .
                                'disabled="disabled" readonly="readonly" value="'.
                                s(format_float($valentered, strlen($valentered), true, true)).'" />';
                        $percent = $ntimes / $total * 100;
                        $percent = round($percent, 2);
                        $percent .= "% ".get_string("enteredthis", "lesson");
                        $answerdata->answers[] = array($data, $percent);
                    }
                } else {
                    $answerdata->answers[] = array(get_string("nooneansweredthisquestion", "lesson"), " ");
                }
                $i++;
            } else if ($useranswer != null && ($answer->id == $useranswer->answerid || ($answer == end($answers) &&
                    empty($answerdata->answers)))) {
                // Get in here when the user answered or for the last answer.
                $data = '<input class="form-control" type="text" size="50" ' .
                        'disabled="disabled" readonly="readonly" value="'.
                        s(format_float($useranswer->useranswer, strlen($useranswer->useranswer), true, true)).'">';
                if (isset($pagestats[$this->properties->id][$useranswer->useranswer])) {
                    $percent = $pagestats[$this->properties->id][$useranswer->useranswer] / $pagestats[$this->properties->id]["total"] * 100;
                    $percent = round($percent, 2);
                    $percent .= "% ".get_string("enteredthis", "lesson");
                } else {
                    $percent = get_string("nooneenteredthis", "lesson");
                }
                $answerdata->answers[] = array($data, $percent);

                if ($answer->id == $useranswer->answerid) {
                    if ($answer->response == null) {
                        if ($useranswer->correct) {
                            $answerdata->response = get_string("thatsthecorrectanswer", "lesson");
                        } else {
                            $answerdata->response = get_string("thatsthewronganswer", "lesson");
                        }
                    } else {
                        $answerdata->response = $answer->response;
                    }
                    if ($this->lesson->custom) {
                        $answerdata->score = get_string("pointsearned", "lesson").": ".$answer->score;
                    } elseif ($useranswer->correct) {
                        $answerdata->score = get_string("receivedcredit", "lesson");
                    } else {
                        $answerdata->score = get_string("didnotreceivecredit", "lesson");
                    }
                } else {
                    $answerdata->response = get_string("thatsthewronganswer", "lesson");
                    if ($this->lesson->custom) {
                        $answerdata->score = get_string("pointsearned", "lesson").": 0";
                    } else {
                        $answerdata->score = get_string("didnotreceivecredit", "lesson");
                    }
                }
            }
            $answerpage->answerdata = $answerdata;
        }
        return $answerpage;
    }
}

class lesson_add_page_form_numerical extends lesson_add_page_form_base {

    public $qtype = 'numerical';
    public $qtypestring = 'numerical';
    protected $answerformat = '';
    protected $responseformat = LESSON_ANSWER_HTML;

    public function custom_definition() {
        for ($i = 0; $i < $this->_customdata['lesson']->maxanswers; $i++) {
            $this->_form->addElement('header', 'answertitle'.$i, get_string('answer').' '.($i+1));
            $this->add_answer($i, null, ($i < 1));
            $this->add_response($i);
            $this->add_jumpto($i, null, ($i == 0 ? LESSON_NEXTPAGE : LESSON_THISPAGE));
            $this->add_score($i, null, ($i===0)?1:0);
        }
    }

    /**
     * We call get data when storing the data into the db. Override to format the floats properly
     *
     * @return object|void
     */
    public function get_data() {
        $data = parent::get_data();

        if (!empty($data->answer_editor)) {
            foreach ($data->answer_editor as $key => $answer) {
                $data->answer_editor[$key] = lesson_unformat_numeric_value($answer);
            }
        }

        return $data;
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data with formatted numbers
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_submitted_data() {
        $data = parent::get_submitted_data();

        if (!empty($data->answer_editor)) {
            foreach ($data->answer_editor as $key => $answer) {
                $data->answer_editor[$key] = lesson_unformat_numeric_value($answer);
            }
        }

        return $data;
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form) after formatting numbers
     *
     *
     * @param stdClass|array $defaults object or array of default values
     */
    public function set_data($defaults) {
        if (is_object($defaults)) {
            $defaults = (array) $defaults;
        }

        $editor = 'answer_editor';
        foreach ($defaults as $key => $answer) {
            if (substr($key, 0, strlen($editor)) == $editor) {
                $defaults[$key] = lesson_format_numeric_value($answer);
            }
        }

        parent::set_data($defaults);
    }
}

class lesson_display_answer_form_numerical extends moodleform {

    public function definition() {
        global $USER, $OUTPUT;
        $mform = $this->_form;
        $contents = $this->_customdata['contents'];

        // Disable shortforms.
        $mform->setDisableShortforms();

        $mform->addElement('header', 'pageheader');

        $mform->addElement('html', $OUTPUT->container($contents, 'contents'));

        $hasattempt = false;
        $attrs = array('size'=>'50', 'maxlength'=>'200');
        if (isset($this->_customdata['lessonid'])) {
            $lessonid = $this->_customdata['lessonid'];
            if (isset($USER->modattempts[$lessonid]->useranswer)) {
                $attrs['readonly'] = 'readonly';
                $hasattempt = true;
            }
        }
        $options = new stdClass;
        $options->para = false;
        $options->noclean = true;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        $mform->addElement('float', 'answer', get_string('youranswer', 'lesson'), $attrs);
        $mform->setType('answer', PARAM_LOCALISEDFLOAT);

        if ($hasattempt) {
            $this->add_action_buttons(null, get_string("nextpage", "lesson"));
        } else {
            $this->add_action_buttons(null, get_string("submit", "lesson"));
        }
    }

    /**
     * We call get data when storing the data into the db. Override to format the floats properly
     *
     * @return object|void
     */
    public function get_data() {
        $data = parent::get_data();

        if (!empty($data->answer)) {
            $data->answer = lesson_unformat_numeric_value($data->answer);
        }

        return $data;
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form) after formatting numbers
     *
     *
     * @param stdClass|array $data object or array of default values
     */
    public function set_data($data) {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!empty($data['answer'])) {
            $data['answer'] = lesson_format_numeric_value($data['answer']);
        }

        parent::set_data($data);
    }

    /**
     * Return submitted data without validation or NULL if there is no submitted data.
     * note: $slashed param removed
     *
     * @return object submitted data; NULL if not submitted
     */
    public function get_submitted_data() {
        $data = parent::get_submitted_data();

        if (!empty($data->answer)) {
            $data->answer = lesson_unformat_numeric_value($data->answer);
        }
        return $data;
    }
}
