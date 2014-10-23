<?php
class tables_employee {

	function getTitle(&$record){
		return $record->val('emp_name');
	}
	
	function titleColumn(){
		return 'emp_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
}

?>