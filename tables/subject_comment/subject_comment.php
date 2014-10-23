<?php
class tables_subject_comment {
  
  function getTitle(&$record){
		return $record->val('sc_person_name');
	}
	
	function titleColumn(){
		return 'sc_person_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)) {
        if (my_static_funcs::UserIsClassTeacher($user,null,$record->val('sc_subject_id'))) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        // hack: allow edit for all, otherwise class teachers can't create new comments via the relation in table subject
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