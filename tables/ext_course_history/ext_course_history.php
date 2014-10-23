<?php
class tables_ext_course_history {

	function getTitle(&$record){
		return $record->val('ech_person_name');
	}
	
	function titleColumn(){
		return 'ech_person_name';
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
  
  function ech_current_year__default(){
    return date("Y");
  }
  
  function updateEYOL($person_id, $course_id){
    $query = "SELECT ech_current_year, ech_history_year FROM ext_course_history WHERE ech_person_id=$person_id AND ech_course_id=$course_id ORDER BY ech_current_year DESC;";
    $res = df_query($query, null, true, true);
    if ( is_array($res) ){
      // due to the ordering, the very first entry yields the largest current year.
      $cur_year = $res[0][0];
      $hist_year = $res[0][1];
      $course = df_get_record('ext_course',array('ec_id'=>$course_id));
      $dura = $course->val('ec_duration');
      $date = $cur_year + $dura - $hist_year;
      if ($course->val('ec_start_month')>1) {
        // course starts at least in February, so it affects the next year as well
        $date = $date + 1;
      }
      df_query("UPDATE ext_education SET ee_expected_leave_year=$date WHERE ee_person_id=$person_id AND ee_course_id=$course_id;");
    } else {
      throw new Exception("Query '".$query."' for update of expected year of leaving failed. ", E_USER_NOTICE);
    }
  }
  
  function afterSave(&$record){
    //// update expected year of leaving
    $course_id = $record->val('ech_course_id');
    $person_id = $record->val('ech_person_id');
    $this->updateEYOL($person_id, $course_id);
  }
  
}

?>