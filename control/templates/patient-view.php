<div class="view-section-container">
<?php
global $wpdb;
$patient_id = intval( $_GET['id'] ?? 0 );
$patient = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patients WHERE id = %d", $patient_id ) );

if ( ! $patient || ! is_object($patient) ) {
    echo '<div class="control-card">' . __('المريض غير موجود.', 'control') . '</div>';
    return;
}

$can_view_clinical = Control_Auth::has_permission('pediatric_view_clinical');
$can_manage = Control_Auth::has_permission('pediatric_manage');

// Tab Definitions (Removed Developmental)
$tabs = array(
    'demographics' => array('label' => Control_I18n::t('file_demographics'), 'icon' => 'admin-users', 'clinical' => false),
    'medical'      => array('label' => Control_I18n::t('file_medical'), 'icon' => 'heart', 'clinical' => true),
    'assessments'  => array('label' => Control_I18n::t('file_assessments'), 'icon' => 'clipboard', 'clinical' => true),
    'diagnosis'    => array('label' => Control_I18n::t('file_diagnosis'), 'icon' => 'visibility', 'clinical' => true),
    'treatment'    => array('label' => Control_I18n::t('file_treatment'), 'icon' => 'welcome-learn-more', 'clinical' => true),
    'sessions'     => array('label' => Control_I18n::t('file_sessions'), 'icon' => 'calendar-alt', 'clinical' => true),
    'behavior'     => array('label' => Control_I18n::t('file_behavior'), 'icon' => 'groups', 'clinical' => true),
    'referrals'    => array('label' => Control_I18n::t('referrals'), 'icon' => 'random', 'clinical' => true),
    'attachments'  => array('label' => Control_I18n::t('file_attachments'), 'icon' => 'paperclip', 'clinical' => false),
    'attendance'   => array('label' => Control_I18n::t('file_attendance'), 'icon' => 'clock', 'clinical' => false),
    'billing'      => array('label' => Control_I18n::t('file_billing'), 'icon' => 'cart', 'clinical' => false),
    'notes'        => array('label' => Control_I18n::t('file_notes'), 'icon' => 'edit', 'clinical' => false),
    'staff'        => array('label' => Control_I18n::t('file_staff'), 'icon' => 'businessperson', 'clinical' => false),
);
?>

