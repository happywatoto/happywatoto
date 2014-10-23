<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_facility {
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function residents__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
  
  function rel_all_residents__permissions(&$record){
    return $this->residents__permissions($record);
  }
  function rel_current_residents__permissions(&$record){
    return $this->residents__permissions($record);
  }
}
?>