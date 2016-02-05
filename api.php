<?php
  
  /**
   * Webmanager API 
   */     

  $IDENT_FILE = "./id.conf";
  $STATUS_FILE = "./status";
  $NETSTAT_FILE = "./netconfig.dat";
  $NETSTAT_SCRIPT = "./netconfig.sh";
  $CONFIG_FILE = "./xhfc.conf";
  $CONFIG_SCRIPT = "./xhfc.sh";
  $COUNTER_SCRIPT = "./aclr.sh";
   
  if (!isset($_REQUEST['task'])) die('{"status":"Parameter error"}'); 
   
  $func = $_REQUEST['task'];

  $res = "";
  
  switch ($func) {
    case "cps":       // copy state
      $file = fopen($STATUS_FILE, "r") or die('{"status":"Unable to read '.$STATUS_FILE.'!"}');
      fclose($file);
      
      echo file_get_contents($STATUS_FILE);
      return;

    case "clrcntrs":  // clear counters
      //shell_exec("echo clear=1 > ".$STATUS_FILE);
      if (shell_exec($COUNTER_SCRIPT) == null) {
        die('{"status":"Script execution error !!!!"}');
      }
      break;
      
    case "rident":    // read Identification
    case "rnetset":   // read NetSettings
    case "rconfig":   // read Configuration
      $ret = (object)array();
      
      if ($func == "rident")
        $f = $IDENT_FILE;
      else  
      if ($func == "rnetset")
        $f = $NETSTAT_FILE;
      else  
        $f = $CONFIG_FILE;
      $file = fopen($f, "r") or die('{"status":"Unable to read '.$f.'!"}');
      
      while(!feof($file)) {
        $line = trim(fgets($file)); 
        if ($line == "" || ($line[0] == "[" && $line[strlen($line) - 1] == "]")) continue;
        $r = explode("=", $line);
        $ret->$r[0] = $r[1];
      }
      
      fclose($file);
      
      echo json_encode($ret);
      return;
      
    case "wnetset":   // submit NetSettings
    case "wconfig":   // submit Configuration
      $txt = ""; 
      if ($func == "wnetset") {
        $f = $NETSTAT_FILE;
        $s = $NETSTAT_SCRIPT;
        
        $txt .= "hostname=" . $_REQUEST['hn'] . "\n"; 
        $txt .= "ip=" . $_REQUEST['ip'] . "\n"; 
        $txt .= "gateway=" . $_REQUEST['gateway'] . "\n"; 
        $txt .= "mask=" . $_REQUEST['mask'] . "\n"; 
        $txt .= "dns=" . $_REQUEST['dns'] . "\n"; 
      } else {  
        $f = $CONFIG_FILE;
        $s = $CONFIG_SCRIPT;

        $txt .= "[default]\n"; 
        $txt .= "modes=" . $_REQUEST['modes'] . "\n"; 
        $txt .= "ip_target=" . $_REQUEST['ip_target'] . "\n"; 
        $txt .= "send_ms=" . $_REQUEST['send_ms'] . "\n"; 
        $txt .= "jbuf=" . $_REQUEST['jbuf'] . "\n"; 
        $txt .= "redu=" . $_REQUEST['redu'] . "\n"; 
        $txt .= "billing=" . $_REQUEST['billing'] . "\n"; 
        $txt .= "level_b=" . $_REQUEST['level_b'] . "\n"; 
        $txt .= "ilim=" . $_REQUEST['ilim'] . "\n"; 
        $txt .= "imp=" . $_REQUEST['imp'] . "\n"; 
        $txt .= "rxgain=" . $_REQUEST['rxgain'] . "\n"; 
        $txt .= "txgain=" . $_REQUEST['txgain'] . "\n"; 
      }
      $file = fopen($f, "w") or die('{"status":"Unable to open '.$f.'!"}');
      fwrite($file, $txt);
      fclose($file);
      
      if (shell_exec("$s") == null) {
        die('{"status":"'.$s.' - script execution error !!!!"}');
      }
      break;
    default: die('{"status":"Unable to process command '.$func.'!"}');
  }
  
  $res = ($res == "") ? "OK" : $res;
  
  echo '{"status":"'.$res.'"}';

?>