<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Control_Shortcode {

	public function __construct() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	public function register_shortcodes() {
		add_shortcode( 'control_system', array( $this, 'render_dashboard' ) );
		add_shortcode( 'control_policies', array( $this, 'render_policies' ) );
		add_shortcode( 'control_kiosk_registration', array( $this, 'render_kiosk' ) );
	}

	public function render_dashboard() {
		global $wpdb;
		ob_start();

		try {
			if ( ! Control_Auth::is_logged_in() ) {
				include CONTROL_PATH . 'templates/login.php';
				return ob_get_clean();
			}

			$view = isset( $_GET['control_view'] ) ? sanitize_text_field( $_GET['control_view'] ) : 'dashboard';
			$is_admin = Control_Auth::is_admin();

			include CONTROL_PATH . 'templates/header.php';

			$no_access_html = '
			<div style="text-align:center; padding:100px 30px; background:#fff; border-radius:20px; border:1px solid #e2e8f0; max-width:600px; margin: 40px auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
				<div style="width:100px; height:100px; background:#fef2f2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 30px;">
					<span class="dashicons dashicons-shield-lock" style="font-size:50px; width:50px; height:50px;"></span>
				</div>
				<h2 style="font-weight:800; color:#1e293b; margin-bottom:15px;">' . __( 'مرحباً بك في نظام كنترول', 'control' ) . '</h2>
				<p style="color:#64748b; font-size:1.1rem; line-height:1.6; margin-bottom:30px;">' . __( 'ليس لديك الصلاحيات الكافية للوصول إلى لوحة التحكم حالياً.', 'control' ) . '</p>
				<div style="padding:15px; background:#f8fafc; border-radius:12px; border:1px dashed #cbd5e1; color:#475569; font-size:0.9rem;">
					' . __( 'برجاء التواصل مع إدارة النظام أو الدعم الفني لطلب تفعيل صلاحيات الوصول الخاصة بحسابك.', 'control' ) . '
				</div>
			</div>';

			switch ( $view ) {
				case 'users':
					if ( ! Control_Auth::has_permission('users_view') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/users.php';
					}
					break;
				case 'pediatric_records':
					if ( ! Control_Auth::has_permission('pediatric_view_basic') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/pediatric-records.php';
					}
					break;
				case 'patient_view':
					if ( ! Control_Auth::has_permission('pediatric_view_basic') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/patient-view.php';
					}
					break;
				case 'finance':
					if ( ! Control_Auth::has_permission('finance_payroll_view') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/finance-mgmt.php';
					}
					break;
				case 'roles':
					if ( ! Control_Auth::has_permission('roles_manage') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/roles.php';
					}
					break;
				case 'settings':
					if ( ! Control_Auth::has_permission('settings_manage') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/settings.php';
					}
					break;
				default:
					if ( ! Control_Auth::has_permission('dashboard') ) {
						echo $no_access_html;
					} else {
						include CONTROL_PATH . 'templates/dashboard-home.php';
					}
					break;
			}

			include CONTROL_PATH . 'templates/footer.php';
		} catch ( Throwable $e ) {
			if ( ob_get_length() ) {
				ob_end_clean();
			}
			return $this->get_error_fallback_html($e);
		}
		return ob_get_clean();
	}

	public function render_kiosk() {
		ob_start();
		try {
			$template_path = CONTROL_PATH . 'templates/kiosk-registration.php';

			if ( file_exists( $template_path ) ) {
				global $wpdb;
				$is_staff = Control_Auth::is_logged_in();
				$resume_id = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : 0;
				$resume_data = null;

				if ($resume_id && $is_staff) {
					$resume_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patients WHERE id = %d", $resume_id), ARRAY_A);
				}

				include $template_path;
			} else {
				echo '<div style="padding:20px; border:2px dashed #ef4444; color:#b91c1c; background:#fef2f2; border-radius:12px; text-align:center;">';
				echo '<strong>[Control System Error]:</strong> Kiosk registration template not found.';
				echo '</div>';
			}
		} catch ( Throwable $e ) {
			if ( ob_get_length() ) ob_end_clean();
			return $this->get_error_fallback_html($e);
		}

		return ob_get_clean();
	}

	public function render_policies() {
		try {
			global $wpdb;
			$policies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_policies ORDER BY id ASC" );

			if ( empty($policies) || ! is_array($policies) ) return '';

			ob_start();
			?>
			<div class="control-policies-display" style="direction: rtl; text-align: right; font-family: 'Rubik', sans-serif; line-height: 1.8; color: #334155; background: #fff; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 900px; margin: 40px auto;">
				<?php foreach($policies as $policy): ?>
					<div class="policy-item" style="margin-bottom: 40px;">
						<h2 style="color:var(--control-primary); font-weight:800; border-bottom: 2px solid var(--control-bg); padding-bottom:10px; margin-bottom:20px;"><?php echo esc_html($policy->title); ?></h2>
						<div class="policy-content">
							<?php echo wp_kses_post( $policy->content ); ?>
						</div>
					</div>
				<?php endforeach; ?>
				<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #f1f5f9; text-align: center; color: #94a3b8; font-size: 0.8rem;">
					<?php echo sprintf( __('تم تحديث كافة السياسات في: %s', 'control'), date_i18n( get_option('date_format') ) ); ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		} catch ( Throwable $e ) {
			return $this->get_error_fallback_html($e);
		}
	}

	private function get_error_fallback_html( $e ) {
		$is_admin = current_user_can( 'manage_options' ) || Control_Auth::is_admin();

		error_log( '[Control System Critical] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
		error_log( $e->getTraceAsString() );

		$error_html = '<div class="control-error-fallback" style="padding:40px; text-align:center; background:#fff1f2; border:2px solid #fda4af; border-radius:20px; margin:40px auto; max-width:800px; font-family:sans-serif;">';
		$error_html .= '<div style="color:#e11d48; font-size:40px; margin-bottom:20px;"><span class="dashicons dashicons-warning" style="font-size:40px; width:40px; height:40px;"></span></div>';
		$error_html .= '<h2 style="color:#9f1239; margin-bottom:10px;">' . __( 'عذراً، حدث خطأ تقني في النظام', 'control' ) . '</h2>';
		$error_html .= '<p style="color:#be123c; margin-bottom:25px;">' . __( 'نحن نعتذر عن هذا الخلل. يرجى محاولة تحديث الصفحة أو التواصل مع الدعم الفني إذا استمرت المشكلة.', 'control' ) . '</p>';
		$error_html .= '<button onclick="location.reload()" class="control-btn" style="background:#e11d48; color:#fff; border:none; padding:12px 30px; border-radius:12px; font-weight:800; cursor:pointer;">' . __( 'تحديث الصفحة', 'control' ) . '</button>';

		if ( $is_admin || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$error_html .= '<div style="margin-top:30px; padding:20px; background:#fff; border-radius:12px; text-align:left; font-size:13px; font-family:monospace; color:#444; overflow-x:auto; border:1px solid #e2e8f0; box-shadow:inset 0 2px 4px rgba(0,0,0,0.05);">';
			$error_html .= '<strong style="color:#e11d48; display:block; margin-bottom:10px;">[ADMIN DEBUG INFO]:</strong>';
			$error_html .= '<strong>Message:</strong> ' . esc_html( $e->getMessage() ) . '<br>';
			$error_html .= '<strong>File:</strong> ' . esc_html( $e->getFile() ) . '<br>';
			$error_html .= '<strong>Line:</strong> ' . $e->getLine() . '<br>';
			$error_html .= '<details style="margin-top:10px;"><summary style="cursor:pointer; color:var(--control-primary);">Stack Trace</summary><pre style="font-size:11px; margin-top:10px; white-space:pre-wrap;">' . esc_html( $e->getTraceAsString() ) . '</pre></details>';
			$error_html .= '</div>';
		}

		$error_html .= '</div>';
		return $error_html;
	}
}

new Control_Shortcode();
