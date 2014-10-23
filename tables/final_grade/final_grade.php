<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_final_grade {
  
  function getTitle(&$record){
		return $record->val('fg_person_name');
	}
	
	function titleColumn(){
		return 'fg_person_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)) {
        if (my_static_funcs::UserIsClassTeacher($user,null,$record->val('fg_subject_id'))) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
          $perms["new"] = 0;
        }
      }
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
  
  function fg_subject_id__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
  
  function fg_person_id__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
  
  function fg_trimester__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
  
  function fg_mean_grade__permissions(&$record) {
    return $this->no_edit_permissions($record);
  }
}
?>