<?php
include_once('bbcode.php');
//cartpaujPM CLASS
if (!class_exists("cartpaujPM"))
{
  class cartpaujPM
  {
/******************************************SETUP BEGIN******************************************/
    //Constructor
    function cartpaujPM()
    {
      $this->setupLinks();
      $this->adminOps = $this->getAdminOps();
    }

    function pmActivate()
    {
      global $table_prefix, $wpdb;

      $charset_collate = '';
      if( $wpdb->has_cap('collation'))
      {
        if(!empty($wpdb->charset))
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate))
          $charset_collate .= " COLLATE $wpdb->collate";
      }

      $sqlMsgs = 	"CREATE TABLE ".$this->tableMsgs."(
            `id` int(11) NOT NULL auto_increment,
            `parent_id` int(11) NOT NULL default '0',
            `from_user` int(11) NOT NULL default '0',
            `to_user` int(11) NOT NULL default '0',
            `last_sender` int(11) NOT NULL default '0',
            `date` datetime NOT NULL default '0000-00-00 00:00:00',
            `last_date` datetime NOT NULL default '0000-00-00 00:00:00',
            `message_title` varchar(65) NOT NULL,
            `message_contents` longtext NOT NULL,
            `message_read` int(11) NOT NULL default '0',
            `to_del` int(11) NOT NULL default '0',
            `from_del` int(11) NOT NULL default '0',
            PRIMARY KEY (`id`))
            {$charset_collate};";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      dbDelta($sqlMsgs);
    }

    function widget($args)
    {
      global $user_ID;
      $uData = get_userdata($user_ID);
      echo $args['before_widget'];
      echo $args['before_title'].__("Messages", "cartpaujpm").$args['after_title'];
      if (!$uData)
        echo __("Login to view your messages", "cartpaujpm");
      else
      {
        $URL = get_permalink($this->getPageID());
        $numNew = $this->getNewMsgs();
        $numAnn = $this->getAnnouncementsNum();
        echo __("Hi", "cartpaujpm")." ".$uData->user_login.",<br/>".
        __("You have", "cartpaujpm")." (<font color='red'>".$numNew."</font>) ".__("new messages", "cartpaujpm")."<br/>".
        __("There are", "cartpaujpm")." (".$numAnn.") ".__("announcement(s)", "cartpaujpm")."<br/>".
        "<a href='".$URL."'>".__("View Message Box", "cartpaujpm")."</a>";
      }
      echo $args['after_widget'];
    }

    //Setup some variables
    var $adminOpsName = "cartpaujPM_options";
    var $adminOps = array();
    var $userOpsName = "cartpaujPM_uOptions";
    var $userOps = array();

    var $notify = "";

    var $pluginDir = "";
    var $pluginURL = "";
    var $styleDir = "";
    var $styleURL = "";
    var $pageURL = "";
    var $actionURL = "";
    var $jsURL = "";

    var $tableMsgs = "";

    function jsInit()
    {
      if($_GET['pmjsscript'] == '1')
      {
        global $wpdb, $user_ID;
        require_once('js/search.php');
      }
    }

    function setupLinks() //And DB table name too :)
    {
      global $table_prefix;
      $this->pluginDir = ABSPATH."wp-content/plugins/cartpauj-pm/";
      $this->pluginURL = WP_CONTENT_URL."/plugins/cartpauj-pm/";
      $this->styleDir = $this->pluginDir."style/";
      $this->styleURL = $this->pluginURL."style/";
      $this->jsURL = $this->pluginURL."js/";

      $this->tableMsgs = $table_prefix."cartpauj_pm_messages";
    }

    function addToWPHead()
    {
      ?>
      <script language="JavaScript" type="text/javascript" src="<?php echo $this->jsURL; ?>script.js"></script>
      <link rel="stylesheet" type="text/css" href="<?php echo $this->styleURL; ?>style.css" />
      <?php
    }

    function getPageID()
    {
      global $wpdb;
      return $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[cartpauj-pm]%' AND post_status = 'publish' AND post_type = 'page'");
    }

    function setPageURLs()
    {
      global $wp_rewrite;
      if($wp_rewrite->using_permalinks())
        $delim = "?";
      else
        $delim = "&";
      $this->pageURL = get_permalink($this->getPageID());
      $this->actionURL = $this->pageURL.$delim."pmaction=";
    }
/******************************************SETUP END******************************************/

