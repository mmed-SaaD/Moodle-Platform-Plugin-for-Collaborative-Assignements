<?php

require_once(__DIR__. '/../../config.php');
require_once(__DIR__. '/Formulaire/studentsubmit_form.php');

// Global variables that does need to be initialised
/**
 * Prepare the standard module information for a new module instance.
 *
 * @param  stdClass $course  course object
 * @param  string $modulename  module name
 * @param  int $section section number
 * @return array module information about other required data
 * @since  Moodle 3.2
 */

Global $DB,$PAGE,$CFG,$USER;

if(!isset($_SESSION['courseid'])){
   redirect($CFG->wwwroot.'/my/');
}

$userid = $USER->id;
$idcourse = $_SESSION['courseid'];
$current_time = new DateTime();
$mod_id = $_SESSION['moduleid'];
$current_time_string = $current_time->format('Y-m-d H:i:s');
$current_time_bigint = strtotime($current_time_string);
$course = $DB->get_record('course',array('id' => $idcourse),'*',MUST_EXIST);
require_login($course);
$context = context_course::instance($idcourse);
$form = new submit_student();

//----------- Here we will try to get (The name of the responsible,group's name,teacher's name)-------------------------
try{
    $conn = new PDO("mysql:host=localhost;dbname=moodle;port=3306;charset=utf8", 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
 }catch(Exception $exc){
   die("Cannot connect to database ! ");
 }
 $statement = $conn->prepare('SELECT firstname,lastname from mdl_user join mdl_groups on mdl_user.id=mdl_groups.idnumber and mdl_groups.courseid="'.$idcourse.'" limit 1');
 $statement->execute();
 $result = $statement->fetch();
 $teacher_firstname = $result['firstname'];
 $teacher_lastname = strtoupper($result['lastname']);
 //----------------------------------------------------------------------------------------------//
 $statement1 = $conn->prepare('SELECT groupid from mdl_groups_members where userid="'.$userid.'"');
 $statement1->execute();
 $result1 = $statement1->fetch();
 $groupid = $result1['groupid'];
 $statement2 = $conn->prepare('SELECT name from mdl_groups where id="'.$groupid.'"');
 $statement2->execute();
 $result2 = $statement2->fetch();
 $groupname = $result2['name'];
 //----------------------------------------------------------------------------------------------//
    $statement4 = $conn->prepare('SELECT id from mdl_role where shortname="Responsible"');
    $statement4->execute();
    $result4 = $statement4->fetch();
    $roleid = $result4['id'];
    $statement5 = $conn->prepare('SELECT userid from mdl_role_assignments where roleid="'.$roleid.'"');
    $statement5->execute();
    $result5 = $statement5->fetchAll();
    $responsibles = array();
    foreach($result5 as $value){
       array_push($responsibles,$value['userid']);
    }
    $statement3 = $conn->prepare('SELECT userid from mdl_groups_members where groupid="'.$groupid.'"');
    $statement3->execute();
    $result3 = $statement3->fetchAll();
    foreach($result3 as $value){
       if(in_array($value['userid'],$responsibles)){
          $statement6 = $conn->prepare('SELECT firstname,lastname,id,email from mdl_user where id="'.$value['userid'].'"');
          $statement6->execute();
          $result6 = $statement6->fetch();
          $responsible_id = $result6['id'];
          $responsible_firstname = $result6['firstname'];
          $responsible_lastname = strtoupper($result6['lastname']);
          $responsible_email = $result6['email'];
       }
    }

    //Let's now get our CA infos
    $statement7 = $conn->prepare('SELECT * from mdl_devoir join mdl_course_modules on mdl_course_modules.instance=mdl_devoir.id and mdl_course_modules.id='.$mod_id.'');
    $statement7->execute();
    $result7 = $statement7->fetch();
    $dev_id = $result7['id'];
    $dev_name = $result7['name'];
    $dev_time = $result7['date_end'];
    $dev_desc = substr($result7['description'],0,-1);
    $dev_scale = substr($result7['bareme'],0,-1);
    $dev_file = $result7['fichier'];
    if($result7['type'] == "*"){
       $dev_type = 'All file types';
    }else{
       $dev_type = $result7['type'];
    }
    
    //Here we will control the data submittion of our form
    if($form->is_cancelled()){
      //TEST FUNCTION CANCEL
      redirect($CFG->wwwroot.'/course/view.php?id='.$_SESSION['courseid']);
   }else if($form_data = $form->get_data()){
      $data = new stdClass();
      $data->userfrom = $USER->id;
      $data->file = $form->get_new_filename('file');
      $data->userto = $responsible_id;
      $data->id_dev = $mod_id;
      $data->time_submitted = $current_time_bigint;
      
      //-----------Here we will get some of our file's informations--------------------
      $filename = $data->file;
      $fileExt = explode(".",$filename);
      $filelowExt = strtolower(end($fileExt));

      //Time of submittion
   if($dev_time-$current_time_bigint > 0){
      if($dev_type != 'All file types'){
      if(strpos($dev_type,$filelowExt)){
         $statement_supp = $conn->prepare('SELECT * from mdl_submit where userfrom ="'.$userid.'" and id_dev="'.$mod_id.'"');
         $statement_supp->execute();
         $submittions = $statement_supp->rowCount();
         if($submittions == 0){
         $DB->insert_record('submit',$data);
         $filenewLoc = 'Medias/'.$filename;
         $form->save_file('file',$filenewLoc);
         redirect($CFG->wwwroot.'/course/view.php?id=2','Your work has been submitted successfully !');
         }else{
            $statement_update = $conn->prepare('Update mdl_submit set file="'.$filename.'"');
            $statement_update->execute();
            $filenewLoc = 'Medias/'.$filename;
            $form->save_file('file',$filenewLoc);
            echo "<script>alert('Your assignment has been updated !'); </script>";
         }
      }else{
         redirect($CFG->wwwroot.'/local/Interface/studentsub.php?id="'.$mod_id.'"','Invalid type of document');
      }
   }else{
         $statement_supp = $conn->prepare('SELECT * from mdl_submit where userfrom ="'.$userid.'" and id_dev="'.$mod_id.'"');
         $statement_supp->execute();
         $submittions = $statement_supp->rowCount();
         if($submittions == 0){
         $DB->insert_record('submit',$data);
         $filenewLoc = 'Medias/'.$filename;
         $form->save_file('file',$filenewLoc);
         redirect($CFG->wwwroot.'/course/view.php?id=2','Your work has been submitted successfully !');
         }else{
            $statement_update = $conn->prepare('Update mdl_submit set file="'.$filename.'" where userfrom="'.$userid.'" and id_dev="'.$mod_id.'"');
            $statement_update->execute();
            $filenewLoc = 'Medias/'.$filename;
            $form->save_file('file',$filenewLoc);
            echo "<script>alert('Your assignment has been updated !'); </script>";
         }
      }
   }else{
      redirect($CFG->wwwroot.'/local/Interface/studentsub.php?id="'.$mod_id.'"','The time of submittion is closed, you can no longer submit your work.');
   }  
}

$PAGE->set_pagelayout('standard');
$PAGE->set_url("/local/Interface/studentsub.php");
$PAGE->set_context(\context_system::instance());
$strplural = get_string("modulenameplural", "devoir");
$PAGE->navbar->add($strplural);
$PAGE->set_title("Collaboratif assignment");
$PAGE->set_heading($course->fullname);

$form = new submit_student();
echo $OUTPUT->header();

//Let's create our corps module

echo "<h1><img src=../../mod/devoir/pix/icon.png style ='width : 30px;height : 30px;'> ".$dev_name." <br> <br>  </h1>";

echo "
<head>
  <style>
  body{
    width : 185vh;  
}
     td{
         padding : 30px 20px 20px 175px;
         font-size : 16px;
     }
     table{
        margin-top : 20px;
        margin-bottom : 40px; 
    }
    #nb{
       font-weight : bold;
       border-bottom : 5px double red;
       margin-left : 20px;
    }
    #homework{
       text-decoration : none;
       margin-left : 25px;
       font-size : 18px;
    }
    #homework:link{
       color : darkblue;
       border-bottom : 2px dotted darkblue;
    }
    #homework:hover{
       color : cadetblue; 
       border-bottom : 2px dotted cadetblue;
   }
   #pen{
      color : deepskyblue;
      margin-right : 5px;
      font-size : 18px;
   }
    #type{
       margin-top : 20px;
      color : red;
      font-size : 15px; 
   }
   #email{
      font-weight : normal;
      font-style : italic;
   }
    </style>
