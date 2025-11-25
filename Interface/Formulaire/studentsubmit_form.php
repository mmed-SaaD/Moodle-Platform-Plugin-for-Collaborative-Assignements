<?php

require_once("$CFG->libdir/formslib.php");

class submit_student extends moodleform{

    public function definition(){
        global $CFG;
         
        //Form's elements creation
        $tform = $this->_form;
        $tform->addElement('filepicker','file','My work : ');
        $tform->addHelpButton('file','file','devoir');
        $tform->addRule('file','','required');
        $this->add_action_buttons();
    }
}