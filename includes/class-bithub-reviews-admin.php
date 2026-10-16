<?php
/**
 * WordPress administration UI and AJAX proxy.
 *
 * @package BitHubReviewsWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BitHub_Reviews_Admin {
	/** @var BitHub_Reviews_Admin|null */
	private static $instance = null;

	/** @var string */
	private $hook_suffix = '';

	/** @return BitHub_Reviews_Admin */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Constructor. */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$actions = array(
			'bithub_reviews_create_business' => 'ajax_create_business',
			'bithub_reviews_manage_business' => 'ajax_manage_business',
			'bithub_reviews_save_widget'     => 'ajax_save_widget',
			'bithub_reviews_refresh'         => 'ajax_refresh',
			'bithub_reviews_manage_link'     => 'ajax_manage_link',
			'bithub_reviews_disconnect'      => 'ajax_disconnect',
		);

		foreach ( $actions as $action => $method ) {
			add_action( 'wp_ajax_' . $action, array( $this, $method ) );
		}
	}

	/**
	 * Register Tools > BitHub Reviews.
	 *
	 * @return void
	 */
	public function admin_menu() {
		$this->hook_suffix = add_management_page(
			__( 'BitHub Reviews', 'bithub-reviews-widget-for-google' ),
			__( 'BitHub Reviews', 'bithub-reviews-widget-for-google' ),
			'manage_options',
			'bithub-reviews-widget',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Load local CSS/JS only on this plugin's settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'bithub-reviews-admin',
			BITHUB_REVIEWS_URL . 'assets/admin.css',
			array(),
			BITHUB_REVIEWS_VERSION
		);
		wp_enqueue_script(
			'bithub-reviews-admin',
			BITHUB_REVIEWS_URL . 'assets/admin.js',
			array(),
			BITHUB_REVIEWS_VERSION,
			true
		);

		$settings = $this->get_settings();
		wp_localize_script(
			'bithub-reviews-admin',
			'BitHubReviewsAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'bithub_reviews_admin' ),
				'settings'    => $settings,
				'defaults'    => self::default_config(),
				'servicePage' => BITHUB_REVIEWS_SERVICE_PAGE,
				'i18n'        => array(
					'working'          => __( 'Working…', 'bithub-reviews-widget-for-google' ),
					'connected'        => __( 'Business connected. Configure the widget and save it.', 'bithub-reviews-widget-for-google' ),
					'loaded'           => __( 'Existing configuration loaded.', 'bithub-reviews-widget-for-google' ),
					'saved'            => __( 'Widget saved. You can now use the shortcode or block.', 'bithub-reviews-widget-for-google' ),
					'refreshed'        => __( 'Reviews refreshed.', 'bithub-reviews-widget-for-google' ),
					'disconnected'     => __( 'Local connection removed. Remote service data was not deleted.', 'bithub-reviews-widget-for-google' ),
					'confirmDisconnect'=> __( 'Remove the local connection from this WordPress site? The remote BitHub configuration will remain available.', 'bithub-reviews-widget-for-google' ),
					'copyDone'         => __( 'Copied!', 'bithub-reviews-widget-for-google' ),
					'genericError'     => __( 'Something went wrong. Please try again.', 'bithub-reviews-widget-for-google' ),
					'needConsent'      => __( 'Please confirm that you understand which data is sent to the BitHub Reviews service.', 'bithub-reviews-widget-for-google' ),
					'needApiKey'       => __( 'Enter the Google Places API key used for this business.', 'bithub-reviews-widget-for-google' ),
					'needIdentifier'   => __( 'Enter a Google Maps link or Place ID.', 'bithub-reviews-widget-for-google' ),
					'needCreateFields' => __( 'Enter the Google Maps link, email address and Google Places API key.', 'bithub-reviews-widget-for-google' ),
					'liveWidget'       => __( 'Open live widget', 'bithub-reviews-widget-for-google' ),
				),
			)
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings     = $this->get_settings();
		$is_connected = ! empty( $settings['widget_id'] ) && BitHub_Reviews_Renderer::valid_widget_id( $settings['widget_id'] );
		?>
		<div class="wrap bithub-reviews-admin">
			<div class="bithub-reviews-hero">
				<div class="bithub-reviews-brand">
					<img src="<?php echo esc_url( BITHUB_REVIEWS_URL . 'assets/bithub-logo.svg' ); ?>" alt="BitHub" width="150" height="42">
					<span><?php esc_html_e( 'Reviews Widget', 'bithub-reviews-widget-for-google' ); ?></span>
				</div>
				<h1><?php esc_html_e( 'Google reviews, without copy-pasting embed code.', 'bithub-reviews-widget-for-google' ); ?></h1>
				<p><?php esc_html_e( 'Connect a business to the BitHub Reviews service, configure the display, then place it anywhere with a shortcode or Gutenberg block.', 'bithub-reviews-widget-for-google' ); ?></p>
				<div class="bithub-reviews-links">
					<a href="<?php echo esc_url( BITHUB_REVIEWS_DOCS_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Plugin documentation', 'bithub-reviews-widget-for-google' ); ?> ↗</a>
					<a href="<?php echo esc_url( BITHUB_REVIEWS_SERVICE_PAGE ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Web configurator', 'bithub-reviews-widget-for-google' ); ?> ↗</a>
					<a href="<?php echo esc_url( BITHUB_REVIEWS_PRIVACY_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy', 'bithub-reviews-widget-for-google' ); ?> ↗</a>
				</div>
			</div>

			<div id="bithub-reviews-notice" class="bithub-reviews-notice" hidden></div>

			<?php if ( $is_connected ) : ?>
				<section class="bithub-reviews-card bithub-reviews-current" id="bithub-current-widget">
					<div>
						<span class="bithub-reviews-kicker"><?php esc_html_e( 'Connected widget', 'bithub-reviews-widget-for-google' ); ?></span>
						<h2><?php echo esc_html( $settings['business_name'] ?? __( 'Google Maps business', 'bithub-reviews-widget-for-google' ) ); ?></h2>
						<p><code><?php echo esc_html( $settings['widget_id'] ); ?></code></p>
					</div>
					<div class="bithub-reviews-actions">
						<a class="button button-secondary" href="<?php echo esc_url( BITHUB_REVIEWS_SERVICE_BASE . '/widgets/' . rawurlencode( $settings['widget_id'] ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open live widget', 'bithub-reviews-widget-for-google' ); ?></a>
						<button type="button" class="button" id="bithub-disconnect-local"><?php esc_html_e( 'Disconnect from this site', 'bithub-reviews-widget-for-google' ); ?></button>
					</div>
				</section>

				<section class="bithub-reviews-card">
					<span class="bithub-reviews-kicker"><?php esc_html_e( 'Insert it', 'bithub-reviews-widget-for-google' ); ?></span>
					<h2><?php esc_html_e( 'Shortcode or Gutenberg block', 'bithub-reviews-widget-for-google' ); ?></h2>
					<p><?php esc_html_e( 'Use the BitHub Reviews Widget block, or copy this shortcode into any page, post or widget area.', 'bithub-reviews-widget-for-google' ); ?></p>
					<div class="bithub-reviews-copy-row">
						<code id="bithub-shortcode">[bithub_reviews]</code>
						<button type="button" class="button" data-copy="#bithub-shortcode"><?php esc_html_e( 'Copy shortcode', 'bithub-reviews-widget-for-google' ); ?></button>
					</div>
				</section>

				<section class="bithub-reviews-card">
					<span class="bithub-reviews-kicker"><?php esc_html_e( 'Edit remote widget', 'bithub-reviews-widget-for-google' ); ?></span>
					<h2><?php esc_html_e( 'Unlock with your Google Places API key', 'bithub-reviews-widget-for-google' ); ?></h2>
					<p><?php esc_html_e( 'The API key is sent only for this request and is never stored by this WordPress plugin.', 'bithub-reviews-widget-for-google' ); ?></p>
					<div class="bithub-reviews-inline-form">
						<label>
							<span><?php esc_html_e( 'Google Places API key', 'bithub-reviews-widget-for-google' ); ?></span>
							<input type="password" id="bithub-unlock-api-key" autocomplete="off" spellcheck="false">
						</label>
						<button type="button" class="button button-primary" id="bithub-unlock-widget"><?php esc_html_e( 'Unlock editor', 'bithub-reviews-widget-for-google' ); ?></button>
						<button type="button" class="button" id="bithub-send-manage-link"><?php esc_html_e( 'Send management link by email', 'bithub-reviews-widget-for-google' ); ?></button>
					</div>
				</section>
			<?php else : ?>
				<div class="bithub-reviews-grid bithub-reviews-connect-grid">
					<section class="bithub-reviews-card">
						<span class="bithub-reviews-kicker">01 · <?php esc_html_e( 'New widget', 'bithub-reviews-widget-for-google' ); ?></span>
						<h2><?php esc_html_e( 'Connect your Google Maps business', 'bithub-reviews-widget-for-google' ); ?></h2>
						<p><?php esc_html_e( 'Use the share link from the correct Google Maps business profile. The BitHub service resolves the Place ID and retrieves the reviews using your Places API key.', 'bithub-reviews-widget-for-google' ); ?></p>
						<div class="bithub-reviews-form-stack">
							<label>
								<span><?php esc_html_e( 'Google Maps business link', 'bithub-reviews-widget-for-google' ); ?></span>
								<input type="text" id="bithub-create-maps-url" placeholder="https://maps.app.goo.gl/…" autocomplete="off">
							</label>
							<label>
								<span><?php esc_html_e( 'Email address', 'bithub-reviews-widget-for-google' ); ?></span>
								<input type="email" id="bithub-create-email" autocomplete="email">
							</label>
							<label>
								<span><?php esc_html_e( 'Google Places API key', 'bithub-reviews-widget-for-google' ); ?></span>
								<input type="password" id="bithub-create-api-key" autocomplete="off" spellcheck="false">
							</label>
							<label class="bithub-reviews-consent">
								<input type="checkbox" id="bithub-create-consent" value="1">
								<span><?php
								/* translators: %1$s: URL of the BitHub Reviews service page. */
								echo wp_kses_post( sprintf( __( 'I understand that these setup details are sent to the <a href="%1$s" target="_blank" rel="noopener noreferrer">BitHub Reviews service</a>.', 'bithub-reviews-widget-for-google' ), esc_url( BITHUB_REVIEWS_SERVICE_PAGE ) ) );
								?></span>
							</label>
							<button type="button" class="button button-primary button-hero" id="bithub-create-business"><?php esc_html_e( 'Connect and configure', 'bithub-reviews-widget-for-google' ); ?></button>
						</div>
					</section>

					<section class="bithub-reviews-card">
						<span class="bithub-reviews-kicker">02 · <?php esc_html_e( 'Existing widget', 'bithub-reviews-widget-for-google' ); ?></span>
						<h2><?php esc_html_e( 'Connect an existing BitHub configuration', 'bithub-reviews-widget-for-google' ); ?></h2>
						<p><?php esc_html_e( 'Enter the Place ID or the same Maps URL used when the business was first configured, then unlock it with the original Google Places API key.', 'bithub-reviews-widget-for-google' ); ?></p>
						<div class="bithub-reviews-form-stack">
							<label>
								<span><?php esc_html_e( 'Place ID or Google Maps link', 'bithub-reviews-widget-for-google' ); ?></span>
								<input type="text" id="bithub-existing-identifier" autocomplete="off">
							</label>
							<label>
								<span><?php esc_html_e( 'Google Places API key', 'bithub-reviews-widget-for-google' ); ?></span>
								<input type="password" id="bithub-existing-api-key" autocomplete="off" spellcheck="false">
							</label>
							<label class="bithub-reviews-consent">
								<input type="checkbox" id="bithub-existing-consent" value="1">
								<span><?php
								/* translators: %1$s: URL of the BitHub Reviews service page. */
								echo wp_kses_post( sprintf( __( 'I understand that this request is sent to the <a href="%1$s" target="_blank" rel="noopener noreferrer">BitHub Reviews service</a>.', 'bithub-reviews-widget-for-google' ), esc_url( BITHUB_REVIEWS_SERVICE_PAGE ) ) );
								?></span>
							</label>
							<button type="button" class="button button-secondary button-hero" id="bithub-load-existing"><?php esc_html_e( 'Load existing widget', 'bithub-reviews-widget-for-google' ); ?></button>
						</div>
					</section>
				</div>
			<?php endif; ?>

			<section class="bithub-reviews-card bithub-reviews-configurator" id="bithub-configurator" hidden>
				<div class="bithub-reviews-config-head">
					<div>
						<span class="bithub-reviews-kicker"><?php esc_html_e( 'Widget configuration', 'bithub-reviews-widget-for-google' ); ?></span>
						<h2 id="bithub-config-business-name"><?php esc_html_e( 'Reviews widget', 'bithub-reviews-widget-for-google' ); ?></h2>
						<p><?php esc_html_e( 'The management token stays only in this browser page and is not saved in WordPress.', 'bithub-reviews-widget-for-google' ); ?></p>
					</div>
					<div class="bithub-reviews-actions">
						<button type="button" class="button" id="bithub-refresh-reviews"><?php esc_html_e( 'Refresh reviews now', 'bithub-reviews-widget-for-google' ); ?></button>
						<button type="button" class="button button-primary" id="bithub-save-widget"><?php esc_html_e( 'Save widget', 'bithub-reviews-widget-for-google' ); ?></button>
					</div>
				</div>

				<div class="bithub-reviews-config-grid">
					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Layout and filters', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label><span><?php esc_html_e( 'Layout', 'bithub-reviews-widget-for-google' ); ?></span>
							<select data-config="layout">
								<option value="slider"><?php esc_html_e( 'Slider', 'bithub-reviews-widget-for-google' ); ?></option>
								<option value="grid"><?php esc_html_e( 'Grid', 'bithub-reviews-widget-for-google' ); ?></option>
								<option value="list"><?php esc_html_e( 'List', 'bithub-reviews-widget-for-google' ); ?></option>
								<option value="rating"><?php esc_html_e( 'Rating only', 'bithub-reviews-widget-for-google' ); ?></option>
							</select>
						</label>
						<label><span><?php esc_html_e( 'Maximum reviews', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="1" max="500" data-config="max_reviews"></label>
						<label><span><?php esc_html_e( 'Minimum rating', 'bithub-reviews-widget-for-google' ); ?></span><select data-config="min_rating"><option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4">4+</option><option value="5">5</option></select></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Responsive visibility', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label><span><?php esc_html_e( 'Desktop', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="1" max="8" data-config="visible.desktop"></label>
						<label><span><?php esc_html_e( 'Tablet', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="1" max="6" data-config="visible.tablet"></label>
						<label><span><?php esc_html_e( 'Mobile', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="1" max="3" data-config="visible.mobile"></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Review text', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="text.show"><span><?php esc_html_e( 'Show review text', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label><span><?php esc_html_e( 'Maximum text length', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="60" max="4000" data-config="text.max_length"></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="text.read_more"><span><?php esc_html_e( 'Show “Read more”', 'bithub-reviews-widget-for-google' ); ?></span></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Author', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="author.show_photo"><span><?php esc_html_e( 'Show author photo', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="author.show_name"><span><?php esc_html_e( 'Show author name', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="author.shorten_surname"><span><?php esc_html_e( 'Shorten surname', 'bithub-reviews-widget-for-google' ); ?></span></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Header', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="header.show"><span><?php esc_html_e( 'Show header', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="header.show_business_name"><span><?php esc_html_e( 'Show business name', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="header.show_rating"><span><?php esc_html_e( 'Show average rating', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="header.show_review_count"><span><?php esc_html_e( 'Show total review count', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="header.show_write_review"><span><?php esc_html_e( 'Show “Write a review” button', 'bithub-reviews-widget-for-google' ); ?></span></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Slider', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="slider.autoplay"><span><?php esc_html_e( 'Autoplay', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label><span><?php esc_html_e( 'Autoplay interval (ms)', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="1500" max="30000" step="100" data-config="slider.interval"></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="slider.pause_on_hover"><span><?php esc_html_e( 'Pause on hover', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="slider.arrows"><span><?php esc_html_e( 'Show arrows', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="slider.dots"><span><?php esc_html_e( 'Show dots', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label><span><?php esc_html_e( 'Transition duration (ms)', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="100" max="2000" step="50" data-config="slider.transition_ms"></label>
					</div>

					<div class="bithub-reviews-config-section">
						<h3><?php esc_html_e( 'Appearance', 'bithub-reviews-widget-for-google' ); ?></h3>
						<label><span><?php esc_html_e( 'Theme', 'bithub-reviews-widget-for-google' ); ?></span><select data-config="theme.mode"><option value="automatic"><?php esc_html_e( 'Automatic', 'bithub-reviews-widget-for-google' ); ?></option><option value="light"><?php esc_html_e( 'Light', 'bithub-reviews-widget-for-google' ); ?></option><option value="dark"><?php esc_html_e( 'Dark', 'bithub-reviews-widget-for-google' ); ?></option></select></label>
						<label><span><?php esc_html_e( 'Star color', 'bithub-reviews-widget-for-google' ); ?></span><input type="color" data-config="theme.star_color"></label>
						<label><span><?php esc_html_e( 'Text color', 'bithub-reviews-widget-for-google' ); ?></span><input type="color" data-config="theme.text_color"></label>
						<label><span><?php esc_html_e( 'Background color', 'bithub-reviews-widget-for-google' ); ?></span><input type="color" data-config="theme.background_color"></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="theme.border"><span><?php esc_html_e( 'Border', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label class="bithub-reviews-check"><input type="checkbox" data-config="theme.shadow"><span><?php esc_html_e( 'Shadow', 'bithub-reviews-widget-for-google' ); ?></span></label>
						<label><span><?php esc_html_e( 'Corner radius', 'bithub-reviews-widget-for-google' ); ?></span><input type="number" min="0" max="40" data-config="theme.radius"></label>
					</div>
				</div>

				<div class="bithub-reviews-after-save" id="bithub-after-save" hidden>
					<h3><?php esc_html_e( 'Ready to publish', 'bithub-reviews-widget-for-google' ); ?></h3>
					<p><?php esc_html_e( 'Add the BitHub Reviews Widget block or use:', 'bithub-reviews-widget-for-google' ); ?></p>
					<div class="bithub-reviews-copy-row">
						<code id="bithub-shortcode-after-save">[bithub_reviews]</code>
						<button type="button" class="button" data-copy="#bithub-shortcode-after-save"><?php esc_html_e( 'Copy shortcode', 'bithub-reviews-widget-for-google' ); ?></button>
						<a class="button" id="bithub-live-widget-link" href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open live widget', 'bithub-reviews-widget-for-google' ); ?></a>
					</div>
				</div>
			</section>

			<section class="bithub-reviews-service-note">
				<strong><?php esc_html_e( 'External service disclosure', 'bithub-reviews-widget-for-google' ); ?></strong>
				<p><?php esc_html_e( 'This plugin is an interface to the BitHub Reviews service. During setup and management, the service receives the business link or Place ID, email, Google Places API key, language and widget configuration. On public pages containing the widget, the browser loads the widget JavaScript from extractor.able2code.com.', 'bithub-reviews-widget-for-google' ); ?></p>
				<p><a href="<?php echo esc_url( BITHUB_REVIEWS_SERVICE_PAGE ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Service documentation', 'bithub-reviews-widget-for-google' ); ?></a> · <a href="<?php echo esc_url( BITHUB_REVIEWS_PRIVACY_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'BitHub privacy information', 'bithub-reviews-widget-for-google' ); ?></a></p>
			</section>
		</div>
		<?php
	}

	/** AJAX: create a new remote business configuration. */
	public function ajax_create_business() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );

		$maps_url = isset( $_POST['maps_url'] ) ? sanitize_text_field( wp_unslash( $_POST['maps_url'] ) ) : '';
		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$consent  = ! empty( $_POST['consent'] );

		if ( ! $consent ) {
			$this->send_error( 'consent_required', __( 'Please confirm the external service disclosure.', 'bithub-reviews-widget-for-google' ), 400 );
		}
		if ( strlen( $maps_url ) < 8 || ! is_email( $email ) || strlen( $api_key ) < 10 ) {
			$this->send_error( 'invalid_fields', __( 'Check the Google Maps link, email address and API key.', 'bithub-reviews-widget-for-google' ), 422 );
		}

		$result = BitHub_Reviews_API_Client::request(
			'POST',
			'/v1/google-reviews/businesses',
			array(
				'google_maps_url' => $maps_url,
				'email'           => $email,
				'google_api_key'  => $api_key,
				'language'        => self::service_language(),
			)
		);

		$this->send_api_result( $result );
	}

	/** AJAX: load/manage an existing remote configuration. */
	public function ajax_manage_business() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );

		$identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( wp_unslash( $_POST['identifier'] ) ) : '';
		$api_key    = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$consent    = ! isset( $_POST['consent'] ) || ! empty( $_POST['consent'] );

		if ( ! $consent ) {
			$this->send_error( 'consent_required', __( 'Please confirm the external service disclosure.', 'bithub-reviews-widget-for-google' ), 400 );
		}
		if ( strlen( $identifier ) < 3 || strlen( $api_key ) < 10 ) {
			$this->send_error( 'invalid_fields', __( 'Enter a Place ID or Maps link and the original Google Places API key.', 'bithub-reviews-widget-for-google' ), 422 );
		}

		$business_id = $identifier;
		if ( false !== strpos( $identifier, '://' ) ) {
			$lookup = BitHub_Reviews_API_Client::request(
				'POST',
				'/v1/google-reviews/businesses/lookup',
				array( 'google_maps_url' => $identifier )
			);
			if ( is_wp_error( $lookup ) ) {
				$this->send_api_result( $lookup );
			}
			if ( empty( $lookup['exists'] ) || empty( $lookup['business_id'] ) ) {
				$this->send_error( 'business_not_found', __( 'No BitHub Reviews configuration was found for that business.', 'bithub-reviews-widget-for-google' ), 404 );
			}
			$business_id = sanitize_text_field( $lookup['business_id'] );
		}

		$result = BitHub_Reviews_API_Client::request(
			'POST',
			'/v1/google-reviews/businesses/' . rawurlencode( $business_id ) . '/manage',
			array( 'google_api_key' => $api_key )
		);

		if ( ! is_wp_error( $result ) && ! empty( $result['widget']['widget_id'] ) && BitHub_Reviews_Renderer::valid_widget_id( $result['widget']['widget_id'] ) ) {
			$this->store_remote_widget( $result['business'] ?? array(), $result['widget'] );
		}

		$this->send_api_result( $result );
	}

	/** AJAX: save or update widget configuration. */
	public function ajax_save_widget() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );

		$token       = isset( $_POST['manage_token'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_token'] ) ) : '';
		$business_id = isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';
		$widget_id   = isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : '';
		$config_raw  = isset( $_POST['config'] ) ? sanitize_textarea_field( wp_unslash( $_POST['config'] ) ) : '';
		$config      = json_decode( $config_raw, true );

		if ( strlen( $token ) < 16 || strlen( $business_id ) < 3 || ! is_array( $config ) ) {
			$this->send_error( 'invalid_request', __( 'The editor session is incomplete. Unlock the widget again and retry.', 'bithub-reviews-widget-for-google' ), 422 );
		}

		$config = self::sanitize_config( $config );

		if ( '' !== $widget_id ) {
			if ( ! BitHub_Reviews_Renderer::valid_widget_id( $widget_id ) ) {
				$this->send_error( 'invalid_widget_id', __( 'The widget identifier is not valid.', 'bithub-reviews-widget-for-google' ), 422 );
			}
			$result = BitHub_Reviews_API_Client::request(
				'PUT',
				'/v1/google-reviews/widgets/' . rawurlencode( $widget_id ),
				array( 'config' => $config ),
				$token
			);
		} else {
			$result = BitHub_Reviews_API_Client::request(
				'POST',
				'/v1/google-reviews/widgets',
				array(
					'business_id' => $business_id,
					'config'      => $config,
				),
				$token
			);
		}

		if ( is_wp_error( $result ) ) {
			$this->send_api_result( $result );
		}

		$business_name = isset( $_POST['business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['business_name'] ) ) : '';
		$business      = array(
			'business_id' => $business_id,
			'name'        => $business_name,
		);
		$this->store_remote_widget( $business, $result );
		wp_send_json_success( $result );
	}

	/** AJAX: force a service refresh while the management token is valid. */
	public function ajax_refresh() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );
		$token       = isset( $_POST['manage_token'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_token'] ) ) : '';
		$business_id = isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';
		if ( strlen( $token ) < 16 || strlen( $business_id ) < 3 ) {
			$this->send_error( 'invalid_request', __( 'Unlock the widget again before refreshing reviews.', 'bithub-reviews-widget-for-google' ), 422 );
		}

		$result = BitHub_Reviews_API_Client::request(
			'POST',
			'/v1/google-reviews/businesses/' . rawurlencode( $business_id ) . '/refresh',
			array(),
			$token
		);
		$this->send_api_result( $result );
	}

	/** AJAX: request the existing service to email a management link. */
	public function ajax_manage_link() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );
		$settings    = $this->get_settings();
		$business_id = isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : ( $settings['business_id'] ?? '' );
		if ( strlen( $business_id ) < 3 ) {
			$this->send_error( 'business_not_found', __( 'No connected business was found.', 'bithub-reviews-widget-for-google' ), 404 );
		}

		$result = BitHub_Reviews_API_Client::request(
			'POST',
			'/v1/google-reviews/businesses/' . rawurlencode( $business_id ) . '/manage-link',
			array()
		);
		$this->send_api_result( $result );
	}

	/** AJAX: remove only this WordPress site's local connection. */
	public function ajax_disconnect() {
		$this->guard_ajax();
		check_ajax_referer( 'bithub_reviews_admin', 'nonce' );
		update_option( BITHUB_REVIEWS_OPTION, array(), false );
		wp_send_json_success( array( 'ok' => true ) );
	}

	/**
	 * Verify the administrator capability for AJAX operations.
	 *
	 * @return void
	 */
	private function guard_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to manage this widget.', 'bithub-reviews-widget-for-google' ) ), 403 );
		}
	}

	/**
	 * Convert API result to a standard wp_send_json response.
	 *
	 * @param array|WP_Error $result Result.
	 * @return void
	 */
	private function send_api_result( $result ) {
		if ( ! is_wp_error( $result ) ) {
			wp_send_json_success( $result );
		}

		$code    = $result->get_error_code();
		$message = self::friendly_error_message( $code, $result->get_error_message() );
		$data    = $result->get_error_data();
		$status  = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		$payload = is_array( $data ) && isset( $data['payload'] ) && is_array( $data['payload'] ) ? $data['payload'] : array();

		wp_send_json_error(
			array_merge(
				array(
					'code'    => $code,
					'message' => $message,
				),
				$payload
			),
			$status > 0 ? $status : 400
		);
	}

	/**
	 * Send a local validation error.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status.
	 * @return void
	 */
	private function send_error( $code, $message, $status ) {
		wp_send_json_error(
			array(
				'code'    => sanitize_key( $code ),
				'message' => $message,
			),
			(int) $status
		);
	}

	/**
	 * Localize common service errors rather than exposing backend-language text.
	 *
	 * @param string $code     Error code.
	 * @param string $fallback Fallback message.
	 * @return string
	 */
	private static function friendly_error_message( $code, $fallback ) {
		$messages = array(
			'business_exists'                   => __( 'This business is already configured in BitHub Reviews. Use the existing-widget form instead.', 'bithub-reviews-widget-for-google' ),
			'business_not_found'                => __( 'No BitHub Reviews configuration was found for this business.', 'bithub-reviews-widget-for-google' ),
			'invalid_api_key'                   => __( 'The Google Places API key does not match the saved configuration.', 'bithub-reviews-widget-for-google' ),
			'api_key_mismatch'                  => __( 'The Google Places API key does not match the saved configuration.', 'bithub-reviews-widget-for-google' ),
			'invalid_manage_token'              => __( 'The editor session has expired. Unlock the widget again.', 'bithub-reviews-widget-for-google' ),
			'manage_token_required'             => __( 'The editor session is missing. Unlock the widget again.', 'bithub-reviews-widget-for-google' ),
			'manage_token_business_mismatch'    => __( 'The editor session does not belong to this business.', 'bithub-reviews-widget-for-google' ),
			'widget_not_found'                  => __( 'The remote widget was not found.', 'bithub-reviews-widget-for-google' ),
			'email_not_configured'              => __( 'Email management is temporarily unavailable on the BitHub service.', 'bithub-reviews-widget-for-google' ),
			'email_send_failed'                 => __( 'The management email could not be sent. Please try again later.', 'bithub-reviews-widget-for-google' ),
			'bithub_reviews_service_unreachable'=> __( 'The BitHub Reviews service could not be reached. Please try again.', 'bithub-reviews-widget-for-google' ),
			'bithub_reviews_invalid_response'   => __( 'The BitHub Reviews service returned an invalid response.', 'bithub-reviews-widget-for-google' ),
		);
		return isset( $messages[ $code ] ) ? $messages[ $code ] : $fallback;
	}

	/**
	 * Store non-sensitive local connection information only.
	 *
	 * @param array $business Business payload.
	 * @param array $widget   Widget payload.
	 * @return void
	 */
	private function store_remote_widget( $business, $widget ) {
		if ( empty( $widget['widget_id'] ) || ! BitHub_Reviews_Renderer::valid_widget_id( $widget['widget_id'] ) ) {
			return;
		}

		$business_id   = isset( $business['business_id'] ) ? sanitize_text_field( $business['business_id'] ) : '';
		$business_name = isset( $business['name'] ) ? sanitize_text_field( $business['name'] ) : '';
		$config        = isset( $widget['config'] ) && is_array( $widget['config'] ) ? self::sanitize_config( $widget['config'] ) : self::default_config();

		update_option(
			BITHUB_REVIEWS_OPTION,
			array(
				'business_id'   => $business_id,
				'business_name' => $business_name,
				'widget_id'     => sanitize_text_field( $widget['widget_id'] ),
				'config'        => $config,
				'updated_at'    => gmdate( 'c' ),
			),
			false
		);
	}

	/** @return array */
	private function get_settings() {
		$settings = get_option( BITHUB_REVIEWS_OPTION, array() );
		return is_array( $settings ) ? $settings : array();
	}

	/** @return string */
	private static function service_language() {
		$locale = determine_locale();
		return 0 === strpos( strtolower( $locale ), 'it' ) ? 'it' : 'en';
	}

	/**
	 * Canonical service defaults.
	 *
	 * @return array
	 */
	public static function default_config() {
		return array(
			'layout'      => 'slider',
			'max_reviews' => 10,
			'min_rating'  => 1,
			'visible'     => array( 'desktop' => 3, 'tablet' => 2, 'mobile' => 1 ),
			'text'        => array( 'show' => true, 'max_length' => 260, 'read_more' => true ),
			'author'      => array( 'show_photo' => true, 'show_name' => true, 'shorten_surname' => false ),
			'header'      => array(
				'show'              => true,
				'show_business_name'=> true,
				'show_rating'       => true,
				'show_review_count' => true,
				'show_write_review' => true,
			),
			'slider'      => array(
				'autoplay'       => true,
				'interval'       => 5000,
				'pause_on_hover' => true,
				'arrows'         => true,
				'dots'           => true,
				'transition_ms'  => 350,
			),
			'theme'       => array(
				'mode'             => 'light',
				'star_color'       => '#fbbc04',
				'text_color'       => '#202124',
				'background_color' => '#ffffff',
				'border'           => true,
				'shadow'           => true,
				'radius'           => 14,
			),
		);
	}

	/**
	 * Whitelist and clamp remote widget configuration.
	 *
	 * @param array $config Raw configuration.
	 * @return array
	 */
	public static function sanitize_config( $config ) {
		$defaults = self::default_config();
		$layout   = isset( $config['layout'] ) ? sanitize_key( $config['layout'] ) : $defaults['layout'];
		if ( ! in_array( $layout, array( 'slider', 'grid', 'list', 'rating' ), true ) ) {
			$layout = 'slider';
		}

		$theme_mode = isset( $config['theme']['mode'] ) ? sanitize_key( $config['theme']['mode'] ) : 'light';
		if ( ! in_array( $theme_mode, array( 'automatic', 'light', 'dark' ), true ) ) {
			$theme_mode = 'light';
		}

		return array(
			'layout'      => $layout,
			'max_reviews' => self::clamp_int( $config['max_reviews'] ?? 10, 1, 500, 10 ),
			'min_rating'  => self::clamp_int( $config['min_rating'] ?? 1, 1, 5, 1 ),
			'visible'     => array(
				'desktop' => self::clamp_int( $config['visible']['desktop'] ?? 3, 1, 8, 3 ),
				'tablet'  => self::clamp_int( $config['visible']['tablet'] ?? 2, 1, 6, 2 ),
				'mobile'  => self::clamp_int( $config['visible']['mobile'] ?? 1, 1, 3, 1 ),
			),
			'text'        => array(
				'show'       => self::bool_value( $config['text']['show'] ?? true ),
				'max_length' => self::clamp_int( $config['text']['max_length'] ?? 260, 60, 4000, 260 ),
				'read_more'  => self::bool_value( $config['text']['read_more'] ?? true ),
			),
			'author'      => array(
				'show_photo'      => self::bool_value( $config['author']['show_photo'] ?? true ),
				'show_name'       => self::bool_value( $config['author']['show_name'] ?? true ),
				'shorten_surname' => self::bool_value( $config['author']['shorten_surname'] ?? false ),
			),
			'header'      => array(
				'show'               => self::bool_value( $config['header']['show'] ?? true ),
				'show_business_name' => self::bool_value( $config['header']['show_business_name'] ?? true ),
				'show_rating'        => self::bool_value( $config['header']['show_rating'] ?? true ),
				'show_review_count'  => self::bool_value( $config['header']['show_review_count'] ?? true ),
				'show_write_review'  => self::bool_value( $config['header']['show_write_review'] ?? true ),
			),
			'slider'      => array(
				'autoplay'       => self::bool_value( $config['slider']['autoplay'] ?? true ),
				'interval'       => self::clamp_int( $config['slider']['interval'] ?? 5000, 1500, 30000, 5000 ),
				'pause_on_hover' => self::bool_value( $config['slider']['pause_on_hover'] ?? true ),
				'arrows'         => self::bool_value( $config['slider']['arrows'] ?? true ),
				'dots'           => self::bool_value( $config['slider']['dots'] ?? true ),
				'transition_ms'  => self::clamp_int( $config['slider']['transition_ms'] ?? 350, 100, 2000, 350 ),
			),
			'theme'       => array(
				'mode'             => $theme_mode,
				'star_color'       => self::hex_color( $config['theme']['star_color'] ?? '#fbbc04', '#fbbc04' ),
				'text_color'       => self::hex_color( $config['theme']['text_color'] ?? '#202124', '#202124' ),
				'background_color' => self::hex_color( $config['theme']['background_color'] ?? '#ffffff', '#ffffff' ),
				'border'           => self::bool_value( $config['theme']['border'] ?? true ),
				'shadow'           => self::bool_value( $config['theme']['shadow'] ?? true ),
				'radius'           => self::clamp_int( $config['theme']['radius'] ?? 14, 0, 40, 14 ),
			),
		);
	}

	/** @return int */
	private static function clamp_int( $value, $min, $max, $default ) {
		if ( ! is_numeric( $value ) ) {
			return (int) $default;
		}
		$value = (int) $value;
		return max( (int) $min, min( (int) $max, $value ) );
	}

	/** @return bool */
	private static function bool_value( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/** @return string */
	private static function hex_color( $value, $default ) {
		$value = sanitize_hex_color( (string) $value );
		return $value ? $value : $default;
	}
}
