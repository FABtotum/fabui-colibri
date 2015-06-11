<?php

class System
{

   /**
    * Shut down teh system
    *
    * @param int &$status Variable for storing command exit status
    * @param bool $return Only return command output instead of echoig it
    *
    * @return misc Command output
    */
   function shutdown (&$status, $return=FALSE)
   {
      $output = array();

      // Shutdown python script
      $mw_shutdown = 'sudo python /var/www/fabui/python/gmacro.py shutdown';
      if ($return)
         exec($mw_shutdown, $output, $status);
      else
         $output[] = system($mw_shutdown, $status);

      if ($status != 0)
         return $output;

      $cmds = array('/sbin/shutdown -h now', '/sbin/poweroff', '/sbin/halt');
      for ($status = -1; $status != 0 && count($cmds); )
      {
         $os_shutdown = 'sudo -E '.array_shift($cmds);
         if ($return)
            exec($os_shutdown, $output, $status);
         else
            $output[] = system($os_shutdown, $status);
      }

      return $output;
   }

}
