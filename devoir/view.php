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
 * This file is the entry point to the assign module. All pages are rendered from here
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

Global $DB,$PAGE,$CFG,$USER;

$iduser = $USER->id;
$idcourse = $PAGE->course->id;
$current_time = new DateTime();
$current_time_string = $current_time->format('Y-m-d H:i:s');
$current_time_bigint = strtotime($current_time_string);
$course = $DB->get_record('course',array('id' => $idcourse),'*',MUST_EXIST);
require_login($course);
$context = context_course::instance($idcourse);

//We need first to check our student's role in order to decide which page we should direct him
//-----------Starting with student role ------------------------------------------------------------
//We need to connect to our database first
try{
    $conn = new PDO("mysql:host=localhost;dbname=moodle;port=3306;charset=utf8", 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
 }catch(Exception $exc){
   die("Cannot connect to database ! ");
 }

 //Tirer l' id du role 'student'
 $statement1 = $conn->prepare('SELECT id from mdl_role where shortname="student"');
 $statement1->execute();
 $result1 = $statement1->fetch();
 $roleid = $result1['id'];

 //Tirer l' id du role 'responsible'
 $statement01 = $conn->prepare('SELECT id from mdl_role where shortname="Responsible"');
 $statement01->execute();
 $result01 = $statement01->fetch();
 $roleid2 = $result01['id'];

 //Tirer l'id du role 'teacher'
 $statement02 = $conn->prepare('SELECT id from mdl_role where shortname="editingteacher"');
 $statement02->execute();
 $result02 = $statement02->fetch();
 $roleid3 = $result02['id'];

 $statement2 = $conn->prepare('SELECT roleid from mdl_role_assignments where userid="'.$iduser.'"');
 $statement2->execute();
 $result2 = $statement2->fetch();
 $roleuser = $result2['roleid'];

 //Interface student
 if($roleuser == $roleid ){
   $statement = $conn->prepare('SELECT course from mdl_course_modules where id="'.$_GET['id'].'"');
   $statement->execute();
   $result = $statement->fetch();
   $_SESSION['courseid'] = $result['course'];
   $_SESSION['moduleid'] = $_GET['id'];
   redirect($CFG->wwwroot.'/local/Interface/studentsub.php?id="'.$_GET['id'].'"');
 }

 //Interface responsible
 if($roleuser == $roleid2){
  $statement = $conn->prepare('SELECT course from mdl_course_modules where id="'.$_GET['id'].'"');
  $statement->execute();
  $result = $statement->fetch();
  $_SESSION['courseid'] = $result['course'];
  $_SESSION['moduleid'] = $_GET['id'];
  redirect($CFG->wwwroot.'/local/Interface/responsiblesub.php?id="'.$_GET['id'].'"');
 }

 //Interface teacher
 if($roleuser == $roleid3){
  $statement = $conn->prepare('SELECT course from mdl_course_modules where id="'.$_GET['id'].'"');
  $statement->execute();
  $result = $statement->fetch();
  $_SESSION['courseid'] = $result['course'];
  $_SESSION['moduleid'] = $_GET['id'];
  redirect($CFG->wwwroot.'/local/Interface/teacher.php?id="'.$_GET['id'].'"');
 }