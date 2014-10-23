<?php
class tables_exam {

  function exam_grades__getAddableValues(&$record, $filter=array()){
    $options = array();
    $subject_id = $record->val('exa_subject_id');
    $query = "SELECT per_id, concat_ws(' ', concat_ws(' ', per_name_first, per_name_last),concat('(', per_id, ')'))
                FROM person INNER JOIN class_member ON per_id=cm_person_id INNER JOIN subject ON cm_class_id=sub_class_id 
                WHERE sub_id=$subject_id 
                ORDER BY per_name_first, per_name_last";
    
    $res = df_query($query, null, true, true);
    if ( is_array($res) ){
      foreach ($res as $row){
        $valuekey = $row[0];
        $valuevalue = count($row)>1 ? $row[1] : $row[0];
        $options["eg_person_id=".$valuekey] = $valuevalue;
      }
    } else {
      throw new Exception("Query '".$query."' for addable values failed. ", E_USER_NOTICE);
    }
    
    return $options;
  }
  
  function beforeInsert(&$record){
    // TODO check permissions of class teacher
  }
  
  function afterInsert(&$record){
    $exam_id = $record->_values['exa_id'];
    $subject_id = $record->_values['exa_subject_id'];
    $query = "INSERT INTO `exam_grade` (`eg_exam_id`,`eg_person_id`) 
                SELECT ".$exam_id.", cm_person_id FROM class_member INNER JOIN subject ON cm_class_id=sub_class_id 
                WHERE sub_id=".$subject_id;
    df_query($query);
  }
  
  function getPermissions(&$record){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( !isset($user) ) return Dataface_PermissionsTool::getRolePermissions('NO ACCESS');
    $role = $user->val('use_role');
    if ($role == 'TEACHER') {
      $perms = Dataface_PermissionsTool::getRolePermissions('READ ONLY');
      if (isset($record)) {
        if (my_static_funcs::UserIsClassTeacher($user,null,$record->val('exa_subject_id'))) {
          $perms = Dataface_PermissionsTool::getRolePermissions('EDIT');
        }
      } else {
        // hack: allow edit for all, otherwise class teachers can't create new exams via the relation in table subject
        $perms['edit'] = 1;
      }
      return $perms;
    }
    return Dataface_PermissionsTool::getRolePermissions($role);
  }
}

?>