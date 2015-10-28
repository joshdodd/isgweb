<?php
/*
Plugin Name: WP Engine Has Your Back Widget
Description: Display WP Engine feed on WordPress Dashboard
*/

function run_wpe_dify_news_feed()
{
	if(get_option('wpengine_news_feed_enabled', true)) {
		add_action("wp_dashboard_setup", "wpe_dify_add_dashboard_widget");
	}
}
add_action( 'init', 'run_wpe_dify_news_feed' );

function wpe_dify_add_dashboard_widget()
{
	wp_add_dashboard_widget(
		"wpe_dify_news_feed",
		"WP Engine has your back",
		"display_wpe_dify_news_feed"
	);
}

function display_wpe_dify_news_feed()
{
	$plugin = WpeCommon::instance();
	$site_info = $plugin->get_site_info();
	$install_name = $site_info->name;
	$show_hidden = (apply_filters('wpengine_show_hidden_news', false)) ? "true" : "false";
	wp_enqueue_script( 'wpe-dify-blog', WPE_PLUGIN_URL . '/js/dify-blog.js', array( 'jquery' ), WPE_PLUGIN_VERSION, true );
?>
	<div id='wpe-dify-widget' >
	<?php
		include __DIR__ . "/dify-partial.php";
		display_wpe_dify($show_hidden, $install_name);
	?>
		<div class='wpe-dify-widget-overlay'> <!-- Used for gradient overlay -->
		</div>
	</div>
<?php
}
