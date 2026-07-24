<?php
/**
 * Cookie Manager — handles cookie consent banner, cookie policy page shortcode, and cookie preference storage.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CookieManager {

	/**
	 * Initialize the Cookie Manager.
	 */
	public static function init(): void {
		// Shortcode for cookie policy page content
		add_shortcode( 'rrc_cookie_policy', [ self::class, 'render_cookie_policy_page' ] );

		// Hook to create page on theme setup/init
		add_action( 'init', [ self::class, 'ensure_cookie_policy_page' ] );

		// Footer banner
		add_action( 'wp_footer', [ self::class, 'render_cookie_consent_banner' ] );

		// Enqueue styles/scripts
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
	}

	/**
	 * Ensure the Cookie Policy page exists in WordPress.
	 */
	public static function ensure_cookie_policy_page(): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// Avoid checking on every load, run only occasionally or on a transient flag
		if ( get_transient( 'rrc_cookie_page_checked' ) ) {
			return;
		}

		$slug = 'politica-de-cookies';
		$page = get_page_by_path( $slug );

		if ( ! $page ) {
			$post_id = wp_insert_post([
				'post_title'   => 'Política de Cookies',
				'post_name'    => $slug,
				'post_content' => '[rrc_cookie_policy]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
			]);

			if ( ! is_wp_error( $post_id ) ) {
				// Mark as draft pending legal review as requested
				wp_update_post([
					'ID'          => $post_id,
					'post_status' => 'draft'
				]);
			}
		}

		set_transient( 'rrc_cookie_page_checked', true, DAY_IN_SECONDS );
	}

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets(): void {
		wp_enqueue_style(
			'rrc-cookie-manager',
			BTMAT_URL . 'assets/frontend/css/cookie-manager.css',
			[],
			BTMAT_VERSION
		);

		wp_enqueue_script(
			'rrc-cookie-manager',
			BTMAT_URL . 'assets/frontend/js/cookie-manager.js',
			[ 'jquery' ],
			BTMAT_VERSION,
			true
		);

		wp_localize_script( 'rrc-cookie-manager', 'rrcCookieConfig', [
			'policyUrl' => home_url( '/politica-de-cookies/' ),
		] );
	}

	/**
	 * Render the Cookie Policy shortcode content.
	 */
	public static function render_cookie_policy_page(): string {
		ob_start();
		?>
		<div class="rrc-cookie-policy-container">
			<!-- Breadcrumbs -->
			<div class="rrc-cookie-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a> &gt; <span>Legal</span> &gt; <span class="active">Política de Cookies</span>
			</div>

			<!-- Hero Section -->
			<div class="rrc-cookie-hero">
				<div class="rrc-cookie-hero-content">
					<h1>Política de Cookies</h1>
					<p>En Ramirez Rent A Car utilizamos cookies y tecnologías similares para mejorar tu experiencia, analizar el uso de la web y ofrecerte contenidos personalizados.</p>
					<div class="rrc-cookie-hero-meta">Última actualización: 20 de julio de 2026</div>
					<div class="rrc-cookie-hero-actions">
						<button class="rrc-btn-primary" id="rrc-open-panel-btn">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
							Configurar cookies
						</button>
						<a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>" class="rrc-btn-secondary">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							Política de Privacidad
						</a>
					</div>
				</div>
				<div class="rrc-cookie-hero-image">
					<div class="rrc-cookie-circle">
						<img src="<?php echo esc_url( BTMAT_URL . 'assets/frontend/images/cookie-graphic.png' ); ?>" alt="Cookies Icon">
					</div>
				</div>
			</div>

			<!-- Grid Row for Cards -->
			<div class="rrc-cookie-features-grid">
				<div class="rrc-cookie-feature-card">
					<div class="card-icon secure">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
					</div>
					<h3>Cookies necesarias</h3>
					<p>Imprescindibles para el funcionamiento básico del sitio web y la seguridad.</p>
				</div>
				<div class="rrc-cookie-feature-card">
					<div class="card-icon preference">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
					</div>
					<h3>Preferencias</h3>
					<p>Permiten recordar tus elecciones y personalizar tu experiencia.</p>
				</div>
				<div class="rrc-cookie-feature-card">
					<div class="card-icon analytic">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
					</div>
					<h3>Analítica</h3>
					<p>Nos ayudan a entender cómo usas la web para mejorar nuestros servicios.</p>
				</div>
				<div class="rrc-cookie-feature-card">
					<div class="card-icon marketing">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
					</div>
					<h3>Marketing</h3>
					<p>Se utilizan para mostrarte publicidad relevante y medir su eficacia.</p>
				</div>
			</div>

			<!-- Main Content Row (Two Columns) -->
			<div class="rrc-cookie-main-row">
				<!-- Left Column: FAQ & Detailed Info -->
				<div class="rrc-cookie-info-column">
					<div class="rrc-cookie-section-card">
						<div class="section-title">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							<h4>Introducción</h4>
						</div>
						<p>Esta Política de Cookies explica qué son las cookies, cómo las utilizamos en nuestro sitio web y las opciones que tienes para administrarlas. Al continuar navegando, aceptas el uso de cookies de acuerdo con esta política.</p>
					</div>

					<div class="rrc-cookie-section-card">
						<div class="section-title">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
							<h4>¿Qué son las cookies?</h4>
						</div>
						<p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Permiten que el sitio funcione correctamente, recuerdan tus preferencias y nos ayudan a ofrecerte una mejor experiencia de navegación.</p>
					</div>

					<div class="rrc-cookie-section-card">
						<div class="section-title">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path></svg>
							<h4>¿Qué cookies utilizamos?</h4>
						</div>
						<p>A continuación, puedes consultar las cookies que pueden instalarse en tu dispositivo al visitar nuestro sitio web.</p>
						
						<div class="rrc-cookie-table-wrapper">
							<table class="rrc-cookie-table">
								<thead>
									<tr>
										<th>Cookie</th>
										<th>Proveedor</th>
										<th>Categoría</th>
										<th>Finalidad</th>
										<th>Duración</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><strong>btmat_language</strong></td>
										<td>ramirezrentacar.com</td>
										<td>Funcional</td>
										<td>Recordar el idioma seleccionado</td>
										<td>12 meses</td>
									</tr>
									<tr>
										<td><strong>rrc_consent_settings</strong></td>
										<td>ramirezrentacar.com</td>
										<td>Necesaria</td>
										<td>Almacena tus preferencias de cookies y privacidad</td>
										<td>12 meses</td>
									</tr>
									<tr>
										<td><strong>rc_session</strong></td>
										<td>ramirezrentacar.com</td>
										<td>Necesaria</td>
										<td>Mantiene tu sesión de reserva activa durante la navegación</td>
										<td>Sesión</td>
									</tr>
									<tr>
										<td><strong>_wp_nonce</strong></td>
										<td>WordPress</td>
										<td>Necesaria</td>
										<td>Proteger formularios contra falsificaciones y solicitudes maliciosas</td>
										<td>Sesión</td>
									</tr>
									<tr>
										<td><strong>_ga</strong></td>
										<td>Google Analytics</td>
										<td>Analítica</td>
										<td>Distingue usuarios únicos para análisis estadístico anónimo</td>
										<td>24 meses</td>
									</tr>
									<tr>
										<td><strong>_gid</strong></td>
										<td>Google Analytics</td>
										<td>Analítica</td>
										<td>Gestiona la tasa de solicitudes en la página de analítica</td>
										<td>24 horas</td>
									</tr>
									<tr>
										<td><strong>_fbp</strong></td>
										<td>Facebook / Meta</td>
										<td>Marketing</td>
										<td>Se utiliza para medir campañas de marketing y ofrecer publicidad relevante</td>
										<td>3 meses</td>
									</tr>
								</tbody>
							</table>
						</div>
						<p class="rrc-table-footnote">Ten en cuenta que terceros (como Google o Meta) pueden instalar cookies a través de nuestros servicios. Consulta sus <strong>políticas</strong> para más información.</p>
					</div>
				</div>

				<!-- Right Column: Interactive Preference Panel -->
				<div class="rrc-cookie-panel-column" id="rrc-cookie-preference-panel">
					<div class="rrc-cookie-preferences-card">
						<h4>Gestiona tus preferencias</h4>
						<p>Puedes activar o desactivar las categorías de cookies excepto las necesarias, que son siempre esenciales.</p>

						<div class="rrc-preference-item">
							<div class="rrc-preference-info">
								<div class="rrc-preference-title">
									Necesarias
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
								</div>
								<div class="rrc-preference-desc">Siempre activas. Necesarias para el funcionamiento del sitio web.</div>
							</div>
							<div class="rrc-switch-wrapper">
								<label class="rrc-switch">
									<input type="checkbox" checked disabled>
									<span class="rrc-slider round"></span>
								</label>
							</div>
						</div>

						<div class="rrc-preference-item">
							<div class="rrc-preference-info">
								<div class="rrc-preference-title">Funcionales</div>
								<div class="rrc-preference-desc">Permiten recordar tus preferencias y mejorar tu experiencia.</div>
							</div>
							<div class="rrc-switch-wrapper">
								<label class="rrc-switch">
									<input type="checkbox" id="rrc-pref-functional">
									<span class="rrc-slider round"></span>
								</label>
							</div>
						</div>

						<div class="rrc-preference-item">
							<div class="rrc-preference-info">
								<div class="rrc-preference-title">Analíticas</div>
								<div class="rrc-preference-desc">Nos ayudan a entender cómo interactúas con nuestro sitio.</div>
							</div>
							<div class="rrc-switch-wrapper">
								<label class="rrc-switch">
									<input type="checkbox" id="rrc-pref-analytics">
									<span class="rrc-slider round"></span>
								</label>
							</div>
						</div>

						<div class="rrc-preference-item">
							<div class="rrc-preference-info">
								<div class="rrc-preference-title">Marketing</div>
								<div class="rrc-preference-desc">Utilizadas para mostrarte publicidad relevante y medir su eficacia.</div>
							</div>
							<div class="rrc-switch-wrapper">
								<label class="rrc-switch">
									<input type="checkbox" id="rrc-pref-marketing">
									<span class="rrc-slider round"></span>
								</label>
							</div>
						</div>

						<div class="rrc-preferences-actions">
							<button class="rrc-btn-save-pref" id="rrc-save-preferences-btn">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								Guardar preferencias
							</button>
							<button class="rrc-btn-accept-all" id="rrc-accept-all-pref-btn">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
								Aceptar todas
							</button>
							<button class="rrc-btn-reject-all" id="rrc-reject-all-pref-btn">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
								Rechazar las no necesarias
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer Help Banner -->
			<div class="rrc-cookie-footer-help">
				<div class="rrc-cookie-help-card">
					<div class="help-header">
						<div class="help-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
						</div>
						<div class="help-title-block">
							<h4>¿Tienes alguna duda?</h4>
							<p>Estamos aquí para ayudarte. Contacta con nuestro equipo y resolveremos cualquier pregunta sobre nuestras cookies.</p>
						</div>
					</div>
					<div class="help-contacts">
						<div class="contact-item">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
							<div>
								<span>Llámanos</span>
								<strong>(504) 24-45-01-58</strong>
							</div>
						</div>
						<div class="contact-item">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
							<div>
								<span>Escríbenos</span>
								<strong>info@ramirezrentacar.com</strong>
							</div>
						</div>
						<div class="contact-item">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
							<div>
								<span>Horario de atención</span>
								<strong>Lun - Vie: 08:00 - 18:00</strong>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the Cookie Consent Pop-up Banner in the footer.
	 */
	public static function render_cookie_consent_banner(): void {
		// Only display on frontend
		if ( is_admin() ) {
			return;
		}
		?>
		<div id="rrc-cookie-consent-banner" class="rrc-cookie-banner" style="display: none;">
			<div class="rrc-cookie-banner-content">
				<div class="rrc-cookie-banner-text">
					<h5>Tu privacidad es importante</h5>
					<p>Utilizamos cookies necesarias para que el sitio y las reservas funcionen. Con tu autorización, también podemos utilizar cookies funcionales, de analítica y marketing para mejorar tu experiencia y medir nuestras campañas. Puedes aceptar todas, rechazarlas o personalizar tus preferencias.</p>
				</div>
				<div class="rrc-cookie-banner-actions">
					<button class="rrc-cookie-btn accept" id="rrc-banner-accept-all">Aceptar todas</button>
					<button class="rrc-cookie-btn reject" id="rrc-banner-reject-all">Rechazar las no necesarias</button>
					<button class="rrc-cookie-btn settings" id="rrc-banner-settings">Personalizar</button>
				</div>
				<div class="rrc-cookie-banner-links">
					<a href="<?php echo esc_url( home_url( '/politica-de-cookies/' ) ); ?>">Política de Cookies</a>
					<a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>">Política de Privacidad</a>
				</div>
			</div>
		</div>
		<?php
	}
}
