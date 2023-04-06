<?php

namespace mod_cluequiz\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class mod_cluequiz_question_form extends moodleform {

    private $cmid;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        $this->cmid = $customdata['cmid'];
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('questionheader', 'mod_cluequiz'));

        // Add the question text field.
        $mform->addElement('textarea', 'questiontext', get_string('questiontext', 'mod_cluequiz'));
        $mform->setType('questiontext', PARAM_TEXT);
        $mform->addRule('questiontext', null, 'required', null, 'client');

        // Add the answer text field.
        $mform->addElement('textarea', 'answertext', get_string('answertext', 'mod_cluequiz'));
        $mform->setType('answertext', PARAM_TEXT);
        $mform->addRule('answertext', null, 'required', null, 'client');

        // Add a submit button to submit the form.
        $mform->addElement('html',
            '<div class="form-group">
                  <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-primary" onclick="submitFormData()">'.
                            get_string('save', 'mod_cluequiz').
                        '</button>
                  </div>
            </div>');








        // Add the clue fields.
        $mform->addElement('header', 'clueheader', get_string('clueheader', 'mod_cluequiz'));

        // Add the first clue field.
        $mform->addElement('textarea', 'clue[0]', get_string('clue', 'mod_cluequiz') . ' 1');
        $mform->setType('clue[0]', PARAM_TEXT);

        // Add a button to allow for adding more clues.
        $mform->addElement('html', '<div id="clues-container"></div>');
        $mform->addElement('html', '<button id="add-clue" type="button" class="btn btn-success" 
        style="margin-bottom: 10px;">'.get_string('addmoreclues', 'mod_cluequiz').'</button>');

    }

    function submitForm($data, $options) {
        global $DB;

        $question = new stdClass();
        $question->activity_id = $this->cmid;
        $question->question_text = $data['questiontext'];
        $question->answer_text = $data['answertext'];

        $question->id = $DB->insert_record('cluequiz_question', $question);

        $answer = new stdClass();
        $answer->question_id = $question->id;
        $answer->answertext = $question->answertext;

        $DB->insert_record('cluequiz_answer', $answer);

        return true;
    }

    public function process_data($data) {
        global $DB;

        $question = new stdClass();
        $question->activity_id = $this->cmid;
        $question->question_text = $data['questiontext'];
        $question->answer_text = $data['answertext'];

        $questionid = $DB->insert_record('cluequiz_questions', $question);

        $answer = new stdClass();
        $answer->answertext = $data['answertext'];
        $answer->question_id = $questionid;

        $DB->insert_record('cluequiz_answers', $answer);
    }

    function submit($data, $files) {
        // Process the form data and save it to the database.
        // Redirect the user to the next page.
        redirect($this->_customdata['returnurl']);
    }


}

