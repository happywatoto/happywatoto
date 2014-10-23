<?php
/**
 * A delegate class for the entire application to handle custom handling of 
 * some functions such as permissions and preferences.
 */
class tables_subject {
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record) and my_static_funcs::UserIsClassTeacher($user,$record->val('sub_class_id'),null)) {
        $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
      }
      return $perms;
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function rel_subject_comment__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)){
        if (my_static_funcs::UserIsClassTeacher($user,$record->val('sub_class_id'),null)) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        $perms['add existing related record'] = 1;
      }
      
      return $perms;
    } elseif ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
  
  function rel_exams__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)){
        if (my_static_funcs::UserIsClassTeacher($user,$record->val('sub_class_id'),null)) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        $perms['add existing related record'] = 1;
      }
      
      return $perms;
    } elseif ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
  
  function subject_comment__getAddableValues(&$record, $filter=array()){
    $options = array();
    $cla_id = $record->val('sub_class_id');
    $query = "SELECT per_id, concat_ws(' ', concat_ws(' ', per_name_first, per_name_last),concat('(', per_id, ')'))
                FROM person INNER JOIN class_member ON per_id=cm_person_id
                WHERE cm_class_id=$cla_id 
                ORDER BY per_name_first, per_name_last";
    
    $res = df_query($query, null, true, true);
    if ( is_array($res) ){
      foreach ($res as $row){
        $valuekey = $row[0];
        $valuevalue = count($row)>1 ? $row[1] : $row[0];
        $options['pup_person_id='.$valuekey] = $valuevalue;
      }
    } else {
      throw new Exception("Query '".$query."' for addable values failed. ", E_USER_NOTICE);
    }
    
    return $options;
  }
}
?>