<?php
class tables_ext_course {

	function getTitle(&$record){
		return $record->val('ec_title_all');
	}
	
	function titleColumn(){
		return 'ec_title_all';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
         // if the user is null then nobody is logged in... no access.
         // This will force a login prompt.
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    } elseif ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function beforeUpdate(&$record){
    //// update expected year of leaving
    $oldrec = df_get_record('ext_course',array('ec_id'=>$record->val('ec_id')));
    $oldval = $oldrec->val('ec_duration');
    // check if the duration is changed
    $diff = $record->val('ec_duration') - $oldval;
    if ($diff != 0) {
      $course_id = $record->val('ec_id');
      $query = "SELECT ee_person_id, ee_expected_leave_year FROM ext_education WHERE ee_course_id=$course_id;";
      $res = df_query($query, null, true, true);
      if ( is_array($res) ){
        // these are all persons/external education records which are affected by the change
        // update the EYOL
        foreach ($res as $row){
          $newval = $row[1] + $diff;
          df_query("UPDATE ext_education SET ee_expected_leave_year=$newval WHERE ee_person_id=". $row[0] ." AND ee_course_id=$course_id;");
        }
      }
    }
  }
  
}

?>