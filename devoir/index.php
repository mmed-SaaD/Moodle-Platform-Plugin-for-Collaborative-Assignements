<?php

require_once(__DIR__. '/../../config.php');
//require_once($CFG->dirroot.'/mod/devoir/locallib.php');

redirect($CFG->wwwroot.'/my/courses.php');
$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course',array('id' => $id) , '*', MUST_EXIST);
$context = context_course::instance($course->id);
//$strplural = get_string("modulenameplural","Collaborative assignment");
require_login($course);
$PAGE->set_url('/mod/devoir/index.php', array('id' => $id));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
//Used to start Typing our header
global $DB;
/*echo $OUTPUT->header();
if($form->is_cancelled()){
   echo "gg";
}else if($form_data = $form->get_data()){
    //the var_dump(var,var) is used to display informations about variables
    var_dump($form_data);
    //die(message) die is an equal as exit() and it displays the message before ending execution
    die();/*
    //l'object data doit etre suivi du véritable field(le vrais nom du field existant dans la table)
    $data = new stdClass();
    $data->name = $form_data->titre;
    $data->description = $fomr_data->description;
    $data->number_students = $form_data->nombre_etudiants_par_groupe;
    $data->file = $form_data->fichier;
    $data->file_type = $fomr_data->type;
    $data->scale = $form_data->bareme;
    $data->date_start = $form_data->date_start;
    $data->date_end = $form_data->date_end;
    
    $DB->insert_record('mdl_devoir',$data);
}
$form->display();*/
echo $OUTPUT->footer();