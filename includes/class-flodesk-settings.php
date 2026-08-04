<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flodesk_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu_page() {
		add_options_page(
			__( 'Flodesk Settings', 'flodesk-elementor' ),
			__( 'Flodesk Settings', 'flodesk-elementor' ),
			'manage_options',
			'flodesk-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( 'flodesk_settings_group', 'flodesk_api_key', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'flodesk_settings_group' );
				do_settings_sections( 'flodesk_settings_group' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Flodesk API Key', 'flodesk-elementor' ); ?></th>
						<td>
							<input type="password" name="flodesk_api_key" value="<?php echo esc_attr( get_option( 'flodesk_api_key' ) ); ?>" class="regular-text" />
							<p class="description">
								<?php esc_html_e( 'Find your API key in your Flodesk Account Settings > Integrations > API Key.', 'flodesk-elementor' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
