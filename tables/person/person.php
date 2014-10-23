<?php
class tables_person {

	function getTitle(&$record){
		return $record->val('per_name');
	}
	
	function titleColumn(){
		return 'per_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function rel_resident__permissions(&$record){
    $asdf = Dataface_converters_date::datetime_to_string("asdf");
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
}

?>