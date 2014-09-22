<?php
/*
	Plugin Name: Quick Pages
	Plugin URI: https://github.com/snaptortoise/wp-quick-pages
	Description: Quickly add blank pages with hierarchies
	Version: 1.0
	Author: Snaptortoise
	Author URI: http://snaptortoise.com
*/

if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class WPQuickPages {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}
	
	public function add_admin_menu() {
		add_pages_page( "WP Quick Pages", "WP Quick Pages", "read", "wp-quick-pages", array(&$this, 'quick_pages_view') );
	}

	public function quick_pages_view() {
	
		$this->header("Quick Pages"); ?>
			<div class="inner-sidebar">
				<p>You can quickly add blank, published pages with hierarchies by following this simple format:</p>
				
<pre>
Home
Stories
Store
- Software
- Freebies
About
- Team
-- Jane Doe
-- John Doe
- Bio
Contact
- Form
</pre>

			</div>
			<div id="post-body" class="has-right-sidebar">
				<div id="post-body-content">
					<div id="wp-content-wrap" class="wp-editor-wrap html-active">
						<div id="wp-content-editor-container" class="">
							<form method="post" action="">
								<textarea name="pages" id="" cols="40" rows="20" class="wp-editor-area"></textarea>
								<p><input type="submit" value="Create these pages" class="button-primary"/></p>
							</form>
						</div><!-- .wp-editor-container -->
					</div><!-- .wp-editor-wrap -->
					<?php if ( isset($_POST["pages"]) ) {
						$this->insert_pages( $_POST["pages"] );
					} ?>
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		<?php $this->footer();
	}
	
	public function insert_pages( $pages ) {
		echo "<h2>Results</h2>";
		$pages = ( explode("\n", $pages) );
		$site = array();

		foreach ( $pages as $key => $page ) {
			$page = trim( $page );
			$parent = 0;
			$parent_id = 0;
			preg_match( "/^[\-]+/", $page, $child );
			
			if ( @$child[0] ) {
				$depth = strlen( $child[0] ) - 1;
				$page = trim( substr($page, $depth + 1) );
				
				// cycle through and find parent
				for ( $i = $key; $i--; $i >= 0 ) {
					// if we find it...
					$pattern = "/^[\-]{".$depth."}[^\-]/";
						
					if ( (preg_match($pattern, $pages[$i], $test) && $depth > 0) || ($depth == 0 && substr($pages[$i], 0, 1) != "-") ) {
						// Get the WordPress page ID
						$parent = $site[$i]["post_title"];
						$parent_id = $site[$i]["id"];
						$parent_key = $i;
						$i = false;
					}
				}
			}

			$page_array = array(
				"post_title" => $page,
				"post_parent" => $parent_id,
				"post_status" => "publish",
				"post_type" => "page"
			);

			$post_id = wp_insert_post( $page_array, $wp_error );
			$page_array["id"] = $post_id;
			$page_array["parent_key"] = $parent_key;
			$site[$key] = $page_array;
			
			?>
			<p>
				Creating <strong>
				<?php 
				if ($parent_id > 0) {
					echo $parent;
				} 
				echo $page;
				?>
				</strong>
			</p>
			<?php
		}
	}

	public function header( $title ) { ?>
		<div class="wrap columns-2">
			<div id="icon-edit-pages" class="icon32"><br /></div>
			<h2><?php echo $title; ?></h2>
			<br/>
		<?php
	}

	public function footer() { ?>
		</div><!-- .wrap -->
		<?php
	}
}

/**
 * Start
 */
function WPQuickPages() {
	global $WPQuickPages;
	$WPQuickPages = new WPQuickPages();
}
add_action('init', 'WPQuickPages');