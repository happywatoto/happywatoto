<?php

class my_static_funcs {
public static function UserIsClassTeacher(&$user, $classId, $subjectId) {
  $ret = false;
  $personId = $user->val('use_person_id');
  if (isset($personId)) {
    if (isset($classId)) {
      $class = df_get_record('class', array('cla_id'=>$classId));
    } else {
      $subject = df_get_record('subject', array('sub_id'=>$subjectId));
      $class = df_get_record('class', array('cla_id'=>$subject->val('sub_class_id')));
    }
    $ret = ($personId==$class->val('cla_person_id'));
  }
  return $ret;
}
}
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class conf_ApplicationDelegate {
  /**
   * Returns permissions array.  This method is called every time an action is 
   * performed to make sure that the user has permission to perform the action.
   * @param record A Dataface_Record object (may be null) against which we check
   *               permissions.
   * @see Dataface_PermissionsTool
   * @see Dataface_AuthenticationTool
   */
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::NO_ACCESS();
         // if the user is null then nobody is logged in... no access.
         // This will force a login prompt.
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function block__before_left_column(){
    echo "<a href=\"".$ENV.DATAFACE_SITE_URL."\"><img src=\"".$ENV.DATAFACE_SITE_URL."/images/logo_2011.jpg\" alt=\"Happy Watoto Homes and Schools\" style=\"max-width:250px\" width=\"100%\"/></a>";
  }
  
  function getNavItem($key, $label){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    
    if (Dataface_Table::tableExists($key)) {
      $perms = Dataface_PermissionsTool::getPermissions(df_get_table_info($key));
      if ($perms['list']) {
        throw new Exception("Use default rendering");
      } else {
        return null;
      }
    }
  }
}
?>