<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $PAGE,$USER;
$idcourse = $PAGE->course->id;
$iduser = $USER->id;
$_SESSION['idcours'] = $idcourse;
$_SESSION['section'] = $_GET['section'];
redirect($CFG->wwwroot.'/local/interface/page.php');

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once(__DIR__. '/../../config.php');
require_once(__DIR__. '/../../mod/devoir/mod_form.php');
class mod_devoir_mod_form extends moodleform {

     function definition(){

     }

      function validation($data, $files) {
        return array();
    }
    }

