<div id="loginBoxPanel">
	<div class="container">
	    <div id='loginForm'>
			<div align="center" id="wpmem_msg" style="display: none;">
				<h2 id="wpmemloginfail">Login Failed!</h2>
				<p id="wpmemloginmsg">You entered an invalid username or password. Please try again</p> 
			</div>


	      <div id="wpmem_login">
			<a name="login"></a>
			<form action="" id="loginform" method="POST" class="form">
				<fieldset>
 
					<label for="username">Email</label>
					<div class="div_text">
						<input name="log" type="text" id="name" value="" class="username" placeholder="email address">

					</div>

					<label for="password">Password</label>
					<div class="div_text">
						<input name="pwd" type="password" id="word" class="password" placeholder="password">

					</div>
 
					<div class="button_div">
		 
						<div class="submit-reg"><input id="login_submit" type="submit" name="Submit" value="Login" class="buttons"></div>
					</div>

					<div class="clear"></div>
					<div align="right" class="pass-reg "><a href="http://localhost/essentialhospitals/membernetwork/my-profile/?a=pwdreset">Forgot your password?</a></div>
					<div align="right" class="new-reg ">Don't have an account?&nbsp;<a href="http://localhost/essentialhospitals/membernetwork/registration/">Sign up</a></div>
					<div class="clear"></div>
					<div class="tester"> </div>
				</fieldset>
			</form>
		</div>




	      <?php //echo do_shortcode('[wp-members page="login"]'); ?>
	    </div>
	</div>
</div>

 