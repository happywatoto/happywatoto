<?php
class tables_pupil {

	function getTitle(&$record){
		return $record->val('pup_name');
	}
	
	function titleColumn(){
		return 'pup_name';
	}
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function rel_class_comment__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
  
  function rel_subject_comment__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO RELATIONS');
    }
    return null;
  }
  
  function pup_comment__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'MATRON') {
      return Dataface_PermissionsTool::getRolePermissions('NO READ');
    }
    return null;
  }
  
  function class_comment__getAddableValues(&$record, $filter=array()){
    $options = array();
    $person_id = $record->val('pup_person_id');
    $query = "SELECT cla_id, CONCAT(cla_grade, ' (', cla_year, ')')
      FROM class INNER JOIN class_member ON cla_id=cm_class_id 
      WHERE cm_person_id=$person_id 
      ORDER BY cla_year DESC;";

    $res = df_query($query, null, true, true);
    if ( is_array($res) ){
      foreach ($res as $row){
        $valuekey = $row[0];
        $valuevalue = count($row)>1 ? $row[1] : $row[0];
        $options['cla_id='.$valuekey] = $valuevalue;
      }
    } else {
      throw new Exception("Query '".$query."' for addable values failed. ", E_USER_NOTICE);
    }
    return $options;
  }
  
  function subject_comment__getAddableValues(&$record, $filter=array()){
    $options = array();
    $person_id = $record->val('pup_person_id');
    $query = "SELECT sub_id, CONCAT(sub_name, ' ', cla_grade, ' (', cla_year, ')')
      FROM class INNER JOIN class_member ON cla_id=cm_class_id 
      INNER JOIN subject ON sub_class_id=cla_id 
      WHERE cm_person_id=$person_id
      ORDER BY cla_year DESC, sub_name;";

    $res = df_query($query, null, true, true);
    if ( is_array($res) ){
      foreach ($res as $row){
        $valuekey = $row[0];
        $valuevalue = count($row)>1 ? $row[1] : $row[0];
        $options['sub_id='.$valuekey] = $valuevalue;
      }
    } else {
      throw new Exception("Query '".$query."' for addable values failed. ", E_USER_NOTICE);
    }
    return $options;
  }
}

?>