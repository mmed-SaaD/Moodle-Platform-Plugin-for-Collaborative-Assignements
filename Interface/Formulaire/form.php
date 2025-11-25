<?php

require_once("$CFG->libdir/formslib.php");


class form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $current_time = new DateTime();
        $current_time_string = $current_time->format('Y-m-d H:i:s');
        $current_time_bigint = strtotime($current_time_string);
        $time_after_oneweek = $current_time_bigint + 604800;
        $buttons = array();
        
        $tform = $this->_form; // Don't forget the underscore! 
        
        $tform->addElement('header','General informations about collaboratif assignment','General');
        $tform->addElement('text','name',"Name",'size=40px',); // Add elements to your form.
        $tform->setType('name', PARAM_NOTAGS);                   // Set type of element.
        $tform->addElement('editor','Description','Description');
        $tform->addHelpButton('Description','Description','devoir');
        $tform->setType('Description',PARAM_RAW);
        $tform->setDefault('name', '');        // Default value.
        $tform->addElement('text','number_students',"Number of students per group",'size=5px');
        $tform->addHelpButton('number_students','number_students','devoir');
        $tform->setType('number_students', PARAM_INT); 
        $tform->setDefault('number_students',2);
        $tform->addElement('filepicker','file','Drag your file here',null);
        $tform->setType('file',PARAM_CLEAN);
        $tform->addElement('filetypes','file_type','Allowed type files');
        $tform->addHelpButton('file_type','file_type','devoir');
        $tform->addElement('editor','Scale','Scale');
        $tform->addHelpButton('Scale','Scale','devoir');
        $tform->setType('Scale',PARAM_NOTAGS);
        $tform->addElement('header','Availability of collaboratif assignment','Availability');
        $tform->addElement('date_time_selector','date_start','Start');
        $tform->addElement('date_time_selector','date_end',"End");
        $tform->setDefault('date_end',$time_after_oneweek);
        $tform->addRule('name','','required');
        $tform->addRule('Description','','required');
        $tform->addRule('number_students','','required');
        $tform->addRule('file','','required');
        $tform->addRule('file_type','','required');
        $tform->addRule('Scale','','required');
        $tform->addRule('date_start','','required');
        $tform->addRule('date_end','','required');
        $this->add_action_buttons();

    }
    }