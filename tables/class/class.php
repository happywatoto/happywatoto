<?php

class tables_class {

	function getTitle(&$record){
		return $record->val('cla_name');
	}
	
	function titleColumn(){
		return 'cla_name';
	}
  
  function pupils__getAddableValues(&$record, $filter=array()){
    $options = array();
    /* iterate over valuelist
    $pup =& df_get_table_info('pupil');
    $vallist = $pup->getValuelist('personlist');
    foreach (array_keys($vallist) as $valkey) {
      $r = df_get_record('pupil',array('pup_person_id'=>$valkey));
      if ($r) {
        if ($r->_values['pup_facility_id'] == $record->_values['cla_facility_id']) {
          $options['pup_person_id='.$valkey] = $vallist[$valkey];
        }
      }
    }
    //*/
    // NEW: use SQL 
    $facility_id = $record->_values['cla_facility_id'];
    $query = "SELECT per_id, concat_ws(' ', concat_ws(' ', per_name_first, per_name_last),concat('(', per_id, ')'))
                FROM person INNER JOIN pupil ON per_id=pup_person_id
                WHERE pup_facility_id=".$facility_id." 
                AND IF(pup_leave_date IS NULL, TRUE, pup_leave_date > CURDATE())
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
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
         // if the user is null then nobody is logged in... no access.
         // This will force a login prompt.
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      if (isset($record) and my_static_funcs::UserIsClassTeacher($user,$record->val('cla_id'),null)) {
        return Dataface_PermissionsTool::getRolePermissions('EDIT');
      }
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
  
  function rel_class_comment__permissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return null;
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)){
        if (my_static_funcs::UserIsClassTeacher($user,$record->val('cla_id'),null)) {
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
  
  function import_pupils__getAddableValues(&$record, $filter=array()){
    $options = array();
    $query = "SELECT cla_id, CONCAT(cla_grade, ' (', cla_year, ')') AS cla_name
                FROM class 
                WHERE cla_facility_id=".$record->val('cla_facility_id')." AND cla_id<>".$record->val('cla_id')." 
                ORDER BY cla_year DESC, cla_name";
    
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
  
  function class_comment__getAddableValues(&$record, $filter=array()){
    $options = array();
    $cla_id = $record->val('cla_id');
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
  
  function afterAddExistingRelatedRecord(&$record) {
    if ($record->_relationshipName=='import_pupils') {
      // import pupils from given class
      $app = Dataface_Application::getInstance();
      $current_record = $app->getRecord();
      $query = "INSERT INTO class_member
        SELECT ".$current_record->val('cla_id').", cm_person_id FROM class_member WHERE cm_class_id=".$record->val('cla_id');
      df_query($query);
      // empty the dummy table
      $query = "TRUNCATE class_import_pupils";
      df_query($query, null, true, true);
      // show a success message
      $app->saveMessage("The pupils have been successfully imported.");
    }
  }
}

?>