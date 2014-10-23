<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_exam_grade {

  function getTitle(&$record){
		return $record->val('eg_title_all');
	}
	
	function titleColumn(){
		return 'eg_title_all';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)) {
        $exam = df_get_record('exam',array('exa_id'=>$record->val('eg_exam_id')));
        if (my_static_funcs::UserIsClassTeacher($user,null,$exam->val('exa_subject_id'))) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        // hack: allow edit for all, otherwise class teachers can't create new exams via the relation in table subject
        // $perms['edit'] = 1;
        $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
      }
      $perms['new'] = 0;
      // $perms['DataGrid:view_grid'] = 1;
      // $perms['DataGrid:update'] = 1;
      // $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
      return $perms;
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function no_edit_permissions(&$record) {
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('NO EDIT');
      return $perms;
    }
    return null;
  }
  
  function eg_exam_id__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
  
  function eg_person_id__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
  
  function beforeSave(&$record) {
    if (!$record->val('eg_points')) {
      // no points set, no update of final grade required
      return;
    }
    // check if a final grade entry exists
    $exam = df_get_record('exam',array('exa_id'=>$record->val('eg_exam_id')));
    if (!$exam) {
      return PEAR::raiseError('You are trying to save a grade for an exam which does not exist!');
    }
    $subject_id = $exam->val('exa_subject_id');
    if (!$subject_id) {
      return PEAR::raiseError('No subject is set for this exam.');
    }
    $trimester = $exam->val('exa_trimester');
    if (!$trimester) {
      return PEAR::raiseError('No trimester is set for this exam.');
    }
    $person_id = $record->val('eg_person_id');
    if (!$person_id) {
      return PEAR::raiseError("No pupil is set for this exam grade.");
    }
    $fg = df_get_record('final_grade',array('fg_person_id'=>$person_id,'fg_subject_id'=>$subject_id,'fg_trimester'=>$trimester));
    if (!$fg) {
      // no final grade record found, let's create it.
      df_query("INSERT INTO final_grade (fg_subject_id, fg_person_id, fg_trimester) VALUES ($subject_id, $person_id, $trimester);");
    }
  }
  
  function afterSave(&$record) {
    if (!$record->val('eg_points')) {
      // no points set, no update of final grade required
      return;
    }
    
    $exam_id = $record->val('eg_exam_id');
    $exam = df_get_record('exam',array('exa_id'=>$exam_id));
    $subject_id = $exam->val('exa_subject_id');
    $trimester = $exam->val('exa_trimester');
    $person_id = $record->val('eg_person_id');
    $all_grades = df_get_records_array('exam_grade',array('eg_person_id'=>$person_id));
    if (count($all_grades)<1) {
      return PEAR::raiseError("Trying to update final grade, but not fitting exam grades found! person $person_id, exam $exam_id");
    }
    $sum = 0;
    foreach ($all_grades as $grade) {
      if ($grade->getValue('eg_grade')) {
        $sum = $sum + $grade->getValue('eg_grade');
      }
    }
    $sum = $sum / count($all_grades);
    df_query("UPDATE final_grade SET fg_mean_grade=$sum WHERE fg_person_id=$person_id AND fg_subject_id=$subject_id AND fg_trimester=$trimester;");
  }
}
?>