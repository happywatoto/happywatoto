<?php
class tables_ext_education {

	function getTitle(&$record){
		return $record->val('ee_person_name');
	}
	
	function titleColumn(){
		return 'ee_person_name';
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
  
  function afterInsert(&$record){
    //// set expected year of leaving for the first time
    $date = $record->val('ee_join_date');
    $course = df_get_record('ext_course',array('ec_id'=>$record->val('ee_course_id')));
    $dura = $course->val('ec_duration');
    // add the duration
    $date = $date['year'] + $dura;
    if ($course->val('ec_start_month')==1) {
        // course starts in January, so it doesn't affect the next year
        $date = $date - 1;
      }
    $course_id = $record->val('ee_course_id');
    $person_id = $record->val('ee_person_id');
    df_query("UPDATE ext_education SET ee_expected_leave_year=$date WHERE ee_person_id=$person_id AND ee_course_id=$course_id;");
  }
  
}

?>