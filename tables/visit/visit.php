<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_visit {
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
}
?>