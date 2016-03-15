<div id='loginSmall'>
	<div align="center" id="wpmem_msg-sm" style="display: none;">
		<h2 id="wpmemloginfail-sm">Login Failed!</h2>
		<p id="wpmemloginmsg-sm">You entered an invalid username or password. Please try again</p> 
	</div>


    <div class="wpmem_login">
		 
		<form action="" id="loginform-sm" method="POST" class="form">
			<fieldset>

				<label for="username">Email</label>
				<div class="div_text">
					<input name="log" type="text" id="name-sm" value="" class="username" placeholder="email address">

				</div>

				<label for="password">Password</label>
				<div class="div_text">
					<input name="pwd" type="password" id="word-sm" class="password" placeholder="password">

				</div>

				<div class="button_div">
	 
					<div class="submit-reg"><input id="login_submit-sm" type="submit" name="Submit" value="Login" class="buttons"></div>
				</div>

				<div class="clear"></div>
				<div   class="pass-reg "><a href="<?php echo get_bloginfo('url' ); ?>/membernetwork/reset-password/">Forgot your password?</a></div>
				<div align="right" class="new-reg ">Don't have an account?&nbsp;<a href="<?php echo get_bloginfo('url' ); ?>/membernetwork/registration/">Sign up</a></div>
				<div class="clear"></div>
			</fieldset>
		</form>
	</div>
 </div>