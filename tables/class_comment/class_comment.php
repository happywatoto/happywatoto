<?php
class tables_class_comment {
  
  function getTitle(&$record){
		return $record->val('cc_person_name').", ".$record->val('cc_class_name');
	}
	
	function titleColumn(){
		return 'cc_person_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)) {
        if (my_static_funcs::UserIsClassTeacher($user,$record->val('cc_class_id'),null)) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        // hack: allow edit for all, otherwise class teachers can't create new comments via the relation in table class
        $perms['edit'] = 1;
      }
      return $perms;
    } elseif ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
}

?>