<div class="patient-file-layout" style="display:flex; gap:30px; align-items:flex-start;">

    <!-- Right Sidebar Navigation -->
    <div class="p-internal-sidebar" style="width:280px; flex-shrink:0; position:sticky; top:100px;">
        <div class="control-card" style="padding:20px; border-radius:20px; background:var(--control-primary); color:#fff; margin-bottom:20px; text-align:center;">
            <div style="width:80px; height:80px; border-radius:20px; overflow:hidden; border:3px solid rgba(255,255,255,0.2); margin:0 auto 15px; background:#fff;">
                <?php if($patient->profile_photo): ?><img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;"><?php else: ?><div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:40px;"></span></div><?php endif; ?>
            </div>
            <h3 style="margin:0; color:#fff; font-size:1.1rem;"><?php echo esc_html($patient->full_name); ?></h3>
            <div style="margin-top:10px; font-size:0.7rem; opacity:0.7; font-family:monospace;">ID: #<?php echo esc_html($patient->permanent_id ?: $patient->id); ?></div>
        </div>

        <div class="control-card" style="padding:10px; border-radius:20px; background:#fff; border:1px solid #f1f5f9; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
            <?php foreach($tabs as $id => $tab):
                if ($tab['clinical'] && ! $can_view_clinical && ! $can_manage) continue; ?>
                <div class="p-nav-item <?php echo $id === 'demographics' ? 'active' : ''; ?>" data-tab="tab-<?php echo $id; ?>" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s;">
                    <div class="nav-icon-box" style="width:32px; height:32px; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:var(--control-muted);"><span class="dashicons dashicons-<?php echo $tab['icon']; ?>"></span></div>
                    <span style="font-weight:700; flex:1; font-size:0.85rem;"><?php echo $tab['label']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="flex:1; min-width:0;">
        <div id="patient-file-content">

            <!-- Tab: Demographics (Structured Rows with Inline Placeholders) -->
            <div id="tab-demographics" class="p-file-pane active">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_demographics'); ?></h4>

                        <!-- Row 1: Names -->
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="name_first" placeholder="<?php echo Control_I18n::t('placeholder_first_name'); ?> *" value="<?php echo esc_attr($patient->name_first); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_second" placeholder="<?php echo Control_I18n::t('placeholder_father_name'); ?> *" value="<?php echo esc_attr($patient->name_second); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_last" placeholder="<?php echo Control_I18n::t('placeholder_family_name'); ?> *" value="<?php echo esc_attr($patient->name_last); ?>" required></div>
                        </div>

                        <!-- Row 2: DOB / Age / Gender -->
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="date" name="dob" class="dob-input-calc" value="<?php echo esc_attr($patient->dob); ?>" title="<?php echo Control_I18n::t('dob'); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" class="age-display-field" readonly style="background:#f8fafc; font-weight:700; color:var(--control-primary);" placeholder="<?php echo Control_I18n::t('age'); ?>"></div>
                            <div class="wiz-field no-label">
                                <select name="gender">
                                    <option value="" disabled <?php selected($patient->gender, ''); ?>><?php echo Control_I18n::t('gender'); ?></option>
                                    <option value="male" <?php selected($patient->gender, 'male'); ?>><?php echo Control_I18n::t('male'); ?></option>
                                    <option value="female" <?php selected($patient->gender, 'female'); ?>><?php echo Control_I18n::t('female'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Nationality / Country / Emirate -->
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="nationality" placeholder="<?php echo Control_I18n::t('nationality'); ?>" value="<?php echo esc_attr($patient->nationality); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="country_residence" placeholder="<?php echo Control_I18n::t('country_residence'); ?>" value="<?php echo esc_attr($patient->country_residence); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="area_district" placeholder="<?php echo Control_I18n::t('emirate_state'); ?>" value="<?php echo esc_attr($patient->area_district); ?>"></div>
                        </div>

                        <!-- Row 4: ID -->
                        <div class="wiz-field no-label"><input type="text" name="national_id" placeholder="<?php echo Control_I18n::t('placeholder_national_id'); ?>" value="<?php echo esc_attr($patient->national_id); ?>"></div>

                        <!-- Row 5: Address Refinement -->
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="street_name" placeholder="<?php echo Control_I18n::t('street_name'); ?>" value="<?php echo esc_attr($patient->street_name); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="area_district" placeholder="<?php echo Control_I18n::t('area_district'); ?>" value="<?php echo esc_attr($patient->area_district); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="city_residence" placeholder="<?php echo Control_I18n::t('city_residence'); ?>" value="<?php echo esc_attr($patient->city_residence); ?>"></div>
                        </div>

                        <div style="margin-top:40px; padding-top:25px; border-top:2px solid #f8fafc;">
                            <h5 style="margin:0 0 25px 0; font-weight:800; color:var(--control-muted);"><?php echo Control_I18n::t('contact_info'); ?></h5>
                            <div class="wiz-grid-3">
                                <div class="wiz-field no-label"><input type="text" name="guardian_name" placeholder="<?php echo Control_I18n::t('guardian_name'); ?>" value="<?php echo esc_attr($patient->guardian_name); ?>"></div>
                                <div class="wiz-field no-label"><input type="tel" name="father_phone" placeholder="<?php echo Control_I18n::t('father_phone'); ?> *" value="<?php echo esc_attr($patient->father_phone); ?>" required></div>
                                <div class="wiz-field no-label"><input type="tel" name="mother_phone" placeholder="<?php echo Control_I18n::t('mother_phone'); ?>" value="<?php echo esc_attr($patient->mother_phone); ?>"></div>
                            </div>
                        </div>

                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Medical History (Clinical Grade) -->
            <div id="tab-medical" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_medical'); ?></h4>

                        <!-- Row 1 -->
                        <div class="wiz-grid">
                            <div class="wiz-field no-label">
                                <select name="birth_type">
                                    <option value="" disabled <?php selected($patient->birth_type, ''); ?>><?php echo Control_I18n::t('birth_type'); ?></option>
                                    <option value="normal" <?php selected($patient->birth_type, 'normal'); ?>><?php echo Control_I18n::t('birth_normal'); ?></option>
                                    <option value="csection" <?php selected($patient->birth_type, 'csection'); ?>><?php echo Control_I18n::t('birth_csection'); ?></option>
                                </select>
                            </div>
                            <div class="wiz-field no-label"><textarea name="birth_complications" placeholder="<?php echo Control_I18n::t('birth_complications'); ?>" rows="2"><?php echo esc_textarea($patient->birth_complications); ?></textarea></div>
                        </div>

                        <!-- Row 2 -->
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="chronic_conditions" placeholder="<?php echo Control_I18n::t('chronic_conditions'); ?> (Multi-select)" rows="2"><?php echo esc_textarea($patient->chronic_conditions); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="current_medications" placeholder="<?php echo Control_I18n::t('medications'); ?>" rows="2"><?php echo esc_textarea($patient->current_medications); ?></textarea></div>
                        </div>

                        <!-- Row 3 -->
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><input type="text" name="drug_allergies" placeholder="<?php echo Control_I18n::t('drug_allergies'); ?> (Food / Med / None)" value="<?php echo esc_attr($patient->drug_allergies); ?>"></div>
                            <div class="wiz-field no-label"><textarea name="medical_surgeries" placeholder="<?php echo Control_I18n::t('surgeries'); ?>" rows="2"><?php echo esc_textarea($patient->medical_surgeries); ?></textarea></div>
                        </div>

                        <!-- Row 4 -->
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="neurological_conditions" placeholder="<?php echo Control_I18n::t('neuro_conditions'); ?>" rows="2"><?php echo esc_textarea($patient->neurological_conditions); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="sensory_issues" placeholder="<?php echo Control_I18n::t('sensory_issues'); ?>" rows="2"><?php echo esc_textarea($patient->sensory_issues); ?></textarea></div>
                        </div>

                        <!-- Row 5: Notes -->
                        <div class="wiz-field no-label"><textarea name="pregnancy_history" placeholder="<?php echo Control_I18n::t('pregnancy_history'); ?> / <?php echo Control_I18n::t('placeholder_notes'); ?>" rows="3"><?php echo esc_textarea($patient->pregnancy_history); ?></textarea></div>

                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Attendance (Session Calendar & Tracking) -->
            <div id="tab-attendance" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:35px; border-bottom:2px solid #f8fafc; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_attendance'); ?></h4>
                        <button class="control-btn" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><span class="dashicons dashicons-calendar-alt" style="margin-left:5px;"></span><?php _e('جدولة جلسة جديدة', 'control'); ?></button>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:20px; padding:30px; text-align:center; margin-bottom:30px;">
                        <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:10px; margin-top:20px;">
                            <?php $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            foreach($days as $day): ?><div style="font-weight:800; font-size:0.7rem; color:var(--control-muted);"><?php echo $day; ?></div><?php endforeach; ?>
                            <?php for($i=1; $i<=31; $i++): ?>
                                <div style="aspect-ratio:1; display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; border-radius:10px; font-size:0.8rem; cursor:pointer;" onmouseover="this.style.borderColor='var(--control-accent)'" onmouseout="this.style.borderColor='#eee'"><?php echo $i; ?></div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <table class="control-table">
                        <thead><tr><th><?php _e('تاريخ الجلسة', 'control'); ?></th><th><?php _e('الحالة', 'control'); ?></th><th><?php _e('الأخصائي', 'control'); ?></th><th><?php _e('إجراءات', 'control'); ?></th></tr></thead>
                        <tbody>
                            <tr><td>2023-10-25 10:00 AM</td><td><span style="color:#059669; font-weight:800;">Present</span></td><td>Dr. Sarah</td><td><button class="control-btn" style="padding:4px 10px; font-size:0.65rem; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إعادة جدولة', 'control'); ?></button></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Billing (Integrated) -->
            <div id="tab-billing" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_billing'); ?></h4>

                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label">
                                <select name="billing_plan">
                                    <option value="" disabled <?php selected($patient->billing_plan, ''); ?>><?php echo Control_I18n::t('billing_plan'); ?></option>
                                    <option value="session" <?php selected($patient->billing_plan, 'session'); ?>><?php echo Control_I18n::t('session_based'); ?></option>
                                    <option value="weekly" <?php selected($patient->billing_plan, 'weekly'); ?>><?php echo Control_I18n::t('weekly'); ?></option>
                                    <option value="monthly" <?php selected($patient->billing_plan, 'monthly'); ?>><?php echo Control_I18n::t('monthly'); ?></option>
                                </select>
                            </div>
                            <div class="wiz-field no-label"><input type="number" name="registration_cost" placeholder="<?php echo Control_I18n::t('registration_cost'); ?>" value="<?php echo esc_attr($patient->registration_cost); ?>"></div>
                            <div class="wiz-field no-label"><input type="number" name="amount_per_cycle" placeholder="<?php echo Control_I18n::t('amount_per_cycle'); ?>" value="<?php echo esc_attr($patient->amount_per_cycle); ?>"></div>
                        </div>

                        <div style="background:var(--control-primary); border-radius:20px; padding:30px; margin-top:20px; display:flex; justify-content:space-around; color:#fff; text-align:center;">
                            <div><small style="opacity:0.6; display:block; margin-bottom:5px;"><?php _e('إجمالي الفواتير', 'control'); ?></small><strong style="font-size:1.4rem; color:var(--control-accent);"><?php echo number_format($patient->total_expected_revenue, 2); ?></strong></div>
                            <div><small style="opacity:0.6; display:block; margin-bottom:5px;"><?php _e('المدفوع', 'control'); ?></small><strong style="font-size:1.4rem; color:#10b981;"><?php
                                $paid = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments p JOIN {$wpdb->prefix}control_fin_invoices i ON p.invoice_id = i.id WHERE i.patient_id = %d", $patient->id)) ?: 0;
                                echo number_format($paid, 2); ?></strong></div>
                            <div><small style="opacity:0.6; display:block; margin-bottom:5px;"><?php _e('المتبقي', 'control'); ?></small><strong style="font-size:1.4rem; color:#ef4444;"><?php echo number_format($patient->total_expected_revenue - $paid, 2); ?></strong></div>
                        </div>

                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Staff (Dynamic Integration) -->
            <div id="tab-staff" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_staff'); ?></h4>

                        <div class="wiz-field no-label">
                            <label style="font-size:0.75rem; color:var(--control-muted); font-weight:800; margin-bottom:10px; display:block;"><?php echo Control_I18n::t('primary_specialist'); ?></label>
                            <?php $all_staff = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff ORDER BY first_name ASC"); ?>
                            <select name="assigned_specialists">
                                <option value=""><?php _e('ابحث واختر الأخصائي المسؤول...', 'control'); ?></option>
                                <?php foreach($all_staff as $s): ?>
                                    <option value="<?php echo $s->id; ?>" <?php selected($patient->assigned_specialists, $s->id); ?>><?php echo esc_html($s->first_name . ' ' . $s->last_name . ' (' . $s->role . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="wiz-field no-label">
                            <label style="font-size:0.75rem; color:var(--control-muted); font-weight:800; margin:20px 0 10px; display:block;"><?php echo Control_I18n::t('case_team'); ?></label>
                            <textarea name="intake_notes" placeholder="<?php echo Control_I18n::t('case_team'); ?>..." rows="3"><?php echo esc_textarea($patient->intake_notes); ?></textarea>
                        </div>

                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Assessments -->
            <div id="tab-assessments" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f8fafc; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_assessments'); ?></h4>
                        <button class="control-btn" onclick="jQuery('#assessment-modal').css('display','flex')" style="background:var(--control-accent); color:var(--control-primary) !important; font-size:0.75rem; font-weight:800; border:none; border-radius:10px;"><span class="dashicons dashicons-plus" style="margin-left:5px;"></span><?php _e('إضافة تقييم', 'control'); ?></button>
                    </div>
                    <table class="control-table">
                        <thead><tr><th><?php _e('الاختبار', 'control'); ?></th><th><?php _e('النتيجة', 'control'); ?></th><th><?php _e('التاريخ', 'control'); ?></th><th></th></tr></thead>
                        <tbody>
                            <?php $assessments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_assessments WHERE patient_id = %d ORDER BY test_date DESC", $patient->id));
                            if($assessments): foreach($assessments as $a): ?>
                                <tr><td><strong><?php echo esc_html($a->test_name); ?></strong></td><td><?php echo nl2br(esc_html($a->test_result)); ?></td><td><?php echo esc_html($a->test_date); ?></td><td style="text-align:left;"><button class="delete-assessment-btn" data-id="<?php echo $a->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button></td></tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--control-muted);"><?php _e('لا توجد تقييمات.', 'control'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Diagnosis -->
            <div id="tab-diagnosis" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_diagnosis'); ?></h4>
                        <div class="wiz-field no-label"><textarea name="initial_diagnosis" placeholder="<?php echo Control_I18n::t('initial_diagnosis'); ?>" rows="2" style="font-weight:700;"><?php echo esc_textarea($patient->initial_diagnosis); ?></textarea></div>
                        <div class="wiz-field no-label"><textarea name="diagnosis_secondary" placeholder="<?php echo Control_I18n::t('secondary_diagnosis'); ?>" rows="3"><?php echo esc_textarea($patient->diagnosis_secondary); ?></textarea></div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><input type="text" name="diagnosis_severity" placeholder="<?php echo Control_I18n::t('severity_level'); ?>" value="<?php echo esc_attr($patient->diagnosis_severity); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="external_diagnosis_source" placeholder="<?php echo Control_I18n::t('diagnosis_source'); ?>" value="<?php echo esc_attr($patient->external_diagnosis_source); ?>"></div>
                        </div>
                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Treatment Plan -->
            <div id="tab-treatment" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_treatment'); ?></h4>
                        <div class="wiz-field no-label"><textarea name="tp_goals_short" placeholder="<?php echo Control_I18n::t('short_goals'); ?>" rows="4"><?php echo esc_textarea($patient->tp_goals_short); ?></textarea></div>
                        <div class="wiz-field no-label"><textarea name="tp_goals_long" placeholder="<?php echo Control_I18n::t('long_goals'); ?>" rows="4"><?php echo esc_textarea($patient->tp_goals_long); ?></textarea></div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><input type="text" name="tp_frequency" placeholder="<?php echo Control_I18n::t('tp_frequency'); ?>" value="<?php echo esc_attr($patient->tp_frequency); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="routing_dept" placeholder="<?php echo Control_I18n::t('routing_dept'); ?>" value="<?php echo esc_attr($patient->routing_dept); ?>"></div>
                        </div>
                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Sessions -->
            <div id="tab-sessions" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f8fafc; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_sessions'); ?></h4>
                        <button class="control-btn" onclick="jQuery('#session-modal').css('display','flex')" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><?php _e('تسجيل جلسة', 'control'); ?></button>
                    </div>
                    <div class="session-timeline" style="display:flex; flex-direction:column; gap:20px;">
                        <?php $sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_fin_sessions WHERE patient_id = %d ORDER BY session_date DESC", $patient->id));
                        if($sessions): foreach($sessions as $s): ?>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:15px; padding:20px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><strong><?php echo esc_html($s->session_date); ?></strong><span style="color:#059669; font-weight:800;"><?php echo $s->progress_percentage; ?>%</span></div>
                                <div style="font-size:0.85rem; color:#475569;"><?php echo nl2br(esc_html($s->clinical_notes)); ?></div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد جلسات.', 'control'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Behavior Plan -->
            <div id="tab-behavior" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_behavior'); ?></h4>
                        <div class="wiz-field no-label"><textarea name="bp_target_behaviors" placeholder="<?php echo Control_I18n::t('target_behaviors'); ?>..." rows="3"><?php echo esc_textarea($patient->bp_target_behaviors); ?></textarea></div>
                        <div class="wiz-field no-label"><textarea name="bp_reinforcement_strategies" placeholder="<?php echo Control_I18n::t('reinforcement'); ?>..." rows="3"><?php echo esc_textarea($patient->bp_reinforcement_strategies); ?></textarea></div>
                        <div class="wiz-field no-label"><textarea name="bp_intervention_techniques" placeholder="<?php echo Control_I18n::t('interventions'); ?>..." rows="3"><?php echo esc_textarea($patient->bp_intervention_techniques); ?></textarea></div>
                        <div style="margin-top:30px; padding-top:20px; border-top:1px solid #f8fafc;">
                            <h5 style="margin-bottom:20px; font-weight:800; color:var(--control-muted);"><?php echo Control_I18n::t('followup_reports'); ?></h5>
                            <div style="background:#f1f5f9; border-radius:15px; padding:20px; text-align:center; color:var(--control-muted); font-size:0.85rem; border:2px dashed #cbd5e1;"><?php _e('سجل التقارير السلوكية الدورية سيظهر هنا بناءً على تقييمات الأخصائي.', 'control'); ?></div>
                        </div>
                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Referrals -->
            <div id="tab-referrals" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('referrals'); ?></h4>
                        <button class="control-btn" onclick="jQuery('#referral-modal').css('display','flex')" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><?php _e('إضافة تحويل', 'control'); ?></button>
                    </div>
                    <table class="control-table">
                        <thead><tr><th><?php _e('من', 'control'); ?></th><th><?php _e('إلى', 'control'); ?></th><th><?php _e('التاريخ', 'control'); ?></th><th></th></tr></thead>
                        <tbody>
                            <?php $referrals = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_referrals WHERE patient_id = %d", $patient->id));
                            if($referrals): foreach($referrals as $r): ?>
                                <tr><td><?php echo esc_html($r->from_department); ?></td><td><strong><?php echo esc_html($r->to_department); ?></strong></td><td><?php echo esc_html($r->referral_date); ?></td><td style="text-align:left;"><button class="delete-referral-btn" data-id="<?php echo $r->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button></td></tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:20px;"><?php _e('لا يوجد سجلات.', 'control'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Notes -->
            <div id="tab-notes" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_notes'); ?></h4>
                        <div class="wiz-field no-label"><textarea name="notes_specialist" placeholder="<?php echo Control_I18n::t('specialist_notes'); ?>" rows="5"><?php echo esc_textarea($patient->notes_specialist); ?></textarea></div>
                        <div class="wiz-field no-label"><textarea name="notes_guardian" placeholder="<?php echo Control_I18n::t('guardian_notes'); ?>" rows="5"><?php echo esc_textarea($patient->notes_guardian); ?></textarea></div>
                        <div class="save-feedback" style="display:none; margin-bottom:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function calculateAge(dob) {
        if (!dob) return "";
        const birthDate = new Date(dob);
        const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) { years--; months += 12; }
        return years + " " + "<?php echo Control_I18n::t('years'); ?>" + "، " + months + " " + "<?php echo Control_I18n::t('months'); ?>";
    }

    $('.dob-input-calc').on('change', function() {
        $(this).closest('form').find('.age-display-field').val(calculateAge($(this).val()));
    }).trigger('change');

    $('.p-nav-item').on('click', function() {
        $('.p-nav-item').removeClass('active'); $(this).addClass('active');
        const tab = $(this).data('tab'); $('.p-file-pane').hide(); $('#' + tab).fadeIn(300);
    });

    $('.clinical-save-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const $feedback = $form.find('.save-feedback');
        $btn.prop('disabled', true).text('...');

        $.post(control_ajax.ajax_url, $form.serialize() + '&action=control_save_patient&id=' + $form.data('patient-id') + '&nonce=' + control_ajax.nonce, (res) => {
            $btn.prop('disabled', false).text('<?php echo Control_I18n::t("save"); ?>');
            if(res.success) {
                $feedback.text('<?php _e("تم حفظ التعديلات بنجاح في هذا القسم.", "control"); ?>').fadeIn().delay(3000).fadeOut();
            } else {
                alert(res.data.message || 'Error occurred');
            }
        });
    });
});
</script>

<style>
.wiz-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
.wiz-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.wiz-field.no-label { margin-bottom: 12px; }
.wiz-field input, .wiz-field select, .wiz-field textarea {
    width: 100%; padding: 14px 18px; border-radius: 14px; border: 1.5px solid #eef2f6;
    font-size: 0.95rem; background:#fcfdfe; transition:0.3s; color:#1e293b;
}
.wiz-field input:focus { border-color: var(--control-accent); background:#fff; outline:none; box-shadow:0 0 0 4px rgba(212,175,55,0.05); }
.wiz-field input::placeholder { color:#94a3b8; font-size:0.85rem; }
.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.p-nav-item:not(.active):hover { background: #f8fafc; transform: translateX(-5px); }
.control-table th { background:#f8fafc; color:var(--control-muted); font-size:0.7rem; font-weight:800; text-transform:uppercase; padding:15px; border-bottom:1px solid #e2e8f0; }
@media (max-width: 1024px) { .patient-file-layout { flex-direction: column; } .p-internal-sidebar { width: 100% !important; position: static !important; } }
@media (max-width: 768px) { .wiz-grid, .wiz-grid-3 { grid-template-columns: 1fr; } }
</style>
</div>