/******************************************ADMIN SETTINGS PAGE BEGIN******************************************/
    function addAdminPage()
    {
      add_options_page('Cartpauj PM', 'Cartpauj PM', 9, basename(__FILE__), array(&$this, "dispAdminPage"));
    }

    function dispAdminPage()
    {
      if ($this->pmAdminSave())
        echo "<div id='message' class='updated fade'><p>".__("Options successfully saved", "cartpaujpm")."</p></div>";
      $viewAdminOps = $this->getAdminOps(); //Get current options
      echo 	"<div class='wrap'>
          <h2>".__("Cartpauj PM Settings", "cartpaujpm")."</h2>
          <form id='pm-admin-save-form' name='pm-admin-save-form' method='post' action=''>
          <table class='widefat'>
          <thead>
          <tr><th width='50%'>".__("Setting", "cartpaujpm")."</th><th width='50%'>".__("Value", "cartpaujpm")."</th></tr>
          </thead>
          <tr><td>".__("Max messages a user can keep in box? (0 = Unlimited)", "cartpaujpm")."<br /><small>".__("Admins always have Unlimited", "cartpaujpm")."</small></td><td><input type='text' size='10' name='num_messages' value='".$viewAdminOps['num_messages']."' /> ".__("Default","cartpaujpm").": 50</td></tr>
          <tr><td>".__("Messages to show per page", "cartpaujpm")."<br/><small>".__("Do not set this to 0!", "cartpaujpm")."</small></td><td><input type='text' size='10' name='messages_page' value='".$viewAdminOps['messages_page']."' /> ".__("Default","cartpaujpm").": 15</td></tr>
          <tr><td colspan='2'><input type='checkbox' name='hide_branding' ".checked(($viewAdminOps['hide_branding'] || $viewAdminOps['hide_branding'] == 'on'))." /> ".__("Hide \"Cartpauj PM\" Branding Footer", "cartpaujpm")."</td></tr>
          <tr><td colspan='2'><span><input class='button' type='submit' name='pm-admin-save' value='".__("Save Options", "cartpaujpm")."' /></span></td></tr>
          </table>
          </form>
          <p><strong>".__("Setup Instructions", "cartpaujpm").":</strong></p>
          <p><ul><li>".__("Create a new page.", "cartpaujpm")."</li>
          <li>".__("Paste [cartpauj-pm] under the HTML tab of the page editor", "cartpaujpm")."</li>
          <li>".__("Publish the page", "cartpaujpm")."</li>
          </ul></p>
          </div>";
    }

    function pmAdminSave()
    {
      if (isset($_POST['pm-admin-save']))
      {
        $saveAdminOps = array('num_messages' 	=> $_POST['num_messages'],
                              'messages_page' => $_POST['messages_page'],
                              'hide_branding' => $_POST['hide_branding']
        );
        update_option($this->adminOpsName, $saveAdminOps);
        return true;
      }
      return false;
    }

    function getAdminOps()
    {
      $pmAdminOps = array('num_messages' => 50,
                          'messages_page' => 15,
                          'hide_branding' => false
      );

      //Get old values if they exist
      $adminOps = get_option($this->adminOpsName);
      if (!empty($adminOps))
      {
        foreach ($adminOps as $key => $option)
          $pmAdminOps[$key] = $option;
      }

      update_option($this->adminOpsName, $pmAdminOps);
      $this->adminOps = $pmAdminOps;
      return $pmAdminOps;
    }
/******************************************ADMIN SETTINGS PAGE END******************************************/

