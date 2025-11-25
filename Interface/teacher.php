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


$PAGE->set_pagelayout('standard');
$PAGE->set_url("/local/Interface/teacher.php");
$PAGE->set_context(\context_system::instance());
$strplural = get_string("modulenameplural", "devoir");
$PAGE->navbar->add($strplural);
$PAGE->set_title("Collaboratif assignment");
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

//----------- Here we will try to get (The name of the responsible,group's name,teacher's name)-------------------------
try{
    $conn = new PDO("mysql:host=localhost;dbname=moodle;port=3306;charset=utf8", 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
 }catch(Exception $exc){
   die("Cannot connect to database ! ");
 }

 //Let's get our CA infos
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
 
 //Now we need number of groups
 $statement1 = $conn->prepare('SELECT count(*) from mdl_groups');
 $statement1->execute();
 $result1 = $statement1->fetch();
 $number_of_groups = $result1['count(*)'];

 //Let's get the role id of role : 'Responsible'
 $statement2 = $conn->prepare('SELECT id from mdl_role where shortname="Responsible"');
 $statement2->execute();
 $result2 = $statement2->fetch();
 $responsibleroleid = $result2['id'];

 //Now we need to know the names of the responsibles
 $statement3 = $conn->prepare('select firstname,lastname,email,groupid from mdl_role_assignments join mdl_user on mdl_role_assignments.userid=mdl_user.id join mdl_groups_members on mdl_user.id=mdl_groups_members.userid where mdl_role_assignments.roleid="'.$responsibleroleid.'"');
 $statement3->execute();
 $result3 = $statement3->fetchAll();
echo "<h1><img src=../../mod/devoir/pix/icon.png style ='width : 30px;height : 30px;'> ".$dev_name." <br> <br>  </h1>";

echo "<style>
body{
    width : 185vh;  
}
     td{
         padding : 30px 20px 20px 150px;
         font-size : 16px;
     }
     table{
        margin-top : 20px;
        margin-bottom : 40px; 
    }
    .infos{
     font-weight : bold;
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
    #email{
    font-weight : normal;
    font-style : italic;
    }
    a{
        text-decoration : none;
        font-size : 18px;
    }
    a:hover{
        text-decoration : none;
        color : royaleblue;
    }
    #submitted{
      text-decoration : none;
      font-size : 16px;
   }
   #submitted:link{
      color : #5F9EA0;
   }
   #submitted:hover{
       color : #6495ED; 
       border-bottom : 1px solid  #6495ED;
   }
</style>
<body onload='chronometer();'>
<a href='Medias/".$dev_file."' id='homework' download><i class='fa fa-pencil' id='pen' aria-hidden='true'></i> Download : ".$dev_file."</a>
<div id='container'>
   <table>
     <tr>
        <td>Nubmer of groups : </td>
        <td class='infos'>".$number_of_groups."</td>
     </tr>
     <tr>
     <td>Responsibles : </td>
     <td class='infos'>";
     foreach($result3 as $value){
        $statement_group = $conn->prepare('SELECT name from mdl_groups where id="'.$value['groupid'].'"');
        $statement_group->execute();
        $result_group = $statement_group->fetch();
        $group_name = $result_group['name'];
        $firstname = $value['firstname'];
        $lastname = strtoupper($value['lastname']);
        $email = $value['email'];
        echo "<element>".$lastname." ".$firstname." <element id='email'>( ".$email." )</element> : group ".$group_name."</element><br>";
     }
     echo"</td>
  </tr>
       <tr>
        <td>Submitted works : </td>
        <td>";
        $statement4 = $conn->prepare('SELECT file,firstname,lastname from mdl_submit join mdl_user on mdl_submit.userfrom=mdl_user.id where userto="'.$userid.'" and id_dev="'.$_SESSION['moduleid'].'"');
        $statement4->execute();
        $submitted_works = $statement4->rowCount();
        $result4 = $statement4->fetchAll();
        if($submitted_works != 0){
         foreach($result4 as $value){
            $lastname = strtoupper($value['lastname']);
            echo "<a href='Medias/".$value['file']."' id='submitted' download><i class='fa fa-handshake-o' aria-hidden='true' id='collab'></i> ".$value['file']."</a> <element id='sent'> -submitted by : ".$lastname." ".$value['firstname']."-</element><br>";
         }
        }else{
         echo " - No works submitted yet ! -";
        }
        echo"</td>
     </tr>
     <tr>";
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
echo "     
<tr>
<td>Time left : </td>
<td id='time_left' class='infos'></td>
</tr>
<tr>
<td><a href='/course/view.php?id=2' ><i class='fa fa-angle-double-left' aria-hidden='true'> </i> Previous</a></td>
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
</div>
</body>
";
echo $OUTPUT->footer();
 ?>