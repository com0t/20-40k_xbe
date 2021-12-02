<?php
/**
 * All settings related functions
 */
namespace Codexpert\Image_Sizes;
use Codexpert\Plugin\Base;

/**
 * @package Plugin
 * @subpackage Settings
 * @author codexpert <hello@codexpert.io>
 */
class Settings extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}
	
    public function admin_notices() {

        if( ! current_user_can( 'manage_options' ) ) return;

        if( 1 == 2 ) : // let's hide this
        /**
         * Promotional banners
         */
        $banners = [
			'codesigner'	=> [
				'name'	=> 'CoDesigner',
				'url'	=> 'https://codexpert.io/codesigner/',
			],
			'share-logins'	=> [
				'name'	=> 'Share Logins',
				'url'	=> 'https://share-logins.com',
			],
			'wc-affiliate'	=> [
				'name'	=> 'WC Affiliate',
				'url'	=> 'https://codexpert.io/wc-affiliate/',
			],
		];

		if( isset( $_GET['is-dismiss'] ) && array_key_exists( $_GET['is-dismiss'], $banners ) ) {
			$dismissed = get_option( '_image-sizes_bf21_dismissed' ) ? : [];
			$dismissed[] = sanitize_text_field( $_GET['is-dismiss'] );
			update_option( '_image-sizes_bf21_dismissed', array_unique( $dismissed ) );
		}

        $dismissed = get_option( '_image-sizes_bf21_dismissed' ) ? : [];
        $active_banners = array_values( array_diff( array_keys( $banners ), $dismissed ) );
        
        $rand_index = rand( 0, count( $active_banners ) - 1 );
        $rand_img = false;
        if( isset( $active_banners[ $rand_index ] ) ) {
        	$rand_img = $active_banners[ $rand_index ];
        }

        if( rand( 1, 3 ) == 1 && $rand_img ) {
        	$query_args = [ 'is-dismiss' => $rand_img ];
        	if( count( $_GET ) > 0 ) {
        		$query_args = array_map( 'sanitize_text_field', $_GET ) + $query_args;
        	}
			?>
			<div class="notice notice-success cx-notice cx-shadow is-dismissible is-bf21-wrap">
				<a href="<?php echo add_query_arg( [ 'utm_campaign' => 'is_bf21_banner' ], $banners[ $rand_img ]['url'] ); ?>" target="_blank">
					<img id="is-bf21-img" src="<?php echo plugins_url( "assets/img/promo/{$rand_img}.png", CXIS ); ?>">
				</a>
				<a href="<?php echo add_query_arg( $query_args, '' ); ?>" class="notice-dismiss"><span class="screen-reader-text"></span></a>
			</div>
			<?php
		}
		endif;

        /**
         * Takes to the settins page, only once
         */
        if( get_option( 'image-sizes_regened' ) == 1 ) return;
        if( isset( $_GET['page'] ) && $_GET['page'] == 'image-sizes' ) {
            update_option( 'image-sizes_regened', 1 );
            update_option( 'image-sizes_version', $this->version );
            return;
        }

        $version_updated = get_option( 'image-sizes_configured' ) != '';
        ?>
        <div class="notice notice-success cxis-notice cx-shadow">
        	<?php if( $version_updated ) echo '<i class="notice-dismiss cxis-dismiss" data-meta_key="image-sizes_regened"></i>'; ?>
            <?php echo '<h3>' . sprintf( __( 'Hello %s!', 'image-sizes' ), wp_get_current_user()->display_name ) . '</h3>'; ?>
            <div>
                <?php
                if( $version_updated ) {
                	echo '<p>' . sprintf( __( 'It looks like you already have disabled some thumbnails with the <strong>%s</strong> plugin. Congratulations!', 'image-sizes' ), 'Image Sizes'  ) . '</p>';
                	echo '<p>' . __( 'Do you know, you can now regenerate thumbnaiils of your existing images?', 'image-sizes' ) . '</p>';
                }
                else {
                	echo '<p>' . sprintf( __( 'Thank you for taking your decision to install the <strong>%s</strong> plugin. Congratulations!', 'image-sizes' ), 'Image Sizes' ) . '</p>';
                	echo '<p>' . sprintf( __( 'You can now prevent WordPress from generating unnecessary thumbnails when you upload an image. You just need to select the thumbnail sizes from the settings screen.', 'image-sizes' ), 'Image Sizes' ) . '</p>';
                }
                ?>
            </div>
            <a class="cx-notice-btn" href="<?php echo admin_url( 'upload.php?page=image-sizes' ); ?>">
            	<?php echo $version_updated ? __( 'Click Here To Regenerate Thumbnails', 'image-sizes' ) : __( 'Click Here To Disable Thumbnails', 'image-sizes' ); ?>
            </a>
        </div>
        <?php
    }
	
	public function init_menu() {
		
		$image_sizes = get_option( '_image-sizes', [] );
		$settings = [
			'id'            => $this->slug,
			'label'         => __( 'Image Sizes', 'image-sizes' ),
			'title'         => $this->name,
			'header'        => $this->name,
			'parent'        => 'upload.php',
			'priority'      => 10,
			'capability'    => 'manage_options',
			'icon'          => 'dashicons-image-crop',
			'position'      => '10.5',
			'sections'      => [
				'prevent_image_sizes'	=> 	[
					'id'        => 'prevent_image_sizes',
					'label'     => __( 'Disable Thumbnails', 'image-sizes' ),
					'icon'      => 'dashicons-images-alt2',
					'color'		=> '#4c3f93',
					'sticky'	=> true,
					'content'	=> Helper::get_template( 'disable-sizes', 'views/settings', [ 'image_sizes' => $image_sizes ] ),
					'fields'    => []
				],
				'image-sizes_regenerate'	=> 	[
					'id'        => 'image-sizes_regenerate',
					'label'     => __( 'Regenerate', 'image-sizes' ),
					'icon'      => 'dashicons-format-gallery',
					'color'		=> '#267942',
					'hide_form'	=> true,
					'content'	=> Helper::get_template( 'regenerate-thumbnails', 'views/settings' ),
					'fields'    => []
				],
				'image-sizes_more_plugins'	=> [
					'id'        => 'image-sizes_more_plugins',
					'label'     => __( 'Supercharge', 'image-sizes' ),
					'icon'      => 'dashicons-superhero',
					'color'		=> '#018aff',
					'hide_form'	=> true,
					'content'	=> Helper::get_template( 'more-plugins', 'views/settings' ),
					'fields'    => [],
				],
			],
		];

		new \Codexpert\Plugin\Settings( $settings );
	}
}