/******************************************USER SETTINGS PAGE BEGIN******************************************/
    function dispUserPage()
    {
      global $user_ID;
      if ($this->pmUserSave())
        $this->notify = __("Your settings have been saved!", "cartpaujpm");
      $viewUserOps = $this->getUserOps($user_ID); //Get current options
      $prefs = "<p><strong>".__("Set your preferences below", "cartpaujpm").":</strong></p>
      <form id='pm-user-save-form' name='pm-user-save-form' method='post' action=''>
      <input type='checkbox' name='allow_messages' value='true'";
      if($viewUserOps['allow_messages'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Allow others to send me messages?", "cartpaujpm")."</i><br/>

      <input type='checkbox' name='allow_emails' value='true'";
      if($viewUserOps['allow_emails'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Email me when I get new messages?", "cartpaujpm")."</i><br/>
      <input class='button' type='submit' name='pm-user-save' value='".__("Save Options", "cartpaujpm")."' />
      </form>";
      return $prefs;
    }

    function pmUserSave()
    {
      global $user_ID;
      if (isset($_POST['pm-user-save']))
      {
        $saveUserOps = array(	'allow_emails' 	=> $_POST['allow_emails'],
                    'allow_messages' => $_POST['allow_messages']
        );
        update_usermeta($user_ID, $this->userOpsName, $saveUserOps);
        return true;
      }
      return false;
    }

    function getUserOps($ID)
    {
      $pmUserOps = array(	'allow_emails' 		=> 'true',
                'allow_messages' 	=> 'true'
      );

      //Get old values if they exist
      $userOps = get_usermeta($ID, $this->userOpsName);
      if (!empty($userOps))
      {
        foreach ($userOps as $key => $option)
          $pmUserOps[$key] = $option;
      }

      update_usermeta($ID, $this->userOpsName, $pmUserOps);
      return $pmUserOps;
    }
/******************************************USER SETTINGS PAGE END******************************************/

/******************************************NEW MESSAGE PAGE BEGIN******************************************/
    function dispNewMsg()
    {
      global $user_ID;
      $users = $this->get_users();
      $to = $_GET['to'];
      if (!$to)
        $to = 0;

      $adminOps = $this->getAdminOps();
      if (!$this->isBoxFull($user_ID, $adminOps['num_messages'], '1'))
      {
      	if(isset($_GET['username'])){
	      	$msguser = $_GET['username'];
	      	$disable = 'disabled';
	      	$user = get_user_by('login', $msguser);
		  	$userEmail = $user->user_email;
		  	$userPerson = $user->first_name . $user->last_name;
      	}else{
	      	$msguser = '';
	      	$disable = '';
      	}
        $newMsg = "<p><strong>".__("Create New Message", "cartpaujpm").":</strong></p>";
        $newMsg .= "<form name='message' action='".$this->actionURL."checkmessage' method='post'>".
        __("Select a member", "cartpaujpm").":<br/>".
        "<input type='text' id='search-q' onkeyup='javascript:autosuggest(\"".$this->actionURL."\")' autocomplete='off' value='".$userPerson."'/><br/>
        <input type='hidden' id='realmessage' name='message_to' value='".$msguser."'/>
        <div id='results'></div>".
        __("Subject", "cartpaujpm").":<br/>
        <input type='text' name='message_title' maxlength='65' value='' /><br/>".
        __("Write your message", "cartpaujpm").":<br/>".$this->get_form_buttons()."<br/>
        <textarea name='message_content'></textarea>
        <input type='hidden' name='message_from' value='".$user_ID."' />
        <input type='hidden' name='message_date' value='".current_time('mysql', $gmt = 1)."' />
        <input type='hidden' name='parent_id' value='0' /><br/>
        <a href='".$this->pageURL."'>Cancel</a>
        <input type='submit' onClick='this.disabled=true;this.form.submit();' value='".__("Send", "cartpaujpm")."' />
        </form>";
        return $newMsg;
      }
      else
      {
        $error = "<p><strong>".__("Message Error", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
        $this->notify = __("You cannot send messages because your message box is full!", "cartpaujpm");
        return $error;
      }
    }
/******************************************NEW MESSAGE PAGE END******************************************/

/******************************************READ MESSAGE PAGE BEGIN******************************************/
    function dispReadMsg()
    {
      global $wpdb, $user_ID;

      $pID = $_GET['id'];
      $wholeThread = $this->getWholeThread($pID);

      $threadOut = "";

      foreach ($wholeThread as $post)
      {
        //Check for privacy errors first
        if ($post->to_user != $user_ID && $post->from_user != $user_ID)
        {
          $error = "<p><strong>".__("Privacy Error", "cartpaujpm").":</strong></p>
          <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
          $this->notify = __("You do not have permission to view this message!", "cartpaujpm");
          return $error;
        }

        //setup info for the reply form
        if ($post->parent_id == 0) //If it is the parent message
        {
          $to = $post->from_user;
          if ($to == $user_ID) //Make sure user doesn't send a message to himself
            $to = $post->to_user;
          $message_title = $this->output_filter($post->message_title);
          if (substr_count($message_title, __("Re:", "cartpaujpm")) < 1) //Prevent all the Re:'s from happening
            $re = __("Re:", "cartpaujpm");
          else
            $re = "";
        }

        $uData = get_userdata($post->from_user);

        if ($post->parent_id == 0) //If it is the parent message
        {
          $threadOut .= "
          	<h3 class='msg-title'>Message: ".$post->message_title."</h3>
          	<div class='threadlist parent'>
          		<div class='gutter'>
          			<div class='author'>
          				<span class='author-name'>".$uData->user_firstname." ".$uData->user_lastname."</span>
          				".get_avatar($post->from_user, 60)."
          			</div>
          			<div class='content'>
          				<div class='gutter'>
          					<span class='date'>".$this->formatDate($post->date)."</span>
		  					".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."
          				</div>
          			</div>
          		</div>
          	</div>
          ";
        }
        else
        {
          $threadOut .= "
          <div class='threadlist'>
          		<div class='gutter'>
          			<div class='author'>
          				<span class='author-name'>".$uData->user_firstname." ".$uData->user_lastname."</span>
          				".get_avatar($post->from_user, 60)."
          			</div>
          			<div class='content'>
          				<div class='gutter'>
          					<span class='date'>".$this->formatDate($post->date)."</span>
          					".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."
          				</div>
          			</div>
          		</div>
          	</div>
          ";
        }
      }

      //SHOW THE REPLY FORM
      $threadOut .= "
      <div class='msg-reply'><div class='gutter'><span class='reply'>".__("Reply", "cartpaujpm")."</span>
      <form name='message' action='".$this->actionURL."checkmessage' method='post'>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>
      <input type='hidden' name='message_to' value='".$this->convertToUser($to)."' />
      <input type='hidden' name='message_title' value='".$re.$message_title."' />
      <input type='hidden' name='message_from' value='".$user_ID."' />
      <input type='hidden' name='message_date' value='".current_time('mysql', $gmt = 1)."' />
      <input type='hidden' name='parent_id' value='".$pID."' />
      <input type='submit' onClick='this.disabled=true;this.form.submit();' value='".__("Send Message", "cartpaujpm")."' /></form></div></div>";

      if ($user_ID != $post->from_user) //Update only if the reader is not the sender ???
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET message_read = 1 WHERE id = %d", $pID));

      return $threadOut;
    }

    function getWholeThread($id)
    {
      global $wpdb;
      $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->tableMsgs} WHERE id = %d OR parent_id = %d ORDER BY id ASC", $id, $id));
      return $results;
    }

    function getThreadCount($id)
    {
      global $wpdb;
      $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->tableMsgs} WHERE id = %d OR parent_id = %d ORDER BY id ASC", $id, $id));
      $num = count($results);
      return $num;
    }

    function convertToUser($to)
    {
      global $wpdb;
      $result = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM {$wpdb->users} WHERE ID = %d", $to));
      return $result;
    }
/******************************************READ MESSAGE PAGE END******************************************/

/******************************************CHECK MESSAGE PAGE BEGIN******************************************/
    function dispCheckMsg()
    {
      global $wpdb, $user_ID;
      $from = $_POST['message_from'];
      $preTo = $_POST['message_to'];
      $to = $this->convertToID($preTo);
      $myReplaceSub = array("'", "\\");//Make sure we get ' and \ out of the message title
      $title = str_replace($myReplaceSub, "", $this->input_filter($_POST['message_title']));
      $content = $this->input_filter($_POST['message_content']);
      $parentID = $_POST['parent_id'];
      $date = $_POST['message_date'];

      $adminOps = $this->getAdminOps();
      if ($to)
        $toUserOps = $this->getUserOps($to);

      //Check for errors first
      if (!$to || !$title || !$content || ($from != $user_ID))
      {
        if (!$to)
          $theError = __("You must enter a valid recipient!", "cartpaujpm");
        if (!$title)
          $theError = __("You must enter a valid subject!", "cartpaujpm");
        if (!$content)
          $theError = __("You must enter some message content!", "cartpaujpm");
        if ($from != $user_ID)
          $theError = __("You do not have permission to send this message!", "cartpaujpm");
        $error = "<p><strong>".__("Message Error", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
        $this->notify = $theError;
        return $error;
      }
      if ($toUserOps['allow_messages'] != 'true')
      {
        $error = "<p><strong>".__("Message Error", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
        $this->notify = __("This user does not want to receive messages!", "cartpaujpm");
        return $error;
      }
      if ($this->isBoxFull($to, $adminOps['num_messages'], $parentID))
      {
        $error = "<p><strong>".__("Message Error", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
        $this->notify = __("The Recipients Message Box Is Full!", "cartpaujpm");
        return $error;
      }

      //If no errors then continue on
      if ($parentID == 0)
        $wpdb->query("INSERT INTO {$this->tableMsgs} (from_user, to_user, message_title, message_contents, parent_id, last_sender, date, last_date) VALUES ('{$from}','{$to}','{$title}','{$content}','{$parentID}','{$from}','{$date}','{$date}')");
      else
      {
        $wpdb->query("INSERT INTO {$this->tableMsgs} (from_user, to_user, message_title, message_contents, parent_id, date) VALUES ('{$from}','{$to}','{$title}','{$content}','{$parentID}','{$date}')");
        //A lot of querys but they're fairly quick and won't get called except when messages are sent
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET message_read = 0 WHERE id = %d", $parentID));
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET last_sender = '{$from}' WHERE id = %d", $parentID));
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET last_date = '{$date}' WHERE id = %d", $parentID));
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET to_del = 0 WHERE id = %d", $parentID));
        $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET from_del = 0 WHERE id = %d", $parentID));
      }

      $check = "<p><strong>".__("Message Sent", "cartpaujpm").":</strong></p>
      <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
      $this->notify = __("Your message was successfully sent!", "cartpaujpm");

      $this->sendEmail($to, $from, $content);

      return $check;
    }

    function isBoxFull($to, $boxSize, $parentID)
    {
      global $wpdb;
      $to_user = get_userdata($to);

      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->tableMsgs} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1)", $to, $to));
      $num = $wpdb->num_rows;

      if ($boxSize == 0 || $num < $boxSize || $parentID != 0 || current_user_can('level_9') || $to_user->user_level >= 9)
        return false;
      else
        return true;
    }

    function sendEmail($to, $from, $content)
    {
      $toOptions = $this->getUserOps($to);
      $notify = $toOptions['allow_emails'];
      if ($notify == 'true')
      {
        $sendername = get_bloginfo("name");
        $sendermail = get_bloginfo("admin_email");
        $uData = get_userdata($from);
        $sendfrom = $uData->user_login;
        $headers = "MIME-Version: 1.0\r\n" .
          "From: ".$sendername." "."<".$sendermail.">\n" .
          "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\r\n";
        $mailMessage = __("You have received a new message from", "cartpaujpm")." ".$sendfrom.", ".__("follow this link to view it", "cartpaujpm").": ".$this->pageURL;
        $mUser = get_userdata($to);
        $mailTo = $mUser->user_email;



        $toUser = get_userdata($to);
    		$toMail = $toUser->user_email;

    	$fromUser = get_userdata($from);
			$fromFname = $fromUser->first_name;
			$fromLname = $fromUser->last_name;
			$fromHosp = get_user_meta($from, 'hospital_name', true);

		$link = get_permalink(248);

    	$subject = "Message from $fromFname $fromLname, a fellow association member";
		$message = "$fromFname $fromLname of $fromHosp writes:<br><br>
					$content<br><br>
					<a href='$link'>Visit your inbox and respond to messages</a>.";
		$headers = "From: America's Essential Hospitals <info@essentialhospitals.org>";

		wp_mail($mailTo, $subject, $message, $headers);
      }
    }

    function convertToID($preTo)
    {
      global $wpdb, $user_ID;
      $result = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE user_login = %s", $preTo));
      if ($result != $user_ID && $result)
        return $result;
      else
        return 0;
    }
/******************************************CHECK MESSAGE PAGE END******************************************/

/******************************************MESSAGE-BOX PAGE BEGIN******************************************/
    function dispMsgBox()
    {
      global $wpdb, $user_ID;

      $adminOps = $this->getAdminOps();
      $numMsgs = $this->getUserNumMsgs();
      if ($numMsgs)
      {
      	$msgsOut .= "<h2 class='heading'>Messages</h2>";
        $numPgs = $numMsgs / $adminOps['messages_page'];
        if ($numPgs > 1)
        {
          $msgsOut = "<p><strong>".__("Page", "cartpaujpm").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['pmpage'] != $i)
              $msgsOut .= "<a href='".$this->actionURL."messagebox&pmpage=".$i."'>".($i+1)."</a> ";
            else
              $msgsOut .= "[<b>".($i+1)."</b>] ";
          $msgsOut .= "</p>";
        }
        $msgs = $this->getMsgs();
        foreach ($msgs as $msg)
        {
          if ($msg->message_read == 0 && $msg->last_sender != $user_ID){
            $read = "unread";
          }else{
	          $read = 'read';
          }
          $uSend = get_userdata($msg->from_user);
          $uLast = get_userdata($msg->last_sender);
          $toUser = get_userdata($msg->to_user);
          $msgExcerpt = substr($msg->message_contents, 0, 100);
          $msgsOut .= "
          	<div class='messagelist $read'>
		  		<div class='gutter clearfix'>
		  			<div class='delete'><a href='".$this->actionURL."deletemessage&id=".$msg->id."' onclick='return confirm(\"".__('Are you sure?', 'cartpaujpm')."\");'>".__("Delete", "cartpaujpm")."</a></div>
		  			<span class='title'><strong>".$msg->message_title."</strong> <em>from ".$uSend->user_firstname." ".$uSend->user_lastname."</em></span>
		  			<span class='meta'>".$this->formatDate($msg->date)." || <span class='orange'><em>(".$this->getThreadCount($msg->id)." threads)</em></span></span>
		  			<span class='excerpt'>".$msgExcerpt." <a href='".$this->actionURL."viewmessage&id=".$msg->id."'>read more >></a></span>
		  		</div>
		  	</div>
          ";
        }

        return $msgsOut;
      }
      else
      {
        $empty = "<p><strong>".__("No Messages", "cartpaujpm")."</strong></p>";
        $this->notify = __("Your inbox is empty", "cartpaujpm");
        return $empty;
      }
    }

    function getMsgs()
    {
      global $wpdb, $user_ID;
      $page = $_GET['pmpage'];
      $adminOps = $this->getAdminOps();
      if (!$page)
        $page = 0;
      $start = $page * $adminOps['messages_page'];
      $end = $adminOps['messages_page'];

      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->tableMsgs} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1) ORDER BY last_date DESC LIMIT %d, %d", $user_ID, $user_ID, $start, $end));

      return $get_messages;
    }
/******************************************MESSAGE-BOX PAGE END******************************************/

/******************************************DELETE PAGE BEGIN******************************************/
    function dispDelMsg()
    {
      global $wpdb, $user_ID;

      $delID = $_GET['id'];
      $toDuser = $wpdb->get_var($wpdb->prepare("SELECT to_user FROM {$this->tableMsgs} WHERE id = %d", $delID));
      $toDel = $wpdb->get_var($wpdb->prepare("SELECT to_del FROM {$this->tableMsgs} WHERE id = %d", $delID));
      $fromDel = $wpdb->get_var($wpdb->prepare("SELECT from_del FROM {$this->tableMsgs} WHERE id = %d", $delID));

      if ($toDuser == $user_ID)
      {
        if ($fromDel == 0)
          $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET to_del = 1 WHERE id = %d", $delID));
        else
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->tableMsgs} WHERE id = %d OR parent_id = %d", $delID, $delID));
      }
      else
      {
        if ($toDel == 0)
          $wpdb->query($wpdb->prepare("UPDATE {$this->tableMsgs} SET from_del = 1 WHERE id = %d", $delID));
        else
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->tableMsgs} WHERE id = %d OR parent_id = %d", $delID, $delID));
      }

      $deleted = "<p><strong>".__("Message Deleted", "cartpaujpm").":</strong></p>
      <p><strong><a href='".$this->pageURL."' style='color:navy;'>".__("Back To Message Box", "cartpaujpm")."</a></strong></p>";
      $this->notify = __("Your message was successfully deleted!", "cartpaujpm");

      return $deleted;
    }
