<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_contact {
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
         // if the user is null then nobody is logged in... no access.
         // This will force a login prompt.
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
}
?>