</head>
<body onload='chronometer();'>
<a href='Medias/".$dev_file."' id='homework' download><i class='fa fa-pencil' id='pen' aria-hidden='true'></i> Download : ".$dev_file."</a>
<table>
   <tr>
      <td>Name of teacher : </td>
      <td style='font-weight : bold;'>".$teacher_lastname." ".$teacher_firstname."</td>
   </tr>
   <tr>
   <td>Name of responsible : </td>
   <td style='font-weight : bold;'>".$responsible_lastname." ".$responsible_firstname." <element id='email'>( ".$responsible_email." )</element></td>
</tr>
<tr>
<td>Name of groupe : </td>
<td style='font-weight : bold;'>groupe ".$groupname."</tD>
</tr>
";
if($result7['description'] != 1){
echo "<tr>
   <td>Description : </td>
   <td style='font-weight : bold;'>".$dev_desc."</td>
</tr>";
}
if($result7['bareme'] != 1){
   echo "<tr>
      <td>Scale : </td>
      <td style='font-weight : bold;'>".$dev_scale."</td>
   </tr>";
   }
   echo"
<tr>
</tr>
<tr>
 <td>Time left : </td>
 <td id='time_left' style='font-weight : bold;'></td>
</tr>
</table>
<script>
var days = 0;
var hours = 0;
var minutes = 0;
var secondes = 0;
let current_time = new Date();
var current_date = Math.floor(current_time.getTime() / 1000);
var time_left_secs = ".$dev_time." - current_date;
days = parseInt(time_left_secs/86400);
hours = parseInt((time_left_secs%86400)/3600);
minutes = parseInt(((time_left_secs%86400)%3600)/60);
secondes = parseInt(((time_left_secs%86400)%3600)%60)
function timer(){
   secondes--;
   if(secondes == 0){
      if(minutes != 0){
      secondes = 59;
      minutes--;
      }else{
         secondes = 0;
         minutes = 0;
      }
   }
      if(minutes == 0){
       if(hours != 0){
       minutes = 59;
       hours--;
       }else{
          minutes = 0;
          hours = 0;
       }
      }
      if(hours == 0){
       if(days != 0){
       hours = 24;
       days --;
       }else{
       hours = 0;
       days = 0; 
      }
   }
   if((days == 0 && hours == 0 && minutes == 0 && secondes == 0) || time_left_secs<0){
      document.getElementById('time_left').textContent = 'Submittion closed';
      document.getElementById('time_left').style.color = 'Red';
      clearInterval(intervalChr);
   }else{
   document.getElementById('time_left').textContent = days+' Day(s) '+hours+' Hour(s) '+minutes+' Minute(s) '+secondes+' Seconde(s) ';
   document.getElementById('time_left').style.color = 'LimeGreen';
}
}
intervalChr = setInterval(timer,1000);
</script>
";

echo "<div>";

$form->display();

echo "
<element id='nb'>NB: This are the allowed type(s) of documents '<element id='type'>".$dev_type."</element>'</element>
</div>
</body>
";

echo $OUTPUT->footer();
   