/******************************************DELETE PAGE END******************************************/

/******************************************VIEW ANNOUNCEMENTS BEGIN******************************************/
    /*
    Announcement Feature TODO list
    # Mass emails when announcement is created
    # Clean-up style
    */
    function dispAnnouncement()
    {
      global $wpdb, $user_ID;
      $announcements = $this->getAnnouncements();
      $num = $wpdb->num_rows;

      if ($this->addAnnouncement()) //Adding a new announcement?
      {
        $announce = "<p><strong>".__("Announcement Added", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->actionURL."viewannouncements' style='color:navy;'>".__("Back To Announcements", "cartpaujpm")."</a></strong></p>";
        $this->notify = __("The announcement was successfully added!", "cartpaujpm");
        return $announce;
      }

      if ($this->deleteAnnouncement()) //Deleting an announcement?
      {
        $announce = "<p><strong>".__("Announcement Deleted", "cartpaujpm").":</strong></p>
        <p><strong><a href='".$this->actionURL."viewannouncements' style='color:navy;'>".__("Back To Announcements", "cartpaujpm")."</a></strong></p>";
        $this->notify = __("The announcement was successfully deleted!", "cartpaujpm");
        return $announce;
      }

      if (!$num) //Just viewing announcements
      {
        $announce = "<p><strong>".__("Announcements", "cartpaujpm").":</strong></p>";
        if (current_user_can('level_9'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $this->notify = __("There are no announcements!", "cartpaujpm");
      }
      else
      {
        $announce = "<p><strong>".__("Announcements", "cartpaujpm").":</strong></p>";
        if (current_user_can('level_9'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $announce .= "<table>";
        $a = 0;
        foreach ($announcements as $announcement)
        {
          $announce .= "<tr class='trodd".$a."'><td class='pmtext'><strong>".__("Subject", "cartpaujpm").":</strong> ".$this->output_filter($announcement->message_title).
          "<br/><strong>".__("Date", "cartpaujpm").":</strong> ".$this->formatDate($announcement->date);
          if (current_user_can('level_9'))
            $announce .= "<br/><a href='".$this->actionURL."viewannouncements&del=1&id=".$announcement->id."'>".__("Delete", "cartpaujpm")."</a>";
          $announce .= "<hr/>";
          $announce .= "<strong>".__("Message", "cartpaujpm").":</strong><br/>".apply_filters("comment_text", $this->output_filter($announcement->message_contents))."</td></tr>";
          if ($a) $a = 0; else $a = 1; //Alternate table colors
        }
        $announce .= "</table>";
      }

      return $announce;
    }

    function dispAnnounceForm()
    {
      $form = "<p>".__("Add a new announcement below", "cartpaujpm")."</p>
      <form name='message' action='' method='post'>
      ".__("Subject", "cartpaujpm").":<br/>
      <input type='text' name='message_title' value='' /><br/>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>
      <input type='hidden' name='message_date' value='".current_time('mysql', $gmt = 1)."' /><br/>
      <input type='submit' onClick='this.disabled=true;this.form.submit();' name='add-announcement' value='".__("Submit", "cartpaujpm")."' />
      </form>";

      return $form;
    }

    function getAnnouncements()
    {
      global $wpdb; //message_read = 12 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT * FROM {$this->tableMsgs} WHERE message_read = 12 ORDER BY `id` DESC");
      return $results;
    }

    function getAnnouncementsNum()
    {
      global $wpdb; //message_read = 12 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT * FROM {$this->tableMsgs} WHERE message_read = 12 ORDER BY `id` DESC");
      return $wpdb->num_rows;
    }

    function addAnnouncement()
    {
      global $wpdb;
      $title = $this->input_filter($_POST['message_title']);
      $contents = $this->input_filter($_POST['message_content']);
      $date = $_POST['message_date'];
      $read = '12';

      if ($title && $contents)
      {
        $wpdb->query("INSERT INTO {$this->tableMsgs} (message_title, message_contents, date, message_read) VALUES ('{$title}','{$contents}','{$date}','{$read}')");
        return true;
      }
      return false;
    }

    function deleteAnnouncement()
    {
      global $wpdb;
      $delID = $_GET['id'];
      if (current_user_can('level_9') && $_GET['del']) //Make sure only admins can delete announcements
      {
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->tableMsgs} WHERE id = %d", $delID));
        return true;
      }
      return false;
    }
/******************************************VIEW ANNOUNCEMENTS END******************************************/

/******************************************MAIN DISPLAY BEGIN******************************************/
    function dispHeader()
    {
      global $user_ID, $user_login;

      $numNew = $this->getNewMsgs();
      $numAnn = $this->getAnnouncementsNum();
      $msgBoxSize = $this->getUserNumMsgs();
      $adminOps = $this->getAdminOps();
      if ($adminOps['num_messages'] == 0 || current_user_can('level_9'))
        $msgBoxTotal = __("Unlimited", "cartpaujpm");
      else
        $msgBoxTotal = $adminOps['num_messages'];

      $header = "<div id='pm-wrapper'>";
      $header .= "<div id='pm-header'>";
      $header .= get_avatar($user_ID, 60)."<p><strong>".__("Welcome", "cartpaujpm").": ".$user_login."</strong><br/>";
      $header .= __("You have", "cartpaujpm")." (<font color='red'>".$numNew."</font>) ".__("new messages", "cartpaujpm").
      " ".__("and", "cartpaujpm")." (".$numAnn.") ".__("announcement(s)", "cartpaujpm")."<br/>";
      if ($msgBoxTotal == __("Unlimited", "cartpaujpm") || $msgBoxSize < $msgBoxTotal)
        $header .= __("Message box size", "cartpaujpm").": ".$msgBoxSize." ".__("of", "cartpaujpm")." ".$msgBoxTotal."</p>";
      else
        $header .= "<font color='red'>".__("Your Message Box Is Full!", "cartpaujpm")."</font></p>";
      $header .= "</div>";
      return $header;
    }

    function dispMenu()
    {
      $menu = "<div id='pm-menu'>";
      $menu .= "<a href='".$this->pageURL."'>".__("Message Box", "cartpaujpm")."</a> | ";
      $menu .= "<a href='".$this->actionURL."viewannouncements'>".__("Announcements", "cartpaujpm")."</a> | ";
      $menu .= "<a href='".$this->actionURL."newmessage'>".__("New Message", "cartpaujpm")."</a> | ";
      $menu .= "<a href='".$this->actionURL."directory'>".__("Directory", "cartpaujpm")."</a> | ";
      $menu .= "<a href='".$this->actionURL."settings'>".__("Settings", "cartpaujpm")."</a>";
      $menu .= "</div>";
      $menu .= "<div id='pm-content'>";
      return $menu;
    }

    function dispNotify()
    {
      $notify = "<div id='pm-notify'>".$this->notify."</div>";
      return $notify;
    }

    function dispFooter()
    {
      $footer = "</div>"; //End content
        //Maybe Add Notify
        if ($this->notify != "")
          $footer .= $this->dispNotify();

      if($this->adminOps['hide_branding'] != 'on')
        $footer .= "<div id='pm-footer'><a href='http://cartpauj.icomnow.com'>Cartpauj PM ".$this->get_version()."</a></div>";

      $footer .= "</div>"; //End main wrapper

      return $footer;
    }

    function dispDirectory()
    {
      $users = $this->get_users();
      $directory = "";

      foreach($users as $u)
      {
        $directory .= '<p><strong>'.$u->user_login.'</strong> - <a href="'.$this->actionURL.'newmessage&to='.$u->ID.'">'.__('Send Message', 'cartpaujpm').'</a></p>';
      }
      return $directory;
    }

    //Display the proper contents
    function displayAll()
    {
      global $user_ID;
      if ($user_ID)
      {
        //Finish the setup since these wouldn't work in the constructor
        $this->userOps = $this->getUserOps($user_ID);
        $this->setPageURLs();

        //Add header
        $out = $this->dispHeader();

        //Add Menu
        $out .= $this->dispMenu();

        //Start the guts of the display
        switch ($_GET['pmaction'])
        {
          case 'newmessage':
            $out .= $this->dispNewMsg();
            break;
          case 'checkmessage':
            $out .= $this->dispCheckMsg();
            break;
          case 'viewmessage':
            $out .= $this->dispReadMsg();
            break;
          case 'deletemessage':
            $out .= $this->dispDelMsg();
            break;
          case 'directory':
            $out .= $this->dispDirectory();
            break;
          case 'settings':
            $out .= $this->dispUserPage();
            break;
          case 'viewannouncements':
            $out .= $this->dispAnnouncement();
            break;
          default: //Message box is shown by Default
            $out .= $this->dispMsgBox();
            break;
        }

        //Add footer
        $out .= $this->dispFooter();
      }
      else
      {
        $out = "<p><strong>".__("You must be logged-in to view this page.", "cartpaujpm")."</strong></p>";
      }
      return $out;
    }
/******************************************MAIN DISPLAY END******************************************/

/******************************************MISC. FUNCTIONS BEGIN******************************************/
    function get_users()
    {
      global $wpdb, $table_prefix;
      return $wpdb->get_results("SELECT user_login, ID FROM $wpdb->users ORDER BY user_login ASC");
    }

    function get_form_buttons()
    {
      $button = '
      <a title="'.__("Bold", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[b]", "[/b]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/b.png" /></a>
      <a title="'.__("Italic", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[i]", "[/i]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/i.png" /></a>
      <a title="'.__("Underline", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[u]", "[/u]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/u.png" /></a>
      <a title="'.__("Strikethrough", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[s]", "[/s]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/s.png" /></a>
      <a title="'.__("Code", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[code]", "[/code]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/code.png" /></a>
      <a title="'.__("Quote", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[quote]", "[/quote]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/quote.png" /></a>
      <a title="'.__("List", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[list]", "[/list]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/list.png" /></a>
      <a title="'.__("List item", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[*]", "", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/li.png" /></a>
      <a title="'.__("Link", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[url]", "[/url]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/url.png" /></a>
      <a title="'.__("Image", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[img]", "[/img]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/img.png" /></a>
      <a title="'.__("Email", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[email]", "[/email]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/email.png" /></a>
      <a title="'.__("Add Hex Color", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[color=#]", "[/color]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/color.png" /></a>
            <a title="'.__("Embed", "cartpaujpm").'" href="javascript:void(0);" onclick=\'surroundTheText("[embed]", "[/embed]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/embed.png" /></a>';

      return $button;
    }

    function output_filter($string)
    {
      $parser = new cartpaujBBCParser();
      return stripslashes($parser->bbc2html($string));
    }

    function input_filter($string)
    {
      global $wpdb;
      $Find = array("<", "%", "$"); //Fixes some serious issues when entering these characters, also allows code to be posted
      $Replace = array("&#60;", "&#37;", "&#36;");
      $newStr = str_replace($Find, $Replace, $string);
      return strip_tags($wpdb->escape($newStr));
    }

    function getUserNumMsgs()
    {
      global $wpdb, $user_ID;
      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->tableMsgs} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1) OR (from_user = %d AND parent_id = 0 AND from_del <> 1)", $user_ID, $user_ID));
      $num = $wpdb->num_rows;
      return $num;
    }

    function formatDate($date)
    {
      return date('M j, g:i a', strtotime($date));
    }

    function getNewMsgs()
    {
      global $wpdb, $user_ID;

      $get_pms = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->tableMsgs} WHERE (to_user = %d AND parent_id = 0 AND to_del <> 1 AND message_read = 0 AND last_sender <> %d) OR (from_user = %d AND parent_id = 0 AND from_del <> 1 AND message_read = 0 AND last_sender <> %d)", $user_ID, $user_ID, $user_ID, $user_ID));
      return $wpdb->num_rows;
    }

    function autoembed($string)
    {
      global $wp_embed;
      if (is_object($wp_embed))
        return $wp_embed->autoembed($string);
      else
        return $string;
    }

    function get_version()
    {
      $plugin_data = implode('', file(ABSPATH."wp-content/plugins/cartpauj-pm/pm-main.php"));
      if (preg_match("|Version:(.*)|i", $plugin_data, $version))
        $version = $version[1];
      return $version;
    }
/******************************************MISC. FUNCTIONS END******************************************/
  } //END CLASS
} //ENDIF
?>