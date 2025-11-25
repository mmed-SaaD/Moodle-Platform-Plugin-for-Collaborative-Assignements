<?php

require_once(__DIR__. '/../../config.php');
require_once(__DIR__. '/Formulaire/form.php');

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

$sectionnumber = $_SESSION['section'];
$group_name = 'A';
$groups = 0;
$iduser = $USER->id;
$idcourse = $_SESSION['idcours'];
$students = 0;
$current_time = new DateTime();
$current_time_string = $current_time->format('Y-m-d H:i:s');
$current_time_bigint = strtotime($current_time_string);
$course = $DB->get_record('course',array('id' => $idcourse),'*',MUST_EXIST);
require_login($course);
$context = context_course::instance($idcourse);

// Calling the form() class and defining how it must manipulate its data
$form = new form();

if($form->is_cancelled()){
    //TEST FUNCTION CANCEL
    redirect($CFG->wwwroot.'/my/?idcours= '.$idcourse);
 }else if($form_data = $form->get_data()){
     $data = new stdClass();
     $data->course = $idcourse;
     $data->name = $form_data->name;
     $data->type = $form_data->file_type;
     $data->description = implode(" ",$form_data->Description);
     $data->nombre_etudiants_par_groupe = $form_data->number_students;
     $data->fichier = $form->get_new_filename('file');
     $data->bareme = implode(" ",$form_data->Scale);
     $data->date_start = $form_data->date_start;
     $data->date_end = $form_data->date_end;
     $data->depot_devoir = $current_time_bigint;
     $data->id_enseignant = $iduser;
     
     //We will try here to get some of our file's informations: 
     $filename = $data->fichier;
     $fileExt = explode(".",$filename);
     $filelowExt = strtolower(end($fileExt));
     $filenewLoc = 'Medias/'.$filename;
     $form->save_file('file',$filenewLoc);
     
     //----------Create groups and add enrolled users to it-----------
     
     //Connecting manualing to the database
     try{
        $conn = new PDO("mysql:host=localhost;dbname=moodle;port=3306;charset=utf8", 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
     }catch(Exception $exc){
       die("Cannot connect to database ! ");
     }

     //We will begin a test in which we will delete every group existing
     $groups_delete = $conn->prepare('DELETE from mdl_groups');
     $groups_delete->execute();
     $groups_members_delete = $conn->prepare('DELETE from mdl_groups_members');
     $groups_members_delete->execute();

     //Before beggining let's get the last collaboratif assignment's id
     $statement_zero = $conn->prepare('SELECT id from mdl_devoir order by id desc limit 1 ');
     $statement_zero->execute();
     $result_zero = $statement_zero->fetch();
     $last_dev_id = $result_zero['id'];
     $current_id = $last_dev_id+1;

      //We will define here an array which will contains all students enrolled in this course
      $enrolled_users = array();
      $statement1 = $conn->prepare('SELECT * FROM mdl_user');
      $statement1->execute();
      $result = $statement1->fetchAll();

    //Here we must try a test to make sure that the groups contain only students, and students must be already enrolled in this course
    foreach($result as $student){
        if($student['id'] != $iduser && is_enrolled($context,$student['id'])){
            array_push($enrolled_users,$student['id']);
            $students ++;
        }
    } 
    
    //Here we will create an array that contains all groups names
    $statement2 = $conn->prepare('SELECT name FROM mdl_groups');
    $statement2->execute();
    $result2 = $statement2->fetchAll();
    $names_of_groups = array();
    foreach($result2 as $groups_name){
    array_push($names_of_groups,$groups_name);

        //Now we will start a test before creating our groups, this test will verify if there's altready a group with this name so that we wont have any deplicated names
    do{
        $group_name++;
    }while(in_array($group_name,$names_of_groups));
    }  

     if((($students)%($data->nombre_etudiants_par_groupe)) != 0){
         $number = (($students)/($data->nombre_etudiants_par_groupe));
         $groupes = number_format($number);
         for($i=0; $i<($groupes); $i++){
            $statement3 = $conn->prepare('INSERT into mdl_groups(name,courseid,idnumber,description,timecreated) values("'.$group_name.'","'.$idcourse.'","'.$iduser.'","Ceci est le groupe '.$group_name.'","'.$current_time_bigint.'")');
            $statement3->execute();
            //We will need this groups id
            $groups_ids = array();
            $statement4 = $conn->prepare('SELECT id FROM mdl_groups where idnumber="'.$iduser.'"');
            $statement4->execute();
            $result2 = $statement4->fetchAll();
            foreach($result2 as $value){
              array_push($groups_ids,$value['id']);
            }
            array_push($names_of_groups,$group_name);
            $group_name++;
        }
     }else if((($students)%($data->nombre_etudiants_par_groupe)) == 0){
        $groupes = (($students)/($data->nombre_etudiants_par_groupe));
        for($i=0; $i<($groupes); $i++){
            $statement3 = $conn->prepare('INSERT into mdl_groups(name,courseid,idnumber,description,timecreated) values("'.$group_name.'","'.$idcourse.'","'.$iduser.'","Ceci est le groupe '.$group_name.'","'.$current_time_bigint.'")');
            $statement3->execute();
            ////We will need this groups id
            $groups_ids = array();
            $statement4 = $conn->prepare('SELECT id FROM mdl_groups where idnumber="'.$iduser.'"');
            $statement4->execute();
            $result2 = $statement4->fetchAll();
            foreach($result2 as $value){
              array_push($groups_ids,$value['id']);
            }
            array_push($names_of_groups,$group_name);
            $group_name++;
        }
     }

     //Here we will try to add users to this groups that we have created before.
     $index = 0;
     for($i=0;$i<$students;$i++){
         //We need to hover all groupes until we have assigned every student enrolled in this course, that's why we must use a condition test that will initialize the index variable to 0  
         //mdl_groups_members is the name of the table that contains each student with each groupe he is part of
         if($index == $groupes){
             $index = 0;
             $statement5 = $conn->prepare('INSERT INTO mdl_groups_members(groupid,userid,timeadded) values("'.$groups_ids[$index].'","'.$enrolled_users[$i].'","'.$current_time_bigint.'")');
             $statement5->execute();
             $index++; 
         }else{
            $statement5 = $conn->prepare('INSERT INTO mdl_groups_members(groupid,userid,timeadded) values("'.$groups_ids[$index].'","'.$enrolled_users[$i].'","'.$current_time_bigint.'")');
            $statement5->execute();
            $index++;    
        }
     }
      //------------Creation of a new role and choosing randomly students from each groups to be responsibles-------------------------------------------------------------------------  
      //We need first to add our new role << responsible >>
        $statement0 = $conn->prepare('SELECT * from mdl_role');
        $statement0->execute();
        $lignes0 = $statement0->rowCount();
        $result01 = $lignes0 + 1;
        $statement11 = $conn->prepare('SELECT * from mdl_role where shortname="Responsible"');
        $statement11->execute();
        $lignes11 = $statement11->rowCount();
        if($lignes11 == 0){
            $statement12 = $conn->prepare('INSERT INTO mdl_role(shortname,sortorder,archetype) values("Responsible","'.$result01.'","student")');
            $statement12->execute();
        }
        $statement13 = $conn->prepare('SELECT id from mdl_role where shortname="Responsible"');
        $statement13->execute();
        $result13 = $statement13->fetch();
        $id_role = $result13['id'];
        $statementsupp = $conn->prepare('UPDATE mdl_role_assignments set roleid="5" where roleid="'.$id_role.'"');
        $statementsupp->execute();
        $statementroleviewed = $conn->prepare('SELECT * from mdl_role_allow_view where roleid="'.$id_role.'"');
        $statementroleviewed->execute();
        $ligneroleviewed = $statementroleviewed->rowCount();
        if($ligneroleviewed ==0){
        foreach($result as $enrolled){
          if($enrolled['id'] != $iduser && is_enrolled($context,$enrolled['id'])){
          $statementroleview = $conn->prepare('INSERT INTO mdl_role_allow_view(roleid,allowview) values("'.$id_role.'","'.$enrolled['id'].'")');
          $statementroleview->execute();
        }
      }
    }

        //We will put all students ids of a group in an array so that we choose one student from it randomly
        for($index2=0;$index2<$groupes;$index2++){
        $statement12 = $conn->prepare('SELECT userid from mdl_groups_members where groupid="'.$groups_ids[$index2].'"');
        $statement12->execute();
        $result12 = $statement12->fetchAll();
        $students_ofgroup = array();
        foreach($result12 as $student_infos){
          array_push($students_ofgroup,$student_infos['userid']);
        }
        //This is the responsible's id
        $array_key = array_rand($students_ofgroup);
        $responsible_id = $students_ofgroup[$array_key];
        $statement14 = $conn->prepare('UPDATE mdl_role_assignments set roleid="'.$id_role.'" WHERE userid="'.$responsible_id.'"');
        $statement14->execute();
        }
        $statementprepare = $conn->prepare('DELETE FROM mdl_role_capabilities where roleid="'.$id_role.'"');
        $statementprepare->execute();
        $statementtest = $conn->prepare('SELECT * from mdl_role_capabilities where roleid="'.$id_role.'"');
        $statementtest->execute();
        $capabilitiestest = $statementtest->rowCount();
        if($capabilitiestest <= 1){
        $statement = $conn->prepare('SELECT * from mdl_role_capabilities where roleid=5');
        $statement->execute();
        $this_result = $statement->fetchAll();
        foreach($this_result as $capabilities){
            $statement00 = $conn->prepare('INSERT INTO mdl_role_capabilities(contextid,roleid,capability,permission)values(1,"'.$id_role.'","'.$capabilities['capability'].'",1)');
            $statement00->execute();
      }
    }

 //Now we need to notify each user that a new collaboratif assignment has been uploaded
    $name = 'notification';
    $subject = 'new collaboratif assignment';
    $fullmessage = 'I have uploaded a new collaboratif assignment, you have been assigned to a group with other students, you must send your assignment to the resposible of your group!';
    $fullmessage_hash = hash('sha256',$fullmessage);
    $smallmessage = 'I have uploaded a new collaboratif assignment, go check !';
    $fullmessageformat = 1;
    $fullmessagehtml = '<p>There is work to be done !</p>';
    $notification = 1;
    
    //First we enable our conversation and we select a type for it
    $statement6 = $conn->prepare('INSERT INTO mdl_message_conversations(type,convhash,enabled,timecreated) values(1,"'.$fullmessage_hash.'",1,'.$current_time_bigint.')');
    $statement6->execute();
    $statement7 = $conn->prepare('SELECT id from mdl_message_conversations where timecreated="'.$current_time_bigint.'"');
    $statement7->execute();
    $conv_id = $statement7->fetch();
    $result_conv = $conv_id['id'];

      //-----------------------------Then we must define some more informations as the id of the user that will send this message
        //And now we will define that there is a new message sent by this user which informations are submitted
        $statement8 = $conn->prepare('INSERT INTO mdl_messages(useridfrom,conversationid,subject,fullmessage,fullmessageformat,fullmessagehtml,smallmessage,timecreated,fullmessagetrust) values("'.$iduser.'","'.$result_conv.'","'.$subject.'","'.$fullmessage.'","'.$fullmessageformat.'","'.$fullmessagehtml.'","'.$smallmessage.'","'.$current_time_bigint.'",0)');
        $statement8->execute();

    //IN order to send notification to some specefic type of users we will add a new test
    foreach($result as $student_enrolled){
       if($student_enrolled['id'] != $iduser && is_enrolled($context,$student_enrolled['id'])){       
        $userto = $student_enrolled['id'];

        //Finally, we will add two entries to this table so that we will define our two users (the one that will send,and the one that will receive) 
        $statement9 = $conn->prepare('INSERT INTO mdl_message_conversation_members(conversationid,userid,timecreated) values("'.$iduser.'","'.$iduser.'",'.$current_time_bigint.')'); 
        $statement9->execute();
        $statement10 = $conn->prepare('INSERT INTO mdl_message_conversation_members(conversationid,userid,timecreated) values("'.$result_conv.'","'.$userto.'",'.$current_time_bigint.')');
        $statement10->execute();
      }
    }

    if((($data->date_start)<($data->date_end)) && ($data->nombre_etudiants_par_groupe) < ($students) && ($data->nombre_etudiants_par_groupe != 0)){
      //---------Right here we will stock our collaboratif assignment and apply some changes to the database's tables in order to display it----------------------------------- 
      $DB->insert_record('devoir',$data);
      //First we should get our collaboratif assignment id
      $statement_iddev = $conn->prepare('SELECT * from mdl_devoir where depot_devoir="'.$current_time_bigint.'"');
      $statement_iddev->execute();
      $result_iddev = $statement_iddev->fetch();
      $iddev = $result_iddev['id'];
      //And then the id of our activity in the table mdl_modules
      $statementsection1 = $conn->prepare('SELECT * from mdl_modules where name="devoir"');
      $statementsection1->execute();
      $resultsection = $statementsection1->fetch();
      $idmod = $resultsection['id'];

      $statementsection2 = $conn->prepare('INSERT INTO mdl_course_modules(course,module,instance,section,added,idnumber) values("'.$idcourse.'","'.$idmod.'","'.$iddev.'","'.$sectionnumber.'","'.$current_time_bigint.'","")');
      $statementsection2->execute();
      //Now we need to get the current section of the section so that we wont lose any activity that are placed in this section
      $statementsequence = $conn->prepare('SELECT * from mdl_course_sections where section="'.$sectionnumber.'"');
      $statementsequence->execute();
      $sequence_result = $statementsequence->fetch();
      $sequence = $sequence_result['sequence'];
      //Also we need the id of the new activity so that we specify in which section it belongs
      $statement_id_course_mod = $conn->prepare('SELECT * from mdl_course_modules where instance="'.$iddev.'"');
      $statement_id_course_mod->execute();
      $result_course_mod = $statement_id_course_mod->fetch();
      $id_course_mod = $result_course_mod['id'];
      $statementsection5 = $conn->prepare('UPDATE mdl_course_sections set sequence="'.$sequence.','.$id_course_mod.'" where section="'.$sectionnumber.'"');
      $statementsection5->execute();
      redirect($CFG->wwwroot.'/course/view.php?id=2','Your collaboratif assignment has been created successfully ! ');
      }else{
      redirect($CFG->wwwroot.'/local/Interface/page.php','Your form has not been confirmed because of an error ! ');
      }
}
    //Page construction
$PAGE->set_pagelayout('standard');
$PAGE->set_url("/local/Interface/page.php?idcours=".$idcourse);
$PAGE->set_context(\context_system::instance());
$strplural = get_string("modulenameplural", "devoir");
$PAGE->navbar->add($strplural);
$PAGE->set_title("Editing a new collaboratif assignment");
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();


echo "<h1><img src=../../mod/devoir/pix/icon.png style ='width : 30px;height : 30px;'> Add a new collaboratif assignment <br> <br>  </h1>";


$form->display();


echo $OUTPUT->footer();