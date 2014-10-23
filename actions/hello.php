<?php
class actions_hello {
    function handle(&$params){
      $app = Dataface_Application::getInstance();
      $query =& $app->getQuery();
      $current_record =& $app->getRecord();
      $app->addMessage('it works!');
      // df_display(array(),'Dataface_View_Record.html');
      df_display(array(),'Dataface_Form_Template.html');
    }
}
?>