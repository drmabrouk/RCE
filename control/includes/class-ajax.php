<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Control_Ajax {

	public function __construct() {
		// Private actions (Logged-in only)
		$private_actions = array(
			'logout', 'add_user', 'save_user', 'delete_user', 'save_settings',
			'get_patient',
			'update_profile', 'undo_activity', 'delete_activity', 'toggle_user_restriction',
			'bulk_delete_users', 'bulk_restrict_users',
			'export_data', 'import_data',
			'preview_import', 'create_backup', 'restore_backup',
			'export_user_package', 'bulk_delete_all_users', 'system_data_reset',
			'save_role', 'delete_role',
			'save_policy', 'delete_policy',
			'get_user_insights', 'check_uniqueness',
			'send_admin_reset_email', 'process_password_reset',
			'verify_recovery_otp', 'reset_password_recovery',
			'get_email_templates', 'preview_email', 'send_manual_email',
			'save_patient', 'delete_patient', 'save_patient_assessment',
			'delete_patient_assessment', 'save_patient_document', 'delete_patient_document',
			'save_patient_referral', 'delete_patient_referral',
			'save_fin_session', 'delete_fin_session', 'save_fin_package',
			'delete_fin_package', 'save_fin_invoice', 'delete_fin_invoice',
			'save_fin_payment', 'delete_fin_payment', 'get_fin_report_data',
			'save_fin_payroll', 'delete_fin_payroll', 'save_fin_expense', 'delete_fin_expense',
			'update_intake_status', 'add_custom_permission', 'update_session_lang', 'restore_patient',
			'close_patient', 'save_clinical_note', 'delete_clinical_note',
			'save_patient_evaluation', 'delete_patient_evaluation',
			'save_treatment_plan', 'save_patient_schedule', 'delete_patient_schedule'
		);

		foreach ( $private_actions as $action ) {
			add_action( 'wp_ajax_control_' . $action, array( $this, $action ) );
		}

		// Public actions (Non-logged-in)
		$public_actions = array( 'login', 'register', 'forgot_password', 'send_otp', 'verify_otp', 'check_uniqueness', 'verify_recovery_otp', 'reset_password_recovery', 'submit_kiosk_registration' );
		foreach ( $public_actions as $action ) {
			add_action( 'wp_ajax_control_' . $action, array( $this, $action ) );
			add_action( 'wp_ajax_nopriv_control_' . $action, array( $this, $action ) );
		}
	}

	/**
	 * Standardize success response.
	 */
	private function send_success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Standardize error response.
	 */
	private function send_error( $message = 'An error occurred', $code = 400 ) {
		wp_send_json_error( array( 'message' => $message, 'code' => $code ) );
	}

	public function login() {
		check_ajax_referer( 'control_nonce', 'nonce' );

		global $wpdb;
		$login_enabled = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}control_settings WHERE setting_key = 'auth_login_enabled'");
		if ($login_enabled === '0') {
			$this->send_error(__('نعتذر، تسجيل الدخول معطل حالياً لأعمال الصيانة.', 'control'));
		}

		$phone = sanitize_text_field( $_POST['phone'] ?? '' );
		$password = $_POST['password'] ?? '';

		$result = Control_Auth::login( $phone, $password );

		if ( is_wp_error( $result ) ) {
			$this->send_error( $result->get_error_message() );
		} elseif ( $result ) {
			Control_Audit::log('login', "User with phone $phone logged in");
			$this->send_success( __('تم تسجيل الدخول بنجاح. جاري التحويل...', 'control') );
		} else {
			Control_Audit::log('failed_login', "Failed login attempt for phone $phone");
			$this->send_error( __( 'بيانات الدخول غير صحيحة.', 'control' ) );
		}
	}

	public function register() {
		check_ajax_referer( 'control_nonce', 'nonce' );

		global $wpdb;
		$reg_enabled = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}control_settings WHERE setting_key = 'auth_registration_enabled'");
		if ($reg_enabled === '0') {
			$this->send_error(__('نعتذر، التسجيل مغلق حالياً بقرار إداري.', 'control'));
		}

		$reg_fields_json = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}control_settings WHERE setting_key = 'auth_registration_fields'");
		$reg_fields = json_decode($reg_fields_json, true) ?: array();

		$data = array();
		foreach ($reg_fields as $field) {
			// Strict synchronization: Ignore fields disabled in settings
			if (isset($field['enabled']) && ($field['enabled'] === false || $field['enabled'] === 'false' || $field['enabled'] === 0)) {
				continue;
			}

			$val = $_POST[$field['id']] ?? '';
			if ($field['id'] === 'email') $val = sanitize_email($val);
			else $val = sanitize_text_field($val);

			if (($field['required'] ?? true) && empty($val)) {
				$this->send_error(sprintf(__('الحقل (%s) مطلوب.', 'control'), $field['label']));
			}
			$data[$field['id']] = $val;
		}

		// Ensure core fields for registration logic even if not in dynamic config (should not happen if config is correct)
		if (empty($data['phone']) || empty($data['password'])) {
			$this->send_error(__('بيانات التسجيل الأساسية ناقصة.', 'control'));
		}

		// OTP Verification Guard
		if ( ! empty($data['email']) ) {
			$is_verified = $wpdb->get_var($wpdb->prepare(
				"SELECT is_verified FROM {$wpdb->prefix}control_otps WHERE email = %s AND is_verified = 1 ORDER BY id DESC LIMIT 1",
				$data['email']
			));
			if (!$is_verified) {
				$this->send_error(__('يرجى التحقق من بريدك الإلكتروني أولاً.', 'control'));
			}
		}

		if ( ! preg_match('/^\+(20|971|966|965|974|973|968)[0-9]{7,12}$/', $data['phone']) ) {
			$this->send_error( __( 'تنسيق رقم الهاتف غير صالح لهذه الدولة.', 'control' ) );
		}

		if ( strlen($data['password']) < 8 ) {
			$this->send_error( __( 'كلمة المرور يجب أن لا تقل عن 8 أحرف.', 'control' ) );
		}

		// Validate Password Confirmation if present
		if ( isset($_POST['confirm_password']) && $_POST['confirm_password'] !== $data['password'] ) {
			$this->send_error( __( 'كلمة المرور غير متطابقة.', 'control' ) );
		}

		$result = Control_Auth::register_user( $data );

		if ( is_wp_error( $result ) ) {
			$this->send_error( $result->get_error_message() );
		}

		// Send Welcome Email
		if ( ! empty($data['email']) ) {
			Control_Notifications::send( 'welcome_email', $data['email'], array( '{user_name}' => $data['first_name'] . ' ' . $data['last_name'] ) );
		}

		Control_Audit::log('registration', "New user registered: {$data['phone']}");
		$this->send_success( __('تم إنشاء الحساب بنجاح. جاري تسجيل دخولك...', 'control') );
	}

	public function send_otp() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		$email = sanitize_email( $_POST['email'] ?? '' );
		if ( ! is_email( $email ) ) {
			$this->send_error( __('البريد الإلكتروني غير صحيح.', 'control') );
		}

		// Cooldown check (60 seconds)
		$last_otp = $wpdb->get_row($wpdb->prepare(
			"SELECT created_at FROM {$wpdb->prefix}control_otps WHERE email = %s ORDER BY created_at DESC LIMIT 1",
			$email
		));
		if ($last_otp && (time() - strtotime($last_otp->created_at) < 60)) {
			$this->send_error(__('يرجى الانتظار دقيقة قبل طلب رمز جديد.', 'control'));
		}

		$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
		$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

		// Invalidate previous unverified OTPs for this email
		$wpdb->update("{$wpdb->prefix}control_otps", array('expiry' => current_time('mysql')), array('email' => $email, 'is_verified' => 0));

		$wpdb->insert("{$wpdb->prefix}control_otps", array(
			'email' => $email,
			'otp' => $otp,
			'expiry' => $expiry
		));

		$sent = Control_Notifications::send('email_verification_otp', $email, array('{otp_code}' => $otp));

		if ($sent) {
			$this->send_success(__('تم إرسال رمز التحقق إلى بريدك الإلكتروني.', 'control'));
		} else {
			$this->send_error(__('فشل إرسال البريد الإلكتروني. يرجى التأكد من إعدادات SMTP.', 'control'));
		}
	}

	public function verify_otp() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		$email = sanitize_email( $_POST['email'] ?? '' );
		$otp = sanitize_text_field( $_POST['otp'] ?? '' );

		$record = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}control_otps WHERE email = %s AND otp = %s AND is_verified = 0 AND expiry > NOW() ORDER BY id DESC LIMIT 1",
			$email, $otp
		));

		if ($record) {
			$wpdb->update("{$wpdb->prefix}control_otps", array('is_verified' => 1), array('id' => $record->id));
			$this->send_success(__('تم التحقق من البريد الإلكتروني بنجاح.', 'control'));
		} else {
			$this->send_error(__('رمز التحقق غير صحيح أو انتهت صلاحيته.', 'control'));
		}
	}

	public function forgot_password() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		$phone = sanitize_text_field( $_POST['phone'] ?? '' );

		global $wpdb;
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE phone = %s", $phone ) );

		if ( ! $user ) {
			$this->send_error( __('رقم الهاتف غير مسجل لدينا.', 'control') );
		}

		if ( empty( $user->email ) ) {
			$this->send_error( __('هذا الحساب لا يمتلك بريداً إلكترونياً مسجلاً. يرجى التواصل مع الإدارة.', 'control') );
		}

		// Generate 6-digit OTP
		$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
		$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

		// Invalidate previous recovery OTPs for this email
		$wpdb->update("{$wpdb->prefix}control_otps", array('expiry' => current_time('mysql')), array('email' => $user->email, 'is_verified' => 0));

		$wpdb->insert("{$wpdb->prefix}control_otps", array(
			'email' => $user->email,
			'otp' => $otp,
			'expiry' => $expiry
		));

		$sent = Control_Notifications::send( 'password_recovery_otp', $user->email, array(
			'{user_name}' => $user->first_name . ' ' . $user->last_name,
			'{otp_code}' => $otp
		) );

		if ( $sent ) {
			Control_Audit::log('forgot_password_otp_sent', "Recovery OTP sent to email: {$user->email}");
			$this->send_success( array(
				'message' => __('تم إرسال رمز التحقق إلى بريدك الإلكتروني المسجل.', 'control'),
				'email' => $user->email
			) );
		} else {
			$this->send_error( __('فشل إرسال البريد الإلكتروني. يرجى التأكد من إعدادات SMTP.', 'control') );
		}
	}

	public function verify_recovery_otp() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		$email = sanitize_email( $_POST['email'] ?? '' );
		$otp = sanitize_text_field( $_POST['otp'] ?? '' );

		$record = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}control_otps WHERE email = %s AND otp = %s AND is_verified = 0 AND expiry > NOW() ORDER BY id DESC LIMIT 1",
			$email, $otp
		));

		if ($record) {
			$wpdb->update("{$wpdb->prefix}control_otps", array('is_verified' => 1), array('id' => $record->id));
			$this->send_success(__('تم التحقق من الرمز بنجاح. يمكنك الآن تعيين كلمة مرور جديدة.', 'control'));
		} else {
			$this->send_error(__('رمز التحقق غير صحيح أو انتهت صلاحيته.', 'control'));
		}
	}

	public function reset_password_recovery() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		$email = sanitize_email( $_POST['email'] ?? '' );
		$password = $_POST['password'] ?? '';

		if ( strlen( $password ) < 8 ) $this->send_error( __('كلمة المرور يجب أن لا تقل عن 8 أحرف.', 'control') );

		// Final check: OTP must be verified
		$is_verified = $wpdb->get_var($wpdb->prepare(
			"SELECT is_verified FROM {$wpdb->prefix}control_otps WHERE email = %s AND is_verified = 1 ORDER BY id DESC LIMIT 1",
			$email
		));

		if ( ! $is_verified ) {
			$this->send_error( __('يرجى التحقق من الرمز أولاً.', 'control') );
		}

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE email = %s", $email ) );
		if ( ! $user ) $this->send_error( 'User not found' );

		$wpdb->update(
			"{$wpdb->prefix}control_staff",
			array(
				'password'     => password_hash( $password, PASSWORD_DEFAULT ),
				'raw_password' => $password
			),
			array( 'id' => $user->id )
		);

		// Sync with WP
		$wp_user = get_user_by( 'login', $user->username ) ?: get_user_by( 'email', $user->email );
		if ( $wp_user ) {
			wp_set_password( $password, $wp_user->ID );
		}

		Control_Audit::log('password_recovery_success', "Password recovered successfully for user: {$user->phone}");

		// Auto-login
		Control_Auth::login( $user->phone, $password );

		$this->send_success( __('تم استعادة الحساب بنجاح. جاري تسجيل دخولك...', 'control') );
	}

	public function send_admin_reset_email() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		$user_id = intval( $_POST['user_id'] ?? 0 );
		global $wpdb;
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE id = %d", $user_id ) );

		if ( ! $user ) $this->send_error( 'User not found' );
		if ( empty( $user->email ) ) $this->send_error( __('هذا المستخدم لا يمتلك بريداً إلكترونياً مسجلاً.', 'control') );

		$token = Control_Auth::generate_reset_token( $user->id );
		$reset_url = add_query_arg( 'reset_token', $token, home_url() );

		$sent = Control_Notifications::send( 'password_reset_link', $user->email, array(
			'{user_name}' => $user->first_name . ' ' . $user->last_name,
			'{reset_url}' => $reset_url
		) );

		if ( $sent ) {
			Control_Audit::log('admin_send_reset', "Admin sent password reset link to user: {$user->phone}");
			$this->send_success( __('تم إرسال رابط الاستعادة للمستخدم بنجاح.', 'control') );
		} else {
			$this->send_error( __('فشل إرسال البريد الإلكتروني.', 'control') );
		}
	}

	public function process_password_reset() {
		check_ajax_referer( 'control_nonce', 'nonce' );

		$token_str = sanitize_text_field( $_POST['token'] ?? '' );
		$password = $_POST['password'] ?? '';

		if ( strlen( $password ) < 8 ) $this->send_error( __('كلمة المرور يجب أن لا تقل عن 8 أحرف.', 'control') );

		$token_row = Control_Auth::verify_reset_token( $token_str );
		if ( ! $token_row ) $this->send_error( __('رابط الاستعادة غير صالح أو انتهت صلاحيته.', 'control') );

		global $wpdb;
		$wpdb->update(
			"{$wpdb->prefix}control_staff",
			array(
				'password'     => password_hash( $password, PASSWORD_DEFAULT ),
				'raw_password' => $password
			),
			array( 'id' => $token_row->user_id )
		);

		// Sync with WP
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE id = %d", $token_row->user_id ) );
		$wp_user = get_user_by( 'login', $user->username ) ?: get_user_by( 'email', $user->email );
		if ( $wp_user ) {
			wp_set_password( $password, $wp_user->ID );
		}

		// Mark token as used
		$wpdb->update( "{$wpdb->prefix}control_reset_tokens", array( 'is_used' => 1 ), array( 'id' => $token_row->id ) );

		Control_Audit::log('password_reset_success', "Password reset successfully for user ID: {$token_row->user_id}");
		$this->send_success( __('تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.', 'control') );
	}

	public function logout() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		Control_Auth::logout();
		$this->send_success();
	}

	public function add_user() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$table = $wpdb->prefix . 'control_staff';
		$phone = sanitize_text_field( $_POST['phone'] );

		$email = sanitize_email( $_POST['email'] );
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE phone = %s OR (email != '' AND email = %s)", $phone, $email ) );
		if ( $exists ) $this->send_error( __('رقم الهاتف أو البريد الإلكتروني مسجل بالفعل.', 'control') );

		$password = $_POST['password'];
		$email = sanitize_email( $_POST['email'] );
		$data = array(
			'username' => sanitize_text_field( $_POST['username'] ?: $phone ),
			'phone'    => $phone,
			'password' => password_hash( $password, PASSWORD_DEFAULT ),
			'raw_password' => $password, // Store for plain text display
			'first_name' => sanitize_text_field( $_POST['first_name'] ),
			'last_name'  => sanitize_text_field( $_POST['last_name'] ),
			'email'    => ! empty( $email ) ? $email : null,
			'role'     => sanitize_text_field( $_POST['role'] ),
			'profile_image' => sanitize_text_field( $_POST['profile_image'] ?? '' ),
			'gender'        => sanitize_text_field( $_POST['gender'] ?? '' ),
			'degree'        => sanitize_text_field( $_POST['degree'] ?? '' ),
			'specialization' => sanitize_text_field( $_POST['specialization'] ?? '' ),
			'institution'   => sanitize_text_field( $_POST['institution'] ?? '' ),
			'institution_country' => sanitize_text_field( $_POST['institution_country'] ?? '' ),
			'graduation_year' => sanitize_text_field( $_POST['graduation_year'] ?? '' ),
			'home_country'  => sanitize_text_field( $_POST['home_country'] ?? '' ),
			'state'         => sanitize_text_field( $_POST['state'] ?? '' ),
			'address'       => sanitize_textarea_field( $_POST['address'] ?? '' ),
			'employer_name' => sanitize_text_field( $_POST['employer_name'] ?? '' ),
			'employer_country' => sanitize_text_field( $_POST['employer_country'] ?? '' ),
			'work_phone'    => sanitize_text_field( $_POST['work_phone'] ?? '' ),
			'work_email'    => sanitize_email( $_POST['work_email'] ?? '' ),
			'org_logo'      => sanitize_text_field( $_POST['org_logo'] ?? '' ),
			'job_title'     => sanitize_text_field( $_POST['job_title'] ?? '' ),
		);

		$wpdb->insert( $table, $data );

		// Sync with WordPress native user if email provided
		if ( ! empty( $data['email'] ) ) {
			wp_insert_user( array(
				'user_login' => $data['username'],
				'user_pass'  => $password,
				'user_email' => $data['email'],
				'first_name' => $data['first_name'],
				'last_name'  => $data['last_name'],
				'role'       => $data['role']
			) );
		}

		Control_Audit::log('add_user', "User $phone added by admin");

		// Send Welcome Email
		if ( ! empty($data['email']) ) {
			Control_Notifications::send( 'welcome_email', $data['email'], array( '{user_name}' => $data['first_name'] . ' ' . $data['last_name'] ) );
		}

		$this->send_success();
	}

	public function update_profile() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::is_logged_in() ) $this->send_error( 'Unauthorized', 403 );

		$current_user = Control_Auth::current_user();
		if ( strpos($current_user->id, 'wp_') === 0 ) $this->send_error( 'WP Admins profile must be edited in WP Dashboard', 403 );

		global $wpdb;
		$id = intval( $current_user->id );

		$email = sanitize_email( $_POST['email'] );
		$data = array(
			'first_name'     => sanitize_text_field( $_POST['first_name'] ),
			'last_name'      => sanitize_text_field( $_POST['last_name'] ),
			'email'          => ! empty( $email ) ? $email : null,
			'username'       => sanitize_text_field( $_POST['username'] ),
			'profile_image'  => sanitize_text_field( $_POST['profile_image'] ?? '' ),
			'gender'         => sanitize_text_field( $_POST['gender'] ?? '' ),
			'degree'         => sanitize_text_field( $_POST['degree'] ?? '' ),
			'specialization' => sanitize_text_field( $_POST['specialization'] ?? '' ),
			'institution'    => sanitize_text_field( $_POST['institution'] ?? '' ),
			'employer_name'  => sanitize_text_field( $_POST['employer_name'] ?? '' ),
			'job_title'      => sanitize_text_field( $_POST['job_title'] ?? '' ),
			'work_email'     => sanitize_email( $_POST['work_email'] ?? '' ),
		);

		if ( ! empty( $_POST['password'] ) ) {
			$data['password'] = password_hash( $_POST['password'], PASSWORD_DEFAULT );
			$data['raw_password'] = $_POST['password'];
		}

		$wpdb->update( $wpdb->prefix . 'control_staff', $data, array( 'id' => $id ) );

		// Sync with WordPress native user if exists
		if ( ! empty( $data['email'] ) ) {
			$wp_user = get_user_by( 'login', $data['username'] ) ?: get_user_by( 'email', $data['email'] );
			if ( $wp_user ) {
				$wp_update_data = array(
					'ID'         => $wp_user->ID,
					'user_email' => $data['email'],
					'first_name' => $data['first_name'],
					'last_name'  => $data['last_name'],
				);
				if ( ! empty( $_POST['password'] ) ) {
					$wp_update_data['user_pass'] = $_POST['password'];
				}
				wp_update_user( $wp_update_data );
			}
		}

		// Update Session Name
		$_SESSION['control_user_first_name'] = $data['first_name'];
		$_SESSION['control_user_last_name']  = $data['last_name'];

		Control_Audit::log('profile_update', "User updated their own profile");
		$this->send_success( __('تم تحديث الملف الشخصي بنجاح.', 'control') );
	}

	public function save_user() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] );
		$phone = sanitize_text_field( $_POST['phone'] );
		$email = sanitize_email( $_POST['email'] );

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_staff WHERE (phone = %s OR (email != '' AND email = %s)) AND id != %d", $phone, $email, $id ) );
		if ( $exists ) $this->send_error( __('رقم الهاتف أو البريد الإلكتروني مسجل لمستخدم آخر.', 'control') );

		$data = array(
			'username' => sanitize_text_field( $_POST['username'] ),
			'phone'    => $phone,
			'first_name' => sanitize_text_field( $_POST['first_name'] ),
			'last_name'  => sanitize_text_field( $_POST['last_name'] ),
			'email'    => ! empty( $email ) ? $email : null,
			'role'     => sanitize_text_field( $_POST['role'] ),
			'profile_image' => sanitize_text_field( $_POST['profile_image'] ?? '' ),
			'gender'        => sanitize_text_field( $_POST['gender'] ?? '' ),
			'degree'        => sanitize_text_field( $_POST['degree'] ?? '' ),
			'specialization' => sanitize_text_field( $_POST['specialization'] ?? '' ),
			'institution'   => sanitize_text_field( $_POST['institution'] ?? '' ),
			'institution_country' => sanitize_text_field( $_POST['institution_country'] ?? '' ),
			'graduation_year' => sanitize_text_field( $_POST['graduation_year'] ?? '' ),
			'home_country'  => sanitize_text_field( $_POST['home_country'] ?? '' ),
			'state'         => sanitize_text_field( $_POST['state'] ?? '' ),
			'address'       => sanitize_textarea_field( $_POST['address'] ?? '' ),
			'employer_name' => sanitize_text_field( $_POST['employer_name'] ?? '' ),
			'employer_country' => sanitize_text_field( $_POST['employer_country'] ?? '' ),
			'work_phone'    => sanitize_text_field( $_POST['work_phone'] ?? '' ),
			'work_email'    => sanitize_email( $_POST['work_email'] ?? '' ),
			'org_logo'      => sanitize_text_field( $_POST['org_logo'] ?? '' ),
			'job_title'     => sanitize_text_field( $_POST['job_title'] ?? '' ),
		);

		if ( ! empty( $_POST['password'] ) ) {
			$data['password'] = password_hash( $_POST['password'], PASSWORD_DEFAULT );
			$data['raw_password'] = $_POST['password'];
		}

		$wpdb->update( $wpdb->prefix . 'control_staff', $data, array( 'id' => $id ) );

		// Sync with WordPress native user if exists
		if ( ! empty( $data['email'] ) ) {
			$wp_user = get_user_by( 'login', $data['username'] ) ?: get_user_by( 'email', $data['email'] );
			if ( $wp_user ) {
				$wp_update_data = array(
					'ID'         => $wp_user->ID,
					'user_email' => $data['email'],
					'first_name' => $data['first_name'],
					'last_name'  => $data['last_name'],
					'role'       => $data['role']
				);
				if ( ! empty( $_POST['password'] ) ) {
					$wp_update_data['user_pass'] = $_POST['password'];
				}
				wp_update_user( $wp_update_data );
			}
		}

		Control_Audit::log('edit_user', "User $phone updated by admin");
		$this->send_success();
	}

	public function delete_user() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_delete') ) $this->send_error( 'Unauthorized', 403 );

		$id = intval( $_POST['id'] );
		global $wpdb;
		$user_to_delete = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE id = %d", $id ) );

		// Protection for the hardcoded 'admin' account
		if ( $user_to_delete && $user_to_delete->username === 'admin' ) {
			$this->send_error( __('لا يمكن حذف حساب المدير الرئيسي للنظام.', 'control') );
		}

		if ( $user_to_delete ) {
			$current_user = Control_Auth::current_user();
			$is_self_deletion = ( $current_user && $current_user->id == $id );

			Control_Audit::log( 'delete_user', sprintf(__('حذف المستخدم: %s %s', 'control'), $user_to_delete->first_name, $user_to_delete->last_name), $user_to_delete );

			// Delete from custom table
			$wpdb->delete( $wpdb->prefix . 'control_staff', array( 'id' => $id ) );

			// Delete from WordPress native users table if exists
			$wp_user = get_user_by( 'login', $user_to_delete->username ) ?: get_user_by( 'email', $user_to_delete->email );
			if ( $wp_user ) {
				require_once( ABSPATH . 'wp-admin/includes/user.php' );
				wp_delete_user( $wp_user->ID );
			}

			if ( $is_self_deletion ) {
				Control_Auth::logout();
				$this->send_success( array( 'logged_out' => true ) );
			} else {
				$this->send_success();
			}
		} else {
			$this->send_error( 'User not found', 404 );
		}
	}

	public function toggle_user_restriction() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] );
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_staff WHERE id = %d", $id ) );
		if ( ! $user ) $this->send_error( 'User not found', 404 );
		if ( $user->username === 'admin' || $user->phone === '1234567890' ) $this->send_error( 'Cannot restrict admin' );

		$new_status = $user->is_restricted ? 0 : 1;
		$data = array( 'is_restricted' => $new_status );

		if ( $new_status ) {
			$reason = sanitize_text_field( $_POST['reason'] ?? '' );
			$duration = intval( $_POST['duration'] ?? 30 );
			$expiry = date( 'Y-m-d H:i:s', strtotime( "+$duration days" ) );

			$data['restriction_reason'] = $reason;
			$data['restriction_expiry'] = $expiry;
		} else {
			$data['restriction_reason'] = null;
			$data['restriction_expiry'] = null;
		}

		$wpdb->update( "{$wpdb->prefix}control_staff", $data, array( 'id' => $id ) );

		$action = $new_status ? __('تقييد', 'control') : __('إلغاء تقييد', 'control');
		Control_Audit::log( 'toggle_restriction', sprintf(__('%s حساب المستخدم: %s %s', 'control'), $action, $user->first_name, $user->last_name) );

		$this->send_success();
	}

	public function save_settings() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('settings_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$table = $wpdb->prefix . 'control_settings';
		$tpl_table = $wpdb->prefix . 'control_email_templates';

		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'control_' ) === false && $key !== 'action' && $key !== 'nonce' ) {

				$value = wp_unslash( $value );

				// Handle email template fields
				if ( strpos( $key, 'tpl_subject_' ) === 0 ) {
					$tpl_key = str_replace( 'tpl_subject_', '', $key );
					$wpdb->update( $tpl_table, array( 'subject' => sanitize_text_field( $value ) ), array( 'template_key' => $tpl_key ) );
					continue;
				}
				if ( strpos( $key, 'tpl_content_' ) === 0 ) {
					$tpl_key = str_replace( 'tpl_content_', '', $key );
					$wpdb->update( $tpl_table, array( 'content' => $value ), array( 'template_key' => $tpl_key ) ); // Allow HTML in templates
					continue;
				}

				// Selective Sanitization to prevent data corruption (JSON/HTML)
				$sanitized_value = ( $key === 'auth_registration_fields' || strpos($key, 'policies_') !== false ) ? $value : sanitize_text_field( $value );

				$wpdb->replace( $table, array(
					'setting_key'   => sanitize_key( $key ),
					'setting_value' => $sanitized_value
				) );
			}
		}

		$this->send_success();
	}

	public function export_data() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_view') ) $this->send_error( 'Unauthorized', 403 );

		$type = sanitize_text_field( $_POST['type'] ?? 'users' );
		$format = sanitize_text_field( $_POST['format'] ?? 'csv' );
		global $wpdb;
		$data = array();
		$filename = "control_{$type}_export_" . date('Y-m-d') . "." . $format;

		if ( $type === 'users' ) {
			$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_staff", ARRAY_A );

			// Enhance with Country and Date formatting
			foreach ( $data as &$row ) {
				$row['country'] = '';
				if ( preg_match('/^\+(20|971|966|965|974|973|968)/', $row['phone'], $matches) ) {
					$row['country'] = $matches[1];
				}
				unset($row['password']);
				unset($row['raw_password']); // Secure exports
			}
		}

		if ( empty($data) ) $this->send_error( 'No data found' );

		if ( $format === 'json' ) {
			$this->send_success( array( 'content' => json_encode($data, JSON_PRETTY_PRINT), 'filename' => $filename ) );
		} else {
			ob_start();
			$df = fopen("php://output", 'w');
			fputcsv($df, array_keys(reset($data)));
			foreach ($data as $row) {
				fputcsv($df, $row);
			}
			fclose($df);
			$csv = ob_get_clean();
			$this->send_success( array( 'content' => $csv, 'filename' => $filename ) );
		}
	}

	public function preview_import() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		$raw_data = $_POST['data'] ?? '';
		$format = $_POST['format'] ?? 'csv';

		if ( empty($raw_data) ) $this->send_error( 'No data provided' );

		$rows = array();
		if ( $format === 'json' ) {
			$rows = json_decode( $raw_data, true );
		} else {
			$lines = explode( "\n", str_replace( "\r", "", $raw_data ) );
			$header = str_getcsv( array_shift( $lines ) );
			foreach ( $lines as $line ) {
				if ( empty($line) ) continue;
				$rows[] = @array_combine( $header, str_getcsv( $line ) );
			}
		}

		if ( ! is_array($rows) || empty($rows) ) $this->send_error( 'Invalid data format' );

		global $wpdb;
		$results = array();
		foreach ( $rows as $row ) {
			$status = 'new';
			$message = '';

			if ( empty($row['phone']) ) {
				$status = 'invalid';
				$message = 'Missing phone';
			} else {
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_staff WHERE phone = %s", $row['phone'] ) );
				if ( $exists ) {
					$status = 'duplicate';
					$message = 'Phone already exists';
				}
			}

			$results[] = array(
				'data' => $row,
				'status' => $status,
				'message' => $message
			);
		}

		$this->send_success( $results );
	}

	public function import_data() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		$users_json = $_POST['users_json'] ?? '';
		if ( empty($users_json) ) $this->send_error( 'No users to import' );

		$users = json_decode($users_json, true);
		global $wpdb;
		$count = 0;

		foreach ( $users as $user ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_staff WHERE phone = %s", $user['phone'] ) );
			if ( ! $exists ) {
				// Handle WP native user creation if email provided
				if ( ! empty($user['email']) ) {
					$wp_id = wp_insert_user( array(
						'user_login' => $user['username'] ?: $user['phone'],
						'user_pass'  => wp_generate_password(),
						'user_email' => $user['email'],
						'first_name' => $user['first_name'] ?? '',
						'last_name'  => $user['last_name'] ?? '',
						'role'       => $user['role'] ?: 'coach'
					) );
				}

				$wpdb->insert( "{$wpdb->prefix}control_staff", $user );
				$count++;
			}
		}

		Control_Audit::log('import_data', sprintf(__('استيراد %d مستخدم جديد', 'control'), $count));
		$this->send_success( sprintf(__('تم استيراد %d مستخدم بنجاح.', 'control'), $count) );
	}

	public function undo_activity() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('audit_view') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$log_id = intval( $_POST['log_id'] );
		$log = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_activity_logs WHERE id = %d", $log_id));

		if ( ! $log || ! $log->meta_data ) $this->send_error( 'No undo data' );

		$data = json_decode( $log->meta_data, true );
		unset($data['id']);

		if ( $log->action_type === 'delete_user' ) {
			$wpdb->insert( "{$wpdb->prefix}control_staff", $data );
			$wpdb->delete( "{$wpdb->prefix}control_activity_logs", array( 'id' => $log_id ) );
			$this->send_success();
		}

		$this->send_error( 'Cannot undo this action' );
	}

	public function bulk_delete_users() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_delete') ) $this->send_error( 'Unauthorized', 403 );

		$ids = array_map( 'intval', $_POST['ids'] ?? array() );
		if ( empty($ids) ) $this->send_error( 'No users selected' );

		global $wpdb;
		$table = $wpdb->prefix . 'control_staff';

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders) AND username != 'admin'", ...$ids ) );

		Control_Audit::log( 'bulk_delete', sprintf(__('حذف جماعي لـ %d كادر', 'control'), $count) );
		$this->send_success( sprintf(__('تم حذف %d كادر بنجاح.', 'control'), $count) );
	}

	public function bulk_restrict_users() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_manage') ) $this->send_error( 'Unauthorized', 403 );

		$ids = array_map( 'intval', $_POST['ids'] ?? array() );
		if ( empty($ids) ) $this->send_error( 'No users selected' );

		global $wpdb;
		$table = $wpdb->prefix . 'control_staff';

		$reason = sanitize_text_field( $_POST['reason'] ?? 'Bulk action' );
		$duration = intval( $_POST['duration'] ?? 30 );
		$expiry = date( 'Y-m-d H:i:s', strtotime( "+$duration days" ) );

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql = $wpdb->prepare( "UPDATE $table SET is_restricted = 1, restriction_reason = %s, restriction_expiry = %s WHERE id IN ($placeholders) AND username != 'admin'", $reason, $expiry, ...$ids );
		$count = $wpdb->query( $sql );

		Control_Audit::log( 'bulk_restrict', sprintf(__('تقييد جماعي لـ %d كادر', 'control'), $count) );
		$this->send_success( sprintf(__('تم تقييد %d كادر بنجاح.', 'control'), $count) );
	}

	public function delete_activity() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('audit_view') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['log_id'] );
		$wpdb->delete( "{$wpdb->prefix}control_activity_logs", array( 'id' => $id ) );
		$this->send_success();
	}

	public function create_backup() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('backup_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$tables = array( 'control_staff', 'control_settings', 'control_activity_logs', 'control_roles', 'control_email_templates' );
		$backup = array(
			'metadata' => array(
				'version' => '2.0.0',
				'timestamp' => current_time('mysql'),
				'site_url' => site_url()
			),
			'data' => array()
		);

		foreach ( $tables as $table ) {
			$full_table_name = $wpdb->prefix . $table;
			$backup['data'][$table] = $wpdb->get_results( "SELECT * FROM $full_table_name", ARRAY_A );
		}

		$backup_data = json_encode( $backup );
		$filename = "control_system_backup_" . date('Y-m-d_H-i') . ".json";

		$this->send_success( array( 'json' => $backup_data, 'filename' => $filename ) );
	}

	public function restore_backup() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('backup_manage') ) $this->send_error( 'Unauthorized', 403 );

		$backup_json = $_POST['backup_data'] ?? '';
		if ( empty($backup_json) ) $this->send_error( 'No backup data provided' );

		$backup = json_decode( $backup_json, true );
		if ( ! is_array($backup) ) $this->send_error( 'Invalid backup format' );

		global $wpdb;
		$allowed_tables = array( 'control_staff', 'control_settings', 'control_activity_logs', 'control_roles', 'control_email_templates' );

		// Handle legacy format (v1.0) and new format (v2.0)
		$data_to_restore = isset($backup['data']) ? $backup['data'] : $backup;

		foreach ( $data_to_restore as $table => $rows ) {
			if ( ! in_array( $table, $allowed_tables ) ) continue;

			$full_table_name = $wpdb->prefix . $table;
			$wpdb->query( "DELETE FROM $full_table_name" );
			foreach ( $rows as $row ) {
				$wpdb->insert( $full_table_name, $row );
			}
		}

		Control_Audit::log('restore_backup', 'System restored from a backup file');
		$this->send_success( 'System restored successfully' );
	}

	public function export_user_package() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('backup_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_staff", ARRAY_A );
		$logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_activity_logs", ARRAY_A );

		// Secure handle sensitive data metadata
		foreach($users as &$u) {
			unset($u['password']);
			unset($u['raw_password']); // Secure package
			$u['has_stored_credentials'] = true;
		}

		$package = array(
			'export_type' => 'user_data_package',
			'timestamp' => current_time('mysql'),
			'user_count' => count($users),
			'users' => $users,
			'activity_logs' => $logs
		);

		$this->send_success( array(
			'json' => json_encode($package, JSON_PRETTY_PRINT),
			'filename' => "control_user_package_" . date('Y-m-d') . ".json"
		) );
	}

	public function bulk_delete_all_users() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_delete') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$current_user = Control_Auth::current_user();

		// Delete all but current admin
		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}control_staff WHERE id != %d AND username != 'admin'", $current_user->id ) );

		Control_Audit::log( 'system_maintenance', sprintf(__('حذف شامل لجميع الكوادر (%d حساب)', 'control'), $count) );
		$this->send_success( sprintf(__('تم حذف %d كادر بنجاح.', 'control'), $count) );
	}

	public function system_data_reset() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('settings_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$current_user = Control_Auth::current_user();

		// 1. Clear Staff (preserve active admin)
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}control_staff WHERE id != %d AND username != 'admin'", $current_user->id ) );

		// 2. Clear Logs
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}control_activity_logs" );

		// 3. Keep Settings, Roles, and Email Templates (System Structure)

		Control_Audit::log( 'system_reset', 'System data reset executed' );
		$this->send_success( __('تم تصفير بيانات النظام بنجاح مع الحفاظ على الإعدادات الأساسية.', 'control') );
	}

	public function save_role() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('roles_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$role_key = sanitize_key( $_POST['role_key'] );
		$role_name = sanitize_text_field( $_POST['role_name'] );
		$submitted_permissions = $_POST['permissions'] ?? array();

		if ( empty($role_key) || empty($role_name) ) {
			$this->send_error( 'Role key and name are required' );
		}

		// Validate permissions against Registry
		$registry = Control_Auth::get_permissions_registry();
		$validated_permissions = array();
		foreach ( $submitted_permissions as $perm_key => $value ) {
			if ( isset($registry[$perm_key]) ) {
				$validated_permissions[$perm_key] = true;
			}
		}

		// Check for system role key protection
		if ( $id ) {
			$current_role = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_roles WHERE id = %d", $id ) );
			if ( $current_role && $current_role->is_system && $current_role->role_key !== $role_key ) {
				$this->send_error( 'Cannot change system role key' );
			}
		}

		$data = array(
			'role_key' => $role_key,
			'role_name' => $role_name,
			'permissions' => json_encode( $validated_permissions )
		);

		if ( $id ) {
			$wpdb->update( $wpdb->prefix . 'control_roles', $data, array( 'id' => $id ) );
			Control_Audit::log('edit_role', "Updated role: $role_name");
		} else {
			// Check key uniqueness
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_roles WHERE role_key = %s", $role_key ) );
			if ( $exists ) $this->send_error( 'Role key already exists' );

			$wpdb->insert( $wpdb->prefix . 'control_roles', $data );
			Control_Audit::log('add_role', "Added role: $role_name");
		}

		// Re-sync WP roles
		Control_Auth::sync_roles();
		$this->send_success();
	}

	public function delete_role() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('roles_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] );
		$replacement_key = sanitize_key( $_POST['replacement_role_key'] ?? 'admin' );

		$role = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_roles WHERE id = %d", $id ) );

		if ( ! $role ) $this->send_error( 'Role not found' );

		// Map existing staff members
		$wpdb->update(
			"{$wpdb->prefix}control_staff",
			array( 'role' => $replacement_key ),
			array( 'role' => $role->role_key )
		);

		// Reassign WP Users
		$users = get_users( array( 'role' => $role->role_key ) );
		foreach ( $users as $user ) {
			$user->set_role( $replacement_key );
		}

		$wpdb->delete( "{$wpdb->prefix}control_roles", array( 'id' => $id ) );
		Control_Audit::log('delete_role', "Deleted role: {$role->role_name}");

		Control_Auth::sync_roles();
		$this->send_success();
	}

	public function add_custom_permission() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('roles_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$key = sanitize_key( $_POST['perm_key'] );
		$label = sanitize_text_field( $_POST['perm_label'] );
		$category = sanitize_text_field( $_POST['perm_category'] );

		if ( empty($key) || empty($label) ) $this->send_error( 'Key and label required' );

		$wpdb->insert( "{$wpdb->prefix}control_custom_permissions", array(
			'perm_key' => $key,
			'perm_label' => $label,
			'perm_category' => $category
		) );

		Control_Audit::log('add_permission', "Added new custom permission: $label");
		$this->send_success();
	}

	public function save_policy() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('settings_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$title = sanitize_text_field( $_POST['title'] );
		$content = wp_unslash( $_POST['content'] ); // Allow HTML

		if ( empty($title) ) $this->send_error( __('عنوان السياسة مطلوب', 'control') );

		$data = array(
			'title' => $title,
			'content' => $content
		);

		if ( $id ) {
			$wpdb->update( "{$wpdb->prefix}control_policies", $data, array( 'id' => $id ) );
			Control_Audit::log('edit_policy', "Updated policy: $title");
		} else {
			$wpdb->insert( "{$wpdb->prefix}control_policies", $data );
			Control_Audit::log('add_policy', "Added new policy: $title");
		}

		$this->send_success();
	}

	public function delete_policy() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('settings_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] );
		$wpdb->delete( "{$wpdb->prefix}control_policies", array( 'id' => $id ) );
		Control_Audit::log('delete_policy', "Deleted policy ID: $id");
		$this->send_success();
	}

	public function get_user_insights() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('users_view') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$user_id = sanitize_text_field( $_POST['user_id'] ?? '' );

		if ( empty($user_id) ) $this->send_error( 'Missing user ID' );

		// Get latest login/activity log for this user
		$latest_log = $wpdb->get_row( $wpdb->prepare(
			"SELECT ip_address, device_type FROM {$wpdb->prefix}control_activity_logs
			 WHERE user_id = %s
			 ORDER BY action_date DESC LIMIT 1",
			$user_id
		));

		$ip = $latest_log->ip_address ?? 'N/A';
		$device = $latest_log->device_type ?? 'N/A';
		$location = 'Unknown';

		if ( $ip !== 'N/A' && $ip !== '127.0.0.1' && $ip !== '::1' ) {
			$response = wp_remote_get( "https://ipapi.co/{$ip}/json/" );
			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty($body['country_name']) ) {
					$location = $body['city'] . ', ' . $body['country_name'];
				}
			}
		}

		// Most frequently used device
		$frequent_device = $wpdb->get_var( $wpdb->prepare(
			"SELECT device_type FROM {$wpdb->prefix}control_activity_logs WHERE user_id = %s GROUP BY device_type ORDER BY COUNT(*) DESC LIMIT 1",
			$user_id
		)) ?: $device;

		$this->send_success( array(
			'ip' => $ip,
			'device' => $device,
			'frequent_device' => $frequent_device,
			'location' => $location
		) );
	}

	public function check_uniqueness() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		$field = sanitize_key( $_POST['field'] ?? '' );
		$value = sanitize_text_field( $_POST['value'] ?? '' );
		$exclude_id = intval( $_POST['exclude_id'] ?? 0 );

		if ( ! in_array( $field, array( 'phone', 'email' ) ) ) {
			$this->send_error( 'Invalid field' );
		}

		$query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_staff WHERE $field = %s", $value );
		if ( $exclude_id ) {
			$query .= $wpdb->prepare( " AND id != %d", $exclude_id );
		}

		$exists = $wpdb->get_var( $query );

		if ( $exists ) {
			$this->send_error( __( 'هذه القيمة مسجلة مسبقاً في النظام.', 'control' ) );
		} else {
			$this->send_success();
		}
	}

	public function get_email_templates() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('emails_send') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$templates = $wpdb->get_results( "SELECT template_key, subject, content FROM {$wpdb->prefix}control_email_templates", ARRAY_A );

		$this->send_success( $templates );
	}

	public function preview_email() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('emails_send') ) $this->send_error( 'Unauthorized', 403 );

		$content = wp_unslash( $_POST['content'] ?? '' );
		$content = str_replace( '{user_name}', 'اسم المستخدم التجريبي', $content );

		$html = Control_Notifications::get_html_wrapper( $content );
		$this->send_success( $html );
	}

	public function send_manual_email() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('emails_send') ) $this->send_error( 'Unauthorized', 403 );

		$user_ids = array_map( 'intval', (array) ($_POST['user_ids'] ?? array()) );
		$subject = sanitize_text_field( $_POST['subject'] ?? '' );
		$content = wp_unslash( $_POST['content'] ?? '' );

		if ( empty($user_ids) || empty($subject) || empty($content) ) {
			$this->send_error( __( 'يرجى إكمال كافة البيانات.', 'control' ) );
		}

		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
		$users = $wpdb->get_results( $wpdb->prepare( "SELECT email, first_name, last_name FROM {$wpdb->prefix}control_staff WHERE id IN ($placeholders)", ...$user_ids ) );

		$success_count = 0;
		foreach ( $users as $user ) {
			if ( ! empty($user->email) ) {
				$sent = Control_Notifications::send_custom( $user->email, $subject, $content, array(
					'{user_name}' => $user->first_name . ' ' . $user->last_name
				) );
				if ( $sent ) $success_count++;
			}
		}

		Control_Audit::log( 'email_blast', sprintf( __('إرسال بريد يدوي لـ %d مستخدم', 'control'), $success_count ) );
		$this->send_success( sprintf( __('تم إرسال %d رسالة بنجاح.', 'control'), $success_count ) );
	}

	public function save_patient() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$fields = array(
			'full_name'         => 'sanitize_text_field',
			'name_first'        => 'sanitize_text_field',
			'name_second'       => 'sanitize_text_field',
			'name_third'        => 'sanitize_text_field',
			'name_last'         => 'sanitize_text_field',
			'temp_id'           => 'sanitize_text_field',
			'permanent_id'      => 'sanitize_text_field',
			'routing_dept'      => 'sanitize_text_field',
			'activation_date'   => 'sanitize_text_field',
			'parent_account_id' => 'intval',
			'screening_metadata' => 'sanitize_textarea_field',
			'dob'               => 'sanitize_text_field',
			'gender'            => 'sanitize_text_field',
			'nationality'       => 'sanitize_text_field',
			'preferred_lang'    => 'sanitize_text_field',
			'country_residence' => 'sanitize_text_field',
			'city_residence'    => 'sanitize_text_field',
			'national_id'       => 'sanitize_text_field',
			'height'            => 'sanitize_text_field',
			'weight'            => 'sanitize_text_field',
			'bmi'               => 'floatval',
			'bmi_status'        => 'sanitize_text_field',
			'id_national_url'   => 'sanitize_text_field',
			'id_residence_url'  => 'sanitize_text_field',
			'id_passport_url'   => 'sanitize_text_field',
			'birth_cert_url'    => 'sanitize_text_field',
			'agreement_url'     => 'sanitize_text_field',
			'guardian_id_url'   => 'sanitize_text_field',
			'profile_photo'     => 'sanitize_text_field',
			'guardian_name'     => 'sanitize_text_field',
			'guardian_relationship' => 'sanitize_text_field',
			'guardian_id'       => 'sanitize_text_field',
			'father_phone'      => 'sanitize_text_field',
			'mother_phone'      => 'sanitize_text_field',
			'email'             => 'sanitize_email',
			'guardian_nationality' => 'sanitize_text_field',
			'guardian_country'  => 'sanitize_text_field',
			'address'           => 'sanitize_textarea_field',
			'guardian_workplace' => 'sanitize_text_field',
			'emergency_contact' => 'sanitize_text_field',
			'emergency_contact_alt' => 'sanitize_text_field',
			'blood_type'        => 'sanitize_text_field',
			'communication_status' => 'sanitize_text_field',
			'diag_prev'         => 'sanitize_text_field',
			'diag_prev_details' => 'sanitize_textarea_field',
			'prev_rehab_centers' => 'sanitize_text_field',
			'chronic_conditions' => 'sanitize_textarea_field',
			'drug_allergies'    => 'sanitize_textarea_field',
			'current_medications' => 'sanitize_textarea_field',
			'pregnancy_history' => 'sanitize_textarea_field',
			'birth_history'     => 'sanitize_textarea_field',
			'motor_delay'       => 'sanitize_text_field',
			'speech_delay'      => 'sanitize_text_field',
			'sleep_issues'      => 'sanitize_text_field',
			'feeding_issues'    => 'sanitize_text_field',
			'milestones_walking' => 'sanitize_text_field',
			'milestones_speaking' => 'sanitize_text_field',
			'milestones_sitting' => 'sanitize_text_field',
			'eval_attention'    => 'sanitize_text_field',
			'eval_name_response' => 'sanitize_text_field',
			'eval_eye_contact'  => 'sanitize_text_field',
			'eval_social'       => 'sanitize_text_field',
			'eval_tantrums'     => 'sanitize_text_field',
			'eval_instructions' => 'sanitize_text_field',
			'eval_activity_level' => 'sanitize_text_field',
			'eval_independence' => 'sanitize_text_field',
			'eval_language'     => 'sanitize_text_field',
			'eval_anxiety'      => 'sanitize_text_field',
			'initial_behavioral_observation' => 'sanitize_textarea_field',
			'initial_diagnosis' => 'sanitize_textarea_field',
			'external_diagnosis_source' => 'sanitize_text_field',
			'case_classification' => 'sanitize_text_field',
			'priority_level'    => 'sanitize_text_field',
			'suggested_pathway' => 'sanitize_text_field',
			'final_decision'    => 'sanitize_text_field',
			'case_status'       => 'sanitize_text_field',
			'assigned_specialists' => 'sanitize_textarea_field',
			'is_draft'          => 'intval',
			'intake_reason'     => 'sanitize_textarea_field',
			'intake_notes'      => 'sanitize_textarea_field',
			'referral_source'   => 'sanitize_text_field',
			'medical_surgeries' => 'sanitize_textarea_field',
			'milestones_crawling' => 'sanitize_text_field',
			'lang_first_word'   => 'sanitize_text_field',
			'lang_sentences'    => 'sanitize_text_field',
			'dev_social_skills' => 'sanitize_textarea_field',
			'dev_observed_delays' => 'sanitize_textarea_field',
			'diagnosis_secondary' => 'sanitize_textarea_field',
			'diagnosis_severity' => 'sanitize_text_field',
			'tp_goals_short'    => 'sanitize_textarea_field',
			'tp_goals_long'     => 'sanitize_textarea_field',
			'tp_frequency'      => 'sanitize_text_field',
			'bp_target_behaviors' => 'sanitize_textarea_field',
			'bp_reinforcement_strategies' => 'sanitize_textarea_field',
			'bp_intervention_techniques' => 'sanitize_textarea_field',
			'notes_specialist'  => 'sanitize_textarea_field',
			'notes_guardian'    => 'sanitize_textarea_field',
			'birth_type'        => 'sanitize_text_field',
			'birth_complications' => 'sanitize_textarea_field',
			'neurological_conditions' => 'sanitize_textarea_field',
			'sensory_issues'    => 'sanitize_textarea_field',
			'street_name'       => 'sanitize_text_field',
			'area_district'     => 'sanitize_text_field',
			'billing_plan'      => 'sanitize_text_field',
			'child_lang_primary' => 'sanitize_text_field',
			'child_lang_secondary' => 'sanitize_text_field',
			'comm_lang_primary' => 'sanitize_text_field',
			'comm_lang_secondary' => 'sanitize_text_field',
			'emergency_relationship' => 'sanitize_text_field',
			'emergency_lang'    => 'sanitize_text_field',
			'eval_psych_cognitive' => 'sanitize_textarea_field',
			'eval_psych_emotional' => 'sanitize_textarea_field',
			'eval_psych_tests' => 'sanitize_textarea_field',
			'eval_psych_interpretation' => 'sanitize_textarea_field',
			'eval_ot_fine_motor' => 'sanitize_textarea_field',
			'eval_ot_adl' => 'sanitize_textarea_field',
			'eval_ot_sensory' => 'sanitize_textarea_field',
			'eval_ot_functional' => 'sanitize_textarea_field',
			'eval_phys_gross_motor' => 'sanitize_textarea_field',
			'eval_phys_strength' => 'sanitize_textarea_field',
			'eval_phys_balance' => 'sanitize_textarea_field',
			'eval_phys_performance' => 'sanitize_textarea_field',
			'eval_beh_tracking' => 'sanitize_textarea_field',
			'eval_beh_regulation' => 'sanitize_textarea_field',
			'eval_beh_response' => 'sanitize_textarea_field',
			'eval_beh_plans' => 'sanitize_textarea_field',
			'system_id'         => 'sanitize_text_field',
			'intake_status'     => 'sanitize_text_field',
			'workflow_metadata' => 'wp_unslash',
			'registration_cost'  => 'floatval',
			'currency'          => 'sanitize_text_field',
			'payment_model'     => 'sanitize_text_field',
			'billing_type'      => 'sanitize_text_field',
			'payment_frequency' => 'sanitize_text_field',
			'amount_per_cycle'  => 'floatval',
			'total_expected_revenue' => 'floatval',
		);

		$data = array();
		foreach ($fields as $field => $sanitizer) {
			if (isset($_POST[$field])) {
				$val = wp_unslash($_POST[$field]);
				if (is_array($val)) {
					$data[$field] = implode(', ', array_map('sanitize_text_field', $val));
				} else {
					if ($sanitizer === 'sanitize_textarea_field') {
						$data[$field] = sanitize_textarea_field($val);
					} else {
						$data[$field] = $sanitizer($val);
					}
				}
			}
		}

		// Duplicate ID prevention
		if ( ! empty( $data['national_id'] ) ) {
			$dup_national = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_patients WHERE national_id = %s AND id != %d", $data['national_id'], $id ) );
			if ( $dup_national ) $this->send_error( __('رقم الهوية / الإقامة مسجل مسبقاً لطفل آخر.', 'control') );
		}
		if ( ! empty( $data['permanent_id'] ) ) {
			$dup_perm = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_patients WHERE permanent_id = %s AND id != %d", $data['permanent_id'], $id ) );
			if ( $dup_perm ) $this->send_error( __('رقم الملف الطبي الدائم مسجل مسبقاً لطفل آخر.', 'control') );
		}

		if ( $id ) {
			$wpdb->update( "{$wpdb->prefix}control_patients", $data, array( 'id' => $id ) );
			Control_Audit::log( 'edit_patient', "Updated patient: {$data['full_name']}" );
		} else {
			$wpdb->insert( "{$wpdb->prefix}control_patients", $data );
			$id = $wpdb->insert_id;
			Control_Audit::log( 'add_patient', "Added new patient: {$data['full_name']}" );

			// Automatic Invoice Generation for Registration Fee
			if (!empty($data['registration_cost']) && $data['registration_cost'] > 0) {
				$invoice_num = 'INV-REG-' . $id . '-' . time();
				$wpdb->insert("{$wpdb->prefix}control_fin_invoices", array(
					'patient_id' => $id,
					'invoice_number' => $invoice_num,
					'subtotal' => $data['registration_cost'],
					'total_amount' => $data['registration_cost'],
					'status' => 'pending',
					'invoice_date' => current_time('mysql'),
					'notes' => __('رسوم تسجيل تلقائية', 'control')
				));
				$inv_id = $wpdb->insert_id;
				$wpdb->insert("{$wpdb->prefix}control_fin_invoice_items", array(
					'invoice_id' => $inv_id,
					'description' => __('رسوم فتح ملف وتسجيل', 'control'),
					'quantity' => 1,
					'unit_price' => $data['registration_cost'],
					'total_price' => $data['registration_cost']
				));
			}
		}

		$this->send_success( array( 'id' => $id ) );
	}

	public function delete_patient() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );

		$id = intval( $_POST['id'] );
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}control_patients", array( 'id' => $id ) );
		$wpdb->delete( "{$wpdb->prefix}control_patient_assessments", array( 'patient_id' => $id ) );
		$wpdb->delete( "{$wpdb->prefix}control_patient_documents", array( 'patient_id' => $id ) );
		$wpdb->delete( "{$wpdb->prefix}control_patient_referrals", array( 'patient_id' => $id ) );

		// Cleanup financial records
		$wpdb->delete( "{$wpdb->prefix}control_fin_sessions", array( 'patient_id' => $id ) );
		$wpdb->delete( "{$wpdb->prefix}control_fin_packages", array( 'patient_id' => $id ) );

		$invoices = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_fin_invoices WHERE patient_id = %d", $id ) );
		if ( ! empty( $invoices ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $invoices ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}control_fin_invoice_items WHERE invoice_id IN ($placeholders)", ...$invoices ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}control_fin_payments WHERE invoice_id IN ($placeholders)", ...$invoices ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}control_fin_invoices WHERE id IN ($placeholders)", ...$invoices ) );
		}

		Control_Audit::log( 'delete_patient', "Deleted patient ID: $id" );
		$this->send_success();
	}

	public function save_patient_assessment() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'patient_id' => intval( $_POST['patient_id'] ),
			'test_name'   => sanitize_text_field( $_POST['test_name'] ),
			'test_result' => sanitize_textarea_field( $_POST['test_result'] ),
			'test_date'   => sanitize_text_field( $_POST['test_date'] ),
			'assessor_id' => Control_Auth::current_user()->id,
		);

		if ( $id ) {
			$wpdb->update( "{$wpdb->prefix}control_patient_assessments", $data, array( 'id' => $id ) );
		} else {
			$wpdb->insert( "{$wpdb->prefix}control_patient_assessments", $data );
		}

		$this->send_success();
	}

	public function delete_patient_assessment() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );

		$id = intval( $_POST['id'] );
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}control_patient_assessments", array( 'id' => $id ) );
		$this->send_success();
	}

	public function save_patient_document() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_basic') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$user = Control_Auth::current_user();
		$data = array(
			'patient_id'      => intval( $_POST['patient_id'] ),
			'doc_type'        => sanitize_text_field( $_POST['doc_type'] ?? '' ),
			'doc_category'    => sanitize_text_field( $_POST['doc_category'] ?? '' ),
			'specialist_role' => $user->role,
			'doc_url'         => esc_url_raw( $_POST['doc_url'] ),
			'doc_name'        => sanitize_text_field( $_POST['doc_name'] ),
		);

		$wpdb->insert( "{$wpdb->prefix}control_patient_documents", $data );
		$this->send_success();
	}

	public function delete_patient_document() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_basic') ) $this->send_error( 'Unauthorized', 403 );

		$id = intval( $_POST['id'] );
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}control_patient_documents", array( 'id' => $id ) );
		$this->send_success();
	}

	public function save_patient_referral() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$data = array(
			'patient_id'      => intval( $_POST['patient_id'] ),
			'from_department' => sanitize_text_field( $_POST['from_department'] ),
			'to_department'   => sanitize_text_field( $_POST['to_department'] ),
			'referral_date'   => sanitize_text_field( $_POST['referral_date'] ),
			'notes'           => sanitize_textarea_field( $_POST['notes'] ),
		);

		$wpdb->insert( "{$wpdb->prefix}control_patient_referrals", $data );
		$this->send_success();
	}

	public function delete_patient_referral() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );

		$id = intval( $_POST['id'] );
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}control_patient_referrals", array( 'id' => $id ) );
		$this->send_success();
	}

	/* --- Financial Module Handlers --- */

	public function save_fin_session() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'patient_id'    => intval( $_POST['patient_id'] ),
			'specialist_id' => sanitize_text_field( $_POST['specialist_id'] ),
			'session_date'  => sanitize_text_field( $_POST['session_date'] ),
			'duration_minutes' => intval( $_POST['duration_minutes'] ?? 60 ),
			'status'        => sanitize_text_field( $_POST['status'] ?? 'attended' ),
			'billing_status' => sanitize_text_field( $_POST['billing_status'] ?? 'unbilled' ),
			'package_id'    => !empty($_POST['package_id']) ? intval($_POST['package_id']) : null,
			'clinical_notes' => sanitize_textarea_field( $_POST['clinical_notes'] ?? '' ),
			'child_response' => sanitize_textarea_field( $_POST['child_response'] ?? '' ),
			'progress_percentage' => intval( $_POST['progress_percentage'] ?? 0 ),
			'plan_adjustments' => sanitize_textarea_field( $_POST['plan_adjustments'] ?? '' ),
		);

		if ( $id ) {
			$wpdb->update( "{$wpdb->prefix}control_fin_sessions", $data, array( 'id' => $id ) );
		} else {
			// Logic to deduct from package if specified
			if ($data['package_id']) {
				$pkg = $wpdb->get_row($wpdb->prepare("SELECT remaining_sessions FROM {$wpdb->prefix}control_fin_packages WHERE id = %d", $data['package_id']));
				if ($pkg && $pkg->remaining_sessions > 0) {
					$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}control_fin_packages SET remaining_sessions = remaining_sessions - 1 WHERE id = %d", $data['package_id']));
					$data['billing_status'] = 'deducted_from_package';
				}
			}
			$wpdb->insert( "{$wpdb->prefix}control_fin_sessions", $data );
		}
		$this->send_success();
	}

	public function delete_fin_session() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval( $_POST['id'] );

		// If it was deducted from a package, refund the session
		$session = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_fin_sessions WHERE id = %d", $id));
		if ($session && $session->billing_status === 'deducted_from_package' && $session->package_id) {
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}control_fin_packages SET remaining_sessions = remaining_sessions + 1 WHERE id = %d", $session->package_id));
		}

		$wpdb->delete( "{$wpdb->prefix}control_fin_sessions", array( 'id' => $id ) );
		$this->send_success();
	}

	public function save_fin_package() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_invoicing') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'patient_id'   => intval( $_POST['patient_id'] ),
			'package_name' => sanitize_text_field( $_POST['package_name'] ),
			'total_sessions' => intval( $_POST['total_sessions'] ),
			'price'        => floatval( $_POST['price'] ),
			'expiry_date'  => sanitize_text_field( $_POST['expiry_date'] ),
			'status'       => sanitize_text_field( $_POST['status'] ?? 'active' ),
		);
		if (!$id) {
			$data['remaining_sessions'] = $data['total_sessions'];
			$wpdb->insert( "{$wpdb->prefix}control_fin_packages", $data );
		} else {
			$wpdb->update( "{$wpdb->prefix}control_fin_packages", $data, array( 'id' => $id ) );
		}
		$this->send_success();
	}

	public function delete_fin_package() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_invoicing') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete( "{$wpdb->prefix}control_fin_packages", array( 'id' => intval($_POST['id']) ) );
		$this->send_success();
	}

	public function save_fin_invoice() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_invoicing') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$items = json_decode(wp_unslash($_POST['items']), true);

		$data = array(
			'patient_id'     => intval( $_POST['patient_id'] ),
			'invoice_number' => sanitize_text_field( $_POST['invoice_number'] ),
			'subtotal'       => floatval( $_POST['subtotal'] ),
			'discount'       => floatval( $_POST['discount'] ?? 0 ),
			'tax'            => floatval( $_POST['tax'] ?? 0 ),
			'total_amount'   => floatval( $_POST['total_amount'] ),
			'status'         => sanitize_text_field( $_POST['status'] ?? 'pending' ),
			'invoice_date'   => sanitize_text_field( $_POST['invoice_date'] ),
			'due_date'       => sanitize_text_field( $_POST['due_date'] ),
			'notes'          => sanitize_textarea_field( $_POST['notes'] ),
		);

		if ($id) {
			$wpdb->update("{$wpdb->prefix}control_fin_invoices", $data, array('id' => $id));
			$wpdb->delete("{$wpdb->prefix}control_fin_invoice_items", array('invoice_id' => $id));
		} else {
			$wpdb->insert("{$wpdb->prefix}control_fin_invoices", $data);
			$id = $wpdb->insert_id;
		}

		foreach ($items as $item) {
			$wpdb->insert("{$wpdb->prefix}control_fin_invoice_items", array(
				'invoice_id'  => $id,
				'description' => sanitize_text_field($item['description']),
				'quantity'    => intval($item['quantity']),
				'unit_price'  => floatval($item['unit_price']),
				'total_price' => floatval($item['total_price']),
			));
		}

		$this->send_success(array('id' => $id));
	}

	public function delete_fin_invoice() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_invoicing') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval($_POST['id']);
		$wpdb->delete("{$wpdb->prefix}control_fin_invoices", array('id' => $id));
		$wpdb->delete("{$wpdb->prefix}control_fin_invoice_items", array('invoice_id' => $id));
		$wpdb->delete("{$wpdb->prefix}control_fin_payments", array('invoice_id' => $id));
		$this->send_success();
	}

	public function save_fin_payment() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_invoicing') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$data = array(
			'invoice_id'     => intval( $_POST['invoice_id'] ),
			'amount'         => floatval( $_POST['amount'] ),
			'payment_method' => sanitize_text_field( $_POST['payment_method'] ),
			'transaction_id' => sanitize_text_field( $_POST['transaction_id'] ),
			'recorded_by'    => Control_Auth::current_user()->id,
		);
		$wpdb->insert("{$wpdb->prefix}control_fin_payments", $data);

		// Update invoice paid amount and status
		$invoice = $wpdb->get_row($wpdb->prepare("SELECT total_amount FROM {$wpdb->prefix}control_fin_invoices WHERE id = %d", $data['invoice_id']));
		$total_paid = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments WHERE invoice_id = %d", $data['invoice_id']));

		$status = 'partial';
		if ($total_paid >= $invoice->total_amount) $status = 'paid';

		$wpdb->update("{$wpdb->prefix}control_fin_invoices", array(
			'paid_amount' => $total_paid,
			'status'      => $status
		), array('id' => $data['invoice_id']));

		$this->send_success();
	}

	public function get_patient() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_basic') ) $this->send_error( 'Unauthorized', 403 );

		global $wpdb;
		$id = intval($_POST['id']);
		$patient = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patients WHERE id = %d", $id));

		if ($patient) {
			$this->send_success($patient);
		} else {
			$this->send_error('Patient not found');
		}
	}

	public function delete_fin_payment() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval($_POST['id']);
		$payment = $wpdb->get_row($wpdb->prepare("SELECT invoice_id FROM {$wpdb->prefix}control_fin_payments WHERE id = %d", $id));
		if ($payment) {
			$wpdb->delete("{$wpdb->prefix}control_fin_payments", array('id' => $id));
			$total_paid = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments WHERE invoice_id = %d", $payment->invoice_id)) ?: 0;
			$wpdb->update("{$wpdb->prefix}control_fin_invoices", array('paid_amount' => $total_paid, 'status' => ($total_paid > 0 ? 'partial' : 'pending')), array('id' => $payment->invoice_id));
		}
		$this->send_success();
	}

	public function save_fin_payroll() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'specialist_id'  => sanitize_text_field( $_POST['specialist_id'] ),
			'month'          => intval( $_POST['month'] ),
			'year'           => intval( $_POST['year'] ),
			'total_sessions' => intval( $_POST['total_sessions'] ),
			'base_salary'    => floatval( $_POST['base_salary'] ),
			'incentives'     => floatval( $_POST['incentives'] ),
			'deductions'     => floatval( $_POST['deductions'] ),
			'net_salary'     => floatval( $_POST['net_salary'] ),
			'payment_status' => sanitize_text_field( $_POST['payment_status'] ?? 'unpaid' ),
			'payment_date'   => !empty($_POST['payment_date']) ? sanitize_text_field($_POST['payment_date']) : null,
		);
		if ($id) {
			$wpdb->update("{$wpdb->prefix}control_fin_payroll", $data, array('id' => $id));
		} else {
			$wpdb->insert("{$wpdb->prefix}control_fin_payroll", $data);
		}
		$this->send_success();
	}

	public function delete_fin_payroll() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}control_fin_payroll", array('id' => intval($_POST['id'])));
		$this->send_success();
	}

	public function save_fin_expense() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'category'       => sanitize_text_field( $_POST['category'] ),
			'description'    => sanitize_textarea_field( $_POST['description'] ),
			'amount'         => floatval( $_POST['amount'] ),
			'expense_date'   => sanitize_text_field( $_POST['expense_date'] ),
			'is_recurring'   => intval( $_POST['is_recurring'] ?? 0 ),
			'attachment_url' => sanitize_text_field( $_POST['attachment_url'] ),
		);
		if ($id) {
			$wpdb->update("{$wpdb->prefix}control_fin_expenses", $data, array('id' => $id));
		} else {
			$wpdb->insert("{$wpdb->prefix}control_fin_expenses", $data);
		}
		$this->send_success();
	}

	public function delete_fin_expense() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}control_fin_expenses", array('id' => intval($_POST['id'])));
		$this->send_success();
	}

	public function get_fin_report_data() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('finance_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$revenue = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments") ?: 0;
		$expenses = $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_expenses") ?: 0;
		$payroll = $wpdb->get_var("SELECT SUM(net_salary) FROM {$wpdb->prefix}control_fin_payroll WHERE payment_status = 'paid'") ?: 0;
		$outstanding = $wpdb->get_var("SELECT SUM(total_amount - paid_amount) FROM {$wpdb->prefix}control_fin_invoices") ?: 0;

		$this->send_success(array(
			'revenue' => $revenue,
			'expenses' => $expenses + $payroll,
			'net_profit' => $revenue - ($expenses + $payroll),
			'outstanding' => $outstanding
		));
	}

	public function submit_kiosk_registration() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		global $wpdb;

		// Utility to handle arrays from multi-select/checkboxes
		$get_val = function($key, $type = 'text') {
			$val = wp_unslash($_POST[$key] ?? '');
			if (is_array($val)) {
				return implode(', ', array_map('sanitize_text_field', $val));
			}

			if ($type === 'email') return sanitize_email($val);
			if ($type === 'textarea') return sanitize_textarea_field($val);
			return sanitize_text_field($val);
		};

		$data = array(
			'full_name'      => $get_val('full_name'),
			'name_first'     => $get_val('name_first'),
			'name_second'    => $get_val('name_second'),
			'name_third'     => $get_val('name_third'),
			'name_last'      => $get_val('name_last'),
			'dob'            => $get_val('dob'),
			'gender'         => $get_val('gender'),
			'nationality'    => $get_val('nationality'),
			'preferred_lang' => $get_val('k_lang'),
			'country_residence' => $get_val('country_residence'),
			'city_residence' => $get_val('city_residence'),
			'national_id'    => $get_val('national_id'),
			'guardian_name'  => $get_val('guardian_name'),
			'guardian_relationship' => $get_val('guardian_relationship'),
			'father_phone'   => $get_val('father_phone'),
			'email'          => $get_val('email', 'email'),
			'emergency_contact' => $get_val('emergency_contact'),
			'blood_type'     => $get_val('blood_type'),
			'address'        => $get_val('address', 'textarea'),
			'diag_prev'      => $get_val('diag_prev'),
			'prev_rehab_centers' => $get_val('prev_rehab_centers'),
			'motor_delay'    => $get_val('motor_delay'),
			'speech_delay'   => $get_val('speech_delay'),
			'chronic_conditions' => $get_val('chronic_conditions'),
			'current_medications' => $get_val('current_medications', 'textarea'),
			'eval_social'    => $get_val('eval_social'),
			'eval_language'  => $get_val('eval_language'),
			'intake_reason'  => $get_val('intake_reason', 'textarea'),
			'national_id'    => $get_val('national_id'),
			'intake_status'  => 'pending',
			'case_status'    => 'waiting_list',
			'temp_id'        => 'REQ-' . strtoupper(wp_generate_password(8, false)),
			'registration_cost'  => floatval($get_val('registration_cost')),
			'payment_model'     => $get_val('payment_model'),
			'billing_type'      => $get_val('billing_type'),
			'payment_frequency' => $get_val('payment_frequency'),
			'amount_per_cycle'  => floatval($get_val('amount_per_cycle')),
			'total_expected_revenue' => floatval($get_val('total_expected_revenue')),
		);

		// Duplicate National ID check for Kiosk
		if ( ! empty( $data['national_id'] ) ) {
			$dup = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}control_patients WHERE national_id = %s", $data['national_id'] ) );
			if ( $dup ) $this->send_error( __('عذراً، هذا الرقم (الهوية/الإقامة) مسجل مسبقاً في النظام. يرجى التواصل مع الاستقبال.', 'control') );
		}

		$result = $wpdb->insert("{$wpdb->prefix}control_patients", $data);
		if ($result === false) {
			$this->send_error(__('فشل حفظ البيانات في قاعدة البيانات. يرجى مراجعة الإدارة.', 'control') . ' ' . $wpdb->last_error);
		}
		$id = $wpdb->insert_id;

		// Automatic Invoice Generation for Registration Fee in Kiosk Mode
		if (!empty($data['registration_cost']) && $data['registration_cost'] > 0) {
			$invoice_num = 'INV-REG-' . $id . '-' . time();
			$wpdb->insert("{$wpdb->prefix}control_fin_invoices", array(
				'patient_id' => $id,
				'invoice_number' => $invoice_num,
				'subtotal' => $data['registration_cost'],
				'total_amount' => $data['registration_cost'],
				'status' => 'pending',
				'invoice_date' => current_time('mysql'),
				'notes' => __('رسوم تسجيل تلقائية من الكشك', 'control')
			));
			$inv_id = $wpdb->insert_id;
			$wpdb->insert("{$wpdb->prefix}control_fin_invoice_items", array(
				'invoice_id' => $inv_id,
				'description' => __('رسوم فتح ملف وتسجيل (كشك)', 'control'),
				'quantity' => 1,
				'unit_price' => $data['registration_cost'],
				'total_price' => $data['registration_cost']
			));
		}

		Control_Audit::log('kiosk_intake', "New intake request from Kiosk: {$data['full_name']}");
		$this->send_success( array( 'id' => $id ) );
	}

	public function update_session_lang() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		$lang = sanitize_text_field( $_POST['lang'] ?? 'ar' );
		$_SESSION['control_lang'] = $lang;
		$this->send_success();
	}

	public function update_intake_status() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$id = intval($_POST['id']);
		$status = sanitize_text_field($_POST['status']);

		$wpdb->update("{$wpdb->prefix}control_patients", array('intake_status' => $status), array('id' => $id));

		if ($status === 'approved') {
			Control_Audit::log('intake_approved', "Intake request approved for ID: $id");
		}

		$this->send_success();
	}

	public function restore_patient() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$id = intval($_POST['id']);
		$wpdb->update("{$wpdb->prefix}control_patients", array('case_status' => 'active'), array('id' => $id));

		Control_Audit::log('restore_patient', "Patient record restored to Active status for ID: $id");
		$this->send_success();
	}

	public function close_patient() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$id = intval($_POST['id']);
		$wpdb->update("{$wpdb->prefix}control_patients", array('case_status' => 'closed'), array('id' => $id));

		Control_Audit::log('close_patient', "Patient record closed for ID: $id");
		$this->send_success();
	}

	public function save_clinical_note() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::is_logged_in() ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$user = Control_Auth::current_user();
		$data = array(
			'patient_id'    => intval($_POST['patient_id']),
			'author_id'     => $user->id,
			'author_name'   => $user->name,
			'author_role'   => $user->role,
			'note_category' => sanitize_text_field($_POST['note_category']),
			'content'       => sanitize_textarea_field($_POST['content']),
			'created_at'    => current_time('mysql')
		);

		$wpdb->insert("{$wpdb->prefix}control_patient_clinical_notes", $data);
		$this->send_success();
	}

	public function delete_clinical_note() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}control_patient_clinical_notes", array('id' => intval($_POST['id'])));
		$this->send_success();
	}

	public function save_patient_evaluation() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$data = array(
			'patient_id'    => intval($_POST['patient_id']),
			'specialist_id' => sanitize_text_field($_POST['specialist_id']),
			'eval_type'     => sanitize_text_field($_POST['eval_type']),
			'eval_date'     => sanitize_text_field($_POST['eval_date']),
			'structured_data' => sanitize_textarea_field($_POST['structured_data']),
			'notes'         => sanitize_textarea_field($_POST['notes']),
		);

		$wpdb->insert("{$wpdb->prefix}control_patient_evaluations", $data);
		$this->send_success();
	}

	public function delete_patient_evaluation() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}control_patient_evaluations", array('id' => intval($_POST['id'])));
		$this->send_success();
	}

	public function save_treatment_plan() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_view_clinical') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$patient_id = intval($_POST['patient_id']);

		// Inactivate previous plans
		$wpdb->update("{$wpdb->prefix}control_patient_treatment_plans", array('status' => 'archived'), array('patient_id' => $patient_id));

		$last_version = $wpdb->get_var($wpdb->prepare("SELECT MAX(version) FROM {$wpdb->prefix}control_patient_treatment_plans WHERE patient_id = %d", $patient_id)) ?: 0;

		$data = array(
			'patient_id'    => $patient_id,
			'st_goals'      => sanitize_textarea_field($_POST['st_goals']),
			'lt_goals'      => sanitize_textarea_field($_POST['lt_goals']),
			'therapy_types' => sanitize_text_field($_POST['therapy_types']),
			'frequency'     => sanitize_text_field($_POST['frequency']),
			'version'       => $last_version + 1,
			'status'        => 'active'
		);

		$wpdb->insert("{$wpdb->prefix}control_patient_treatment_plans", $data);
		$this->send_success();
	}

	public function save_patient_schedule() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;

		$data = array(
			'patient_id'    => intval($_POST['patient_id']),
			'day_of_week'   => sanitize_text_field($_POST['day_of_week']),
			'time_slot'     => sanitize_text_field($_POST['time_slot']),
			'session_type'  => sanitize_text_field($_POST['session_type']),
		);

		$wpdb->insert("{$wpdb->prefix}control_patient_schedules", $data);
		$this->send_success();
	}

	public function delete_patient_schedule() {
		check_ajax_referer( 'control_nonce', 'nonce' );
		if ( ! Control_Auth::has_permission('pediatric_manage') ) $this->send_error( 'Unauthorized', 403 );
		global $wpdb;
		$wpdb->delete("{$wpdb->prefix}control_patient_schedules", array('id' => intval($_POST['id'])));
		$this->send_success();
	}
}

new Control_Ajax();
