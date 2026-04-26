<div class="view-section-container">
<?php
global $wpdb;
$patient_id = intval( $_GET['id'] ?? 0 );
$patient = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patients WHERE id = %d", $patient_id ) );

if ( ! $patient || ! is_object($patient) ) {
    echo '<div class="control-card">' . __('المريض غير موجود.', 'control') . '</div>';
    return;
}

$current_user = Control_Auth::current_user();
$user_role = $current_user->role ?? '';
$can_view_clinical = Control_Auth::has_permission('pediatric_view_clinical');
$can_manage = Control_Auth::has_permission('pediatric_manage');

// Role-Based Module Access Logic
$specialist_modules = array(
    'psych' => array('roles' => ['psych_assessor', 'admin', 'center_director'], 'label' => Control_I18n::t('psychological_eval'), 'icon' => 'groups'),
    'ot'    => array('roles' => ['occupational_therapist', 'admin', 'center_director'], 'label' => Control_I18n::t('occupational_eval'), 'icon' => 'welcome-learn-more'),
    'phys'  => array('roles' => ['physical_rehab', 'sports_therapy', 'admin', 'center_director'], 'label' => Control_I18n::t('physical_sports_eval'), 'icon' => 'chart-line'),
    'beh'   => array('roles' => ['behavior_modification', 'admin', 'center_director'], 'label' => Control_I18n::t('behavioral_eval'), 'icon' => 'visibility'),
);

$tabs = array(
    'demographics' => array('label' => Control_I18n::t('file_demographics'), 'icon' => 'admin-users', 'specialist' => false),
    'medical'      => array('label' => Control_I18n::t('file_medical'), 'icon' => 'heart', 'specialist' => false),
    'eval_psych'   => array('label' => $specialist_modules['psych']['label'], 'icon' => 'groups', 'specialist' => 'psych'),
    'eval_ot'      => array('label' => $specialist_modules['ot']['label'], 'icon' => 'welcome-learn-more', 'specialist' => 'ot'),
    'eval_phys'    => array('label' => $specialist_modules['phys']['label'], 'icon' => 'chart-line', 'specialist' => 'phys'),
    'eval_beh'     => array('label' => $specialist_modules['beh']['label'], 'icon' => 'visibility', 'specialist' => 'beh'),
    'treatment'    => array('label' => Control_I18n::t('file_treatment'), 'icon' => 'welcome-add-page', 'specialist' => false),
    'sessions'     => array('label' => Control_I18n::t('file_sessions'), 'icon' => 'calendar-alt', 'specialist' => false),
    'reports'      => array('label' => Control_I18n::t('file_reports'), 'icon' => 'analytics', 'specialist' => false),
    'attachments'  => array('label' => Control_I18n::t('file_attachments'), 'icon' => 'paperclip', 'specialist' => false),
    'attendance'   => array('label' => Control_I18n::t('file_attendance'), 'icon' => 'clock', 'specialist' => false),
    'billing'      => array('label' => Control_I18n::t('file_billing'), 'icon' => 'cart', 'specialist' => false),
    'notes'        => array('label' => Control_I18n::t('file_notes'), 'icon' => 'edit', 'specialist' => false),
    'staff'        => array('label' => Control_I18n::t('file_staff'), 'icon' => 'businessperson', 'specialist' => false),
);
?>

<div class="patient-file-layout" style="display:flex; gap:30px; align-items:flex-start;">

    <!-- Right Sidebar Navigation -->
    <div class="p-internal-sidebar" style="width:280px; flex-shrink:0; position:sticky; top:100px;">
        <div class="control-card profile-header-card" style="padding:20px; border-radius:20px; background:var(--control-primary); color:#fff; margin-bottom:20px; text-align:center;">
            <div id="clickable-profile-img" style="width:100px; height:100px; border-radius:25px; overflow:hidden; border:4px solid rgba(255,255,255,0.2); margin:0 auto 15px; background:#fff; cursor:pointer; position:relative;" title="<?php echo Control_I18n::t('upload_photo'); ?>">
                <?php if($patient->profile_photo): ?><img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;"><?php else: ?><div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:50px;"></span></div><?php endif; ?>
                <div style="position:absolute; bottom:0; left:0; width:100%; background:rgba(0,0,0,0.5); font-size:0.6rem; padding:4px 0; color:#fff;"><?php echo Control_I18n::t('edit_title'); ?></div>
            </div>
            <h3 style="margin:0; color:#fff; font-size:1.2rem; font-weight:800;"><?php echo esc_html($patient->full_name); ?></h3>

            <div style="display:flex; flex-wrap:wrap; gap:5px; justify-content:center; margin-top:15px;">
                <span class="patient-status-badge status-<?php echo esc_attr($patient->case_status); ?>">
                    <?php
                        $status_labels = ['active' => 'نشط', 'evaluation_only' => 'تقييم', 'waiting_list' => 'انتظار', 'dropped_out' => 'منقطع', 'completed' => 'تأهيل', 'closed' => 'مغلق'];
                        echo $status_labels[$patient->case_status] ?? $patient->case_status;
                    ?>
                </span>
                <span class="badge-pastel badge-nat"><?php echo $patient->nationality; ?></span>
                <span class="badge-pastel badge-age" id="sidebar-age-badge">--</span>
                <span class="badge-pastel badge-diagnosis"><?php echo esc_html($patient->initial_diagnosis ?: 'No Diagnosis'); ?></span>
            </div>
        </div>

        <div class="control-card" style="padding:10px; border-radius:20px; background:#fff; border:1px solid #f1f5f9; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
            <?php foreach($tabs as $id => $tab):
                if ($tab['specialist']) {
                    $mod = $specialist_modules[$tab['specialist']];
                    if (!in_array($user_role, $mod['roles'])) continue;
                }
                if ($id !== 'demographics' && ! $can_view_clinical && ! $can_manage) continue;
            ?>
                <div class="p-nav-item <?php echo $id === 'demographics' ? 'active' : ''; ?>" data-tab="tab-<?php echo $id; ?>" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                    <div class="nav-icon-box" style="width:32px; height:32px; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:var(--control-muted);"><span class="dashicons dashicons-<?php echo $tab['icon']; ?>"></span></div>
                    <span style="font-weight:700; flex:1; font-size:0.85rem;"><?php echo $tab['label']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="flex:1; min-width:0;">
        <div id="patient-file-content">

            <!-- Tab: Demographics (Professional Grid Layout) -->
            <div id="tab-demographics" class="p-file-pane active">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">

                    <!-- Container 1: Child Identity -->
                    <div class="control-card profile-section-box pastel-box-primary" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid var(--control-primary);">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;"><span class="dashicons dashicons-admin-users"></span> <?php echo Control_I18n::t('basic_info'); ?></h4>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="name_first" placeholder="<?php echo Control_I18n::t('placeholder_first_name'); ?> *" value="<?php echo esc_attr($patient->name_first); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_second" placeholder="<?php echo Control_I18n::t('placeholder_father_name'); ?> *" value="<?php echo esc_attr($patient->name_second); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_last" placeholder="<?php echo Control_I18n::t('placeholder_family_name'); ?> *" value="<?php echo esc_attr($patient->name_last); ?>" required></div>
                        </div>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="date" name="dob" class="dob-input-calc" value="<?php echo esc_attr($patient->dob); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" class="age-display-field" readonly style="background:rgba(255,255,255,0.5); font-weight:700;" placeholder="<?php echo Control_I18n::t('age'); ?>"></div>
                            <div class="wiz-field no-label">
                                <select name="gender">
                                    <option value="male" <?php selected($patient->gender, 'male'); ?>><?php echo Control_I18n::t('male'); ?></option>
                                    <option value="female" <?php selected($patient->gender, 'female'); ?>><?php echo Control_I18n::t('female'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="nationality" placeholder="<?php echo Control_I18n::t('nationality'); ?>" value="<?php echo esc_attr($patient->nationality); ?>"></div>
                            <div class="wiz-field no-label">
                                <select name="child_lang_primary">
                                    <option value=""><?php echo Control_I18n::t('child_lang_primary'); ?>...</option>
                                    <option value="ar" <?php selected($patient->child_lang_primary, 'ar'); ?>>العربية</option>
                                    <option value="en" <?php selected($patient->child_lang_primary, 'en'); ?>>English</option>
                                </select>
                            </div>
                            <div class="wiz-field no-label">
                                <select name="child_lang_secondary">
                                    <option value=""><?php echo Control_I18n::t('child_lang_secondary'); ?>...</option>
                                    <option value="ar" <?php selected($patient->child_lang_secondary, 'ar'); ?>>العربية</option>
                                    <option value="en" <?php selected($patient->child_lang_secondary, 'en'); ?>>English</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Container 2: Communication -->
                    <div class="control-card profile-section-box pastel-box-success" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid #10b981;">
                        <h4 style="margin:0 0 30px 0; color:#059669; font-weight:800; display:flex; align-items:center; gap:10px;"><span class="dashicons dashicons-phone"></span> <?php echo Control_I18n::t('contact_info'); ?></h4>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="tel" name="father_phone" placeholder="<?php echo Control_I18n::t('father_phone'); ?> *" value="<?php echo esc_attr($patient->father_phone); ?>" required></div>
                            <div class="wiz-field no-label"><input type="tel" name="mother_phone" placeholder="<?php echo Control_I18n::t('mother_phone'); ?>" value="<?php echo esc_attr($patient->mother_phone); ?>"></div>
                            <div class="wiz-field no-label"><input type="email" name="email" placeholder="<?php echo Control_I18n::t('email'); ?>" value="<?php echo esc_attr($patient->email); ?>"></div>
                        </div>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label">
                                <select name="comm_lang_primary">
                                    <option value=""><?php echo Control_I18n::t('comm_lang_primary'); ?>...</option>
                                    <option value="ar" <?php selected($patient->comm_lang_primary, 'ar'); ?>>العربية</option>
                                    <option value="en" <?php selected($patient->comm_lang_primary, 'en'); ?>>English</option>
                                </select>
                            </div>
                            <div class="wiz-field no-label"><input type="text" name="city_residence" placeholder="<?php echo Control_I18n::t('city_residence'); ?>" value="<?php echo esc_attr($patient->city_residence); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="area_district" placeholder="<?php echo Control_I18n::t('area_district'); ?>" value="<?php echo esc_attr($patient->area_district); ?>"></div>
                        </div>
                        <div class="wiz-field no-label"><textarea name="address" placeholder="<?php echo Control_I18n::t('placeholder_address'); ?>" rows="2"><?php echo esc_textarea($patient->address); ?></textarea></div>
                    </div>

                    <div style="text-align:left;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:15px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                </form>
            </div>

            <!-- Tab: Medical History (Enhanced Clinical Groups) -->
            <div id="tab-medical" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">

                    <!-- Group: Birth & Development -->
                    <div class="control-card profile-section-box pastel-box-warning" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid #f59e0b;">
                        <h4 style="margin:0 0 30px 0; color:#b45309; font-weight:800;"><?php _e('تاريخ الولادة والتطور النمائي', 'control'); ?></h4>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><select name="birth_type"><option value="normal" <?php selected($patient->birth_type,'normal'); ?>><?php echo Control_I18n::t('birth_normal'); ?></option><option value="csection" <?php selected($patient->birth_type,'csection'); ?>><?php echo Control_I18n::t('birth_csection'); ?></option></select></div>
                            <div class="wiz-field no-label"><textarea name="birth_complications" placeholder="<?php echo Control_I18n::t('birth_complications'); ?>" rows="2"><?php echo esc_textarea($patient->birth_complications); ?></textarea></div>
                        </div>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="milestones_walking" placeholder="<?php echo Control_I18n::t('walking'); ?>" value="<?php echo esc_attr($patient->milestones_walking); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="milestones_speaking" placeholder="<?php echo Control_I18n::t('speaking'); ?>" value="<?php echo esc_attr($patient->milestones_speaking); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="milestones_sitting" placeholder="<?php echo Control_I18n::t('sitting'); ?>" value="<?php echo esc_attr($patient->milestones_sitting); ?>"></div>
                        </div>
                    </div>

                    <!-- Group: Clinical Conditions & Meds -->
                    <div class="control-card profile-section-box pastel-box-danger" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid #ef4444;">
                        <h4 style="margin:0 0 30px 0; color:#b91c1c; font-weight:800;"><?php _e('الحالات الطبية والأدوية', 'control'); ?></h4>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="chronic_conditions" placeholder="<?php echo Control_I18n::t('chronic_conditions'); ?>" rows="2"><?php echo esc_textarea($patient->chronic_conditions); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="current_medications" placeholder="<?php echo Control_I18n::t('medications'); ?>" rows="2"><?php echo esc_textarea($patient->current_medications); ?></textarea></div>
                        </div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="drug_allergies" placeholder="<?php echo Control_I18n::t('drug_allergies'); ?>" rows="2"><?php echo esc_textarea($patient->drug_allergies); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="medical_surgeries" placeholder="<?php echo Control_I18n::t('surgeries'); ?>" rows="2"><?php echo esc_textarea($patient->medical_surgeries); ?></textarea></div>
                        </div>
                    </div>

                    <div style="text-align:left;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:15px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                </form>
            </div>

            <!-- Specialized Evaluation Modules (Multi-Entry Timeline) -->
            <?php foreach($specialist_modules as $mod_id => $mod): if(in_array($user_role, $mod['roles'])): ?>
                <div id="tab-eval_<?php echo $mod_id; ?>" class="p-file-pane" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo $mod['label']; ?></h4>
                        <button class="control-btn add-eval-btn" data-type="<?php echo $mod_id; ?>" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php echo Control_I18n::t('add_eval_entry'); ?></button>
                    </div>
                    <div class="eval-timeline-container" id="eval-timeline-<?php echo $mod_id; ?>">
                        <?php
                        $evals = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_evaluations WHERE patient_id = %d AND eval_type = %s ORDER BY eval_date DESC", $patient->id, $mod_id));
                        if($evals): foreach($evals as $ev): ?>
                            <div class="control-card eval-entry-card" style="border-radius:24px; padding:30px; margin-bottom:20px; border-right:4px solid var(--control-accent);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:1.5px solid #f8fafc; padding-bottom:15px;">
                                    <div><div style="font-weight:800; font-size:1rem;"><?php echo $ev->eval_date; ?></div><small style="color:var(--control-muted);"><?php echo $ev->specialist_id; ?></small></div>
                                    <div style="display:flex; gap:8px;">
                                        <button class="control-btn" style="background:#f1f5f9; color:#475569 !important; border:none; padding:6px 12px; border-radius:10px; font-size:0.7rem;"><span class="dashicons dashicons-pdf" style="margin-left:5px;"></span><?php echo Control_I18n::t('eval_download_pdf'); ?></button>
                                        <button class="delete-eval-btn" data-id="<?php echo $ev->id; ?>" style="background:none; border:none; color:#cbd5e1; cursor:pointer;" onmouseover="this.style.color='#ef4444'"><span class="dashicons dashicons-trash"></span></button>
                                    </div>
                                </div>
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                                    <div><h5 style="font-size:0.8rem; color:var(--control-muted); margin-bottom:10px;"><?php echo Control_I18n::t('eval_structured_data'); ?></h5><div style="font-size:0.9rem; line-height:1.6; white-space:pre-wrap; background:#f8fafc; padding:15px; border-radius:12px;"><?php echo esc_html($ev->structured_data); ?></div></div>
                                    <div><h5 style="font-size:0.8rem; color:var(--control-muted); margin-bottom:10px;"><?php echo Control_I18n::t('file_notes'); ?></h5><div style="font-size:0.9rem; line-height:1.6; white-space:pre-wrap; background:#f8fafc; padding:15px; border-radius:12px;"><?php echo esc_html($ev->notes); ?></div></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="text-align:center; padding:60px; background:#fcfdfe; border:2px dashed #f1f5f9; border-radius:24px; color:var(--control-muted);"><p style="font-weight:700;"><?php _e('لا توجد تقييمات مسجلة.', 'control'); ?></p></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; endforeach; ?>

            <!-- Tab: Treatment Plan (Smart Versioning) -->
            <div id="tab-treatment" class="p-file-pane" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_treatment'); ?></h4>
                    <button class="control-btn" onclick="jQuery('#tp-modal').css('display','flex')" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-edit" style="margin-left:5px;"></span><?php _e('تحديث الخطة', 'control'); ?></button>
                </div>
                <?php $tp = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_treatment_plans WHERE patient_id = %d AND status = 'active' ORDER BY version DESC LIMIT 1", $patient->id));
                if($tp): ?>
                    <div class="control-card profile-section-box pastel-box-primary" style="border-radius:24px; padding:35px; border-right:6px solid var(--control-accent);">
                         <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; border-bottom:1px solid rgba(0,0,0,0.05); padding-bottom:15px;">
                            <span class="badge-pastel" style="background:#fef3c7; color:#92400e;"><?php echo Control_I18n::t('treatment_version'); ?>: <?php echo $tp->version; ?></span>
                            <small style="color:var(--control-muted); font-weight:700;"><?php _e('تاريخ الإصدار:', 'control'); ?> <?php echo date('Y-m-d', strtotime($tp->created_at)); ?></small>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                            <div><h5 style="color:var(--control-primary); margin-bottom:10px;"><?php echo Control_I18n::t('short_goals'); ?></h5><div style="background:#fff; padding:20px; border-radius:15px; font-size:0.9rem; line-height:1.7;"><?php echo nl2br(esc_html($tp->st_goals)); ?></div></div>
                            <div><h5 style="color:var(--control-primary); margin-bottom:10px;"><?php echo Control_I18n::t('long_goals'); ?></h5><div style="background:#fff; padding:20px; border-radius:15px; font-size:0.9rem; line-height:1.7;"><?php echo nl2br(esc_html($tp->lt_goals)); ?></div></div>
                        </div>
                    </div>
                <?php else: ?><div style="text-align:center; padding:60px; background:#fcfdfe; border:2px dashed #f1f5f9; border-radius:24px; color:var(--control-muted);"><p style="font-weight:700;"><?php _e('لا توجد خطة علاجية مفعلة.', 'control'); ?></p></div><?php endif; ?>
            </div>

            <!-- Tab: Sessions & Timeline -->
            <div id="tab-sessions" class="p-file-pane" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_sessions'); ?></h4>
                    <button class="control-btn" onclick="jQuery('#session-entry-modal').css('display','flex')" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('تسجيل جلسة', 'control'); ?></button>
                </div>
                <div class="sessions-timeline" style="display:flex; flex-direction:column; gap:15px;">
                    <?php $sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_fin_sessions WHERE patient_id = %d ORDER BY session_date DESC", $patient->id));
                    if($sessions): foreach($sessions as $s): ?>
                        <div class="control-card session-entry-card" style="border-radius:20px; padding:25px; border-right:5px solid #10b981; background:#fff;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <div style="font-weight:800; color:var(--control-primary);"><?php echo $s->session_date; ?> <small style="margin-right:10px; color:var(--control-muted); font-weight:400;"><?php echo $s->specialist_id; ?></small></div>
                                <span class="badge-pastel" style="background:#f1f5f9; color:#475569;"><?php echo $s->progress_percentage; ?>%</span>
                            </div>
                            <p style="font-size:0.85rem; line-height:1.6; color:#475569; margin:0;"><?php echo nl2br(esc_html($s->clinical_notes)); ?></p>
                        </div>
                    <?php endforeach; else: ?><div style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد جلسات مسجلة.', 'control'); ?></div><?php endif; ?>
                </div>
            </div>

            <!-- Tab: Reports & Media -->
            <div id="tab-reports" class="p-file-pane" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_reports'); ?></h4>
                    <button class="control-btn add-document-btn" data-cat="report" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-upload" style="margin-left:5px;"></span><?php _e('رفع تقرير', 'control'); ?></button>
                </div>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                    <?php $reports = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_documents WHERE patient_id = %d AND doc_category = 'report' ORDER BY uploaded_at DESC", $patient->id));
                    if($reports): foreach($reports as $rep): ?>
                        <div class="control-card report-card-prof" style="border-radius:20px; padding:20px; border:1.5px solid #f1f5f9;">
                            <div style="display:flex; gap:15px; align-items:center; margin-bottom:15px;">
                                <div style="width:45px; height:45px; background:#f0f9ff; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#0369a1;"><span class="dashicons dashicons-pdf" style="font-size:25px;"></span></div>
                                <div style="flex:1; min-width:0;"><div style="font-weight:800; font-size:0.85rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($rep->doc_name); ?></div><small style="color:var(--control-muted);"><?php echo $rep->uploaded_at; ?></small></div>
                            </div>
                            <div style="display:flex; gap:10px;"><a href="<?php echo esc_url($rep->doc_url); ?>" target="_blank" class="control-btn" style="flex:1; padding:8px; font-size:0.75rem; font-weight:800;"><?php _e('معاينة', 'control'); ?></a><button class="delete-doc-btn" data-id="<?php echo $rep->id; ?>" style="background:none; border:none; color:#cbd5e1; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button></div>
                        </div>
                    <?php endforeach; else: ?><div style="grid-column:1/-1; text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد تقارير.', 'control'); ?></div><?php endif; ?>
                </div>
            </div>

            <!-- Tab: Attendance & Schedule -->
            <div id="tab-attendance" class="p-file-pane" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_attendance'); ?></h4>
                    <button class="control-btn" onclick="jQuery('#schedule-modal').css('display','flex')" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-calendar-alt" style="margin-left:5px;"></span><?php _e('إدارة المواعيد', 'control'); ?></button>
                </div>
                <div class="control-card" style="border-radius:24px; padding:35px; background:#f8fafc; border:1px solid #e2e8f0;">
                    <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:12px;">
                        <?php $days_map = ['Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'];
                        $schedule = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_schedules WHERE patient_id = %d", $patient->id));
                        $sched_by_day = []; foreach($schedule as $sc) { $sched_by_day[$sc->day_of_week][] = $sc; }
                        foreach($days_map as $en => $ar): ?>
                            <div style="background:#fff; padding:15px; border-radius:15px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.02);">
                                <div style="font-weight:800; font-size:0.75rem; margin-bottom:10px; color:var(--control-primary);"><?php echo $ar; ?></div>
                                <?php if(!empty($sched_by_day[$en])): foreach($sched_by_day[$en] as $s): ?>
                                    <div style="background:var(--control-primary-soft); color:var(--control-primary); font-size:0.65rem; padding:6px; border-radius:8px; margin-bottom:5px; font-weight:800;"><?php echo $s->time_slot; ?></div>
                                <?php endforeach; else: ?><div style="font-size:0.6rem; color:#cbd5e1;">-</div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Billing -->
            <div id="tab-billing" class="p-file-pane" style="display:none;">
                 <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_billing'); ?></h4>
                    <button class="control-btn" onclick="jQuery('#invoice-modal').css('display','flex')" style="background:var(--control-primary); border:none; border-radius:12px; font-size:0.8rem; font-weight:800;"><span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إنشاء فاتورة', 'control'); ?></button>
                </div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom:30px;">
                    <div class="control-card" style="padding:30px; text-align:center; background:#f0fdf4; border:1px solid #dcfce7; border-radius:20px;">
                        <small style="color:#166534; font-weight:800; display:block; margin-bottom:5px;"><?php echo Control_I18n::t('billing_total'); ?></small>
                        <strong style="font-size:1.8rem; color:#14532d;"><?php echo number_format($patient->total_expected_revenue, 2); ?></strong>
                    </div>
                    <div class="control-card" style="padding:30px; text-align:center; background:#eff6ff; border:1px solid #dbeafe; border-radius:20px;">
                        <small style="color:#1d4ed8; font-weight:800; display:block; margin-bottom:5px;"><?php echo Control_I18n::t('billing_paid'); ?></small>
                        <?php $paid = $wpdb->get_var($wpdb->prepare("SELECT SUM(paid_amount) FROM {$wpdb->prefix}control_fin_invoices WHERE patient_id = %d", $patient->id)) ?: 0; ?>
                        <strong style="font-size:1.8rem; color:#1e3a8a;"><?php echo number_format($paid, 2); ?></strong>
                    </div>
                    <div class="control-card" style="padding:30px; text-align:center; background:#fef2f2; border:1px solid #fee2e2; border-radius:20px;">
                        <small style="color:#991b1b; font-weight:800; display:block; margin-bottom:5px;"><?php echo Control_I18n::t('billing_balance'); ?></small>
                        <strong style="font-size:1.8rem; color:#7f1d1d;"><?php echo number_format($patient->total_expected_revenue - $paid, 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Tab: Staff -->
            <div id="tab-staff" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px; background:#f8fafc;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_staff'); ?></h4>
                        <div class="wiz-field no-label">
                            <?php $staff = $wpdb->get_results("SELECT id, first_name, last_name, role, profile_image FROM {$wpdb->prefix}control_staff ORDER BY first_name ASC"); ?>
                            <select name="assigned_specialists">
                                <option value=""><?php echo Control_I18n::t('primary_specialist'); ?>...</option>
                                <?php foreach($staff as $s): ?>
                                    <option value="<?php echo $s->id; ?>" <?php selected($patient->assigned_specialists, $s->id); ?>><?php echo esc_html($s->first_name . ' ' . $s->last_name . ' (' . $s->role . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="text-align:left; margin-top:20px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Modals -->
<div id="eval-entry-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10007; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:40px; border-radius:24px; box-shadow:0 30px 60px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('add_eval_entry'); ?></h3>
        <form id="new-eval-entry-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <input type="hidden" name="eval_type" id="new-eval-type" value="">
            <div class="wiz-grid"><div class="wiz-field no-label"><input type="date" name="eval_date" value="<?php echo date('Y-m-d'); ?>" required></div><div class="wiz-field no-label"><input type="text" name="specialist_id" value="<?php echo $current_user->name; ?>" readonly></div></div>
            <div class="wiz-field no-label"><textarea name="structured_data" placeholder="<?php echo Control_I18n::t('eval_structured_data'); ?>" rows="4" required></textarea></div>
            <div class="wiz-field no-label"><textarea name="notes" placeholder="<?php echo Control_I18n::t('file_notes'); ?>" rows="3"></textarea></div>
            <div style="display:flex; gap:12px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; padding:12px;"><?php _e('حفظ التقييم', 'control'); ?></button><button type="button" onclick="jQuery('#eval-entry-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<div id="tp-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10008; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:40px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('تحديث الخطة العلاجية', 'control'); ?></h3>
        <form id="treatment-plan-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <div class="wiz-field no-label"><textarea name="st_goals" placeholder="<?php echo Control_I18n::t('short_goals'); ?>" rows="3" required></textarea></div>
            <div class="wiz-field no-label"><textarea name="lt_goals" placeholder="<?php echo Control_I18n::t('long_goals'); ?>" rows="3" required></textarea></div>
            <div class="wiz-grid"><div class="wiz-field no-label"><input type="text" name="therapy_types" placeholder="أنواع العلاج" required></div><div class="wiz-field no-label"><input type="text" name="frequency" placeholder="الدورية" required></div></div>
            <div style="display:flex; gap:12px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; padding:12px;"><?php _e('اعتماد الخطة', 'control'); ?></button><button type="button" onclick="jQuery('#tp-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<div id="session-entry-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10009; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:40px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('تسجيل جلسة جديدة', 'control'); ?></h3>
        <form id="session-entry-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <div class="wiz-grid"><div class="wiz-field no-label"><input type="date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required></div><div class="wiz-field no-label"><input type="text" name="specialist_id" value="<?php echo $current_user->name; ?>" readonly></div></div>
            <div class="wiz-field no-label"><textarea name="clinical_notes" placeholder="<?php echo Control_I18n::t('clinical_notes'); ?>" rows="3" required></textarea></div>
            <div class="wiz-field no-label"><input type="number" name="progress_percentage" placeholder="التقدم المحقق (0-100)" required></div>
            <div style="display:flex; gap:12px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; padding:12px;"><?php _e('حفظ الجلسة', 'control'); ?></button><button type="button" onclick="jQuery('#session-entry-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<div id="schedule-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10010; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:600px; padding:40px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('إضافة موعد مجدول', 'control'); ?></h3>
        <form id="schedule-entry-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <div class="wiz-grid">
                <div class="wiz-field no-label"><select name="day_of_week" required><?php foreach($days_map as $en => $ar): ?><option value="<?php echo $en; ?>"><?php echo $ar; ?></option><?php endforeach; ?></select></div>
                <div class="wiz-field no-label"><input type="text" name="time_slot" placeholder="10:00 AM" required></div>
            </div>
            <div class="wiz-field no-label"><input type="text" name="session_type" placeholder="نوع الجلسة (مثلاً: تخاطب)" required></div>
            <div style="display:flex; gap:12px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; padding:12px;"><?php _e('إضافة للمجدول', 'control'); ?></button><button type="button" onclick="jQuery('#schedule-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<div id="clinical-note-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10006; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:40px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('تدوين ملاحظة مهنية', 'control'); ?></h3>
        <form id="file-clinical-note-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <div class="wiz-field no-label"><select name="note_category" required><option value="clinical"><?php echo Control_I18n::t('clinical_notes'); ?></option><option value="behavioral"><?php echo Control_I18n::t('behavioral_notes'); ?></option><option value="administrative"><?php echo Control_I18n::t('administrative_notes'); ?></option></select></div>
            <div class="wiz-field no-label"><textarea name="content" placeholder="<?php _e('اكتب نص الملاحظة هنا...', 'control'); ?>" rows="6" required></textarea></div>
            <div style="display:flex; gap:12px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; padding:12px;"><?php _e('حفظ الملاحظة', 'control'); ?></button><button type="button" onclick="jQuery('#clinical-note-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#clickable-profile-img').on('click', function() {
        const frame = wp.media({ title: '<?php echo Control_I18n::t('upload_photo'); ?>', multiple: false }).open();
        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            $.post(control_ajax.ajax_url, { action:'control_save_patient', id:<?php echo $patient->id; ?>, profile_photo:attachment.url, nonce:control_ajax.nonce }, (res) => {
                if(res.success) $('#clickable-profile-img').html('<img src="'+attachment.url+'" style="width:100%; height:100%; object-fit:cover;">');
            });
        });
    });

    function calculateAge(dob) { if (!dob) return ""; const birthDate = new Date(dob); const today = new Date(); let years = today.getFullYear() - birthDate.getFullYear(); let months = today.getMonth() - birthDate.getMonth(); if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) { years--; months += 12; } return years + " " + "<?php echo Control_I18n::t('years'); ?>"; }
    $('.dob-input-calc').on('change', function() { const age = calculateAge($(this).val()); $(this).closest('form').find('.age-display-field').val(age); $('#sidebar-age-badge').text(age); }).trigger('change');
    $('.p-nav-item').on('click', function() { $('.p-nav-item').removeClass('active'); $(this).addClass('active'); $('.p-file-pane').hide(); $('#' + $(this).data('tab')).fadeIn(300); });

    $('.clinical-save-form').on('submit', function(e) {
        e.preventDefault(); const $form = $(this); const $btn = $form.find('button[type="submit"]'); $btn.prop('disabled', true).text('...');
        $.post(control_ajax.ajax_url, $form.serialize() + '&action=control_save_patient&id=' + $form.data('patient-id') + '&nonce=' + control_ajax.nonce, (res) => {
            $btn.prop('disabled', false).text('<?php echo Control_I18n::t("save"); ?>');
            if(res.success) alert('<?php _e("تم الحفظ بنجاح.", "control"); ?>'); else alert(res.data.message);
        });
    });

    $('.add-eval-btn').on('click', function() { $('#new-eval-type').val($(this).data('type')); $('#eval-entry-modal').css('display', 'flex'); });
    $('#new-eval-entry-form').on('submit', function(e) { e.preventDefault(); $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_evaluation&nonce=' + control_ajax.nonce, (res) => { if(res.success) location.reload(); else alert(res.data.message); }); });
    $(document).on('click', '.delete-eval-btn', function() { if(confirm('حذف التقييم؟')) $.post(control_ajax.ajax_url, { action:'control_delete_patient_evaluation', id:$(this).data('id'), nonce:control_ajax.nonce }, () => location.reload()); });
    $('#treatment-plan-form').on('submit', function(e) { e.preventDefault(); $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_treatment_plan&nonce=' + control_ajax.nonce, (res) => { if(res.success) location.reload(); else alert(res.data.message); }); });
    $('#session-entry-form').on('submit', function(e) { e.preventDefault(); $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_session&nonce=' + control_ajax.nonce, (res) => { if(res.success) location.reload(); else alert(res.data.message); }); });
    $('#schedule-entry-form').on('submit', function(e) { e.preventDefault(); $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_schedule&nonce=' + control_ajax.nonce, (res) => { if(res.success) location.reload(); else alert(res.data.message); }); });
    $('#file-clinical-note-form').on('submit', function(e) { e.preventDefault(); $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_clinical_note&nonce=' + control_ajax.nonce, (res) => { if(res.success) location.reload(); else alert(res.data.message); }); });
    $(document).on('click', '.delete-doc-btn', function() { if(confirm('حذف الوثيقة؟')) $.post(control_ajax.ajax_url, { action:'control_delete_patient_document', id:$(this).data('id'), nonce:control_ajax.nonce }, () => location.reload()); });
});
</script>

<style>
.patient-file-layout { padding: 10px 0; }
.wiz-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.wiz-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
.wiz-field.no-label { margin-bottom: 12px; }
.wiz-field input, .wiz-field select, .wiz-field textarea, .control-modal input, .control-modal textarea, .control-modal select { width: 100%; padding: 14px 18px; border-radius: 14px; border: 1.5px solid #eef2f6; font-size: 0.9rem; background:#fcfdfe; transition:0.3s; color:#1e293b; box-sizing: border-box; }
.wiz-field input:focus, .wiz-field select:focus, .wiz-field textarea:focus { border-color: var(--control-accent); background:#fff; outline:none; box-shadow: 0 0 0 4px rgba(212,175,55,0.05); }

/* Vertical Dropdown Text Fix */
.wiz-field select { -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: left 1rem center; background-size: 1em; padding-left: 2.5rem; line-height: normal; }

.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }

/* Pastel Box Classes */
.pastel-box-primary { background: rgba(239, 246, 255, 0.4); border: 1px solid #dbeafe; }
.pastel-box-success { background: rgba(236, 253, 245, 0.4); border: 1px solid #d1fae5; }
.pastel-box-warning { background: rgba(255, 251, 235, 0.4); border: 1px solid #fef3c7; }
.pastel-box-danger { background: rgba(254, 242, 242, 0.4); border: 1px solid #fee2e2; }

.control-table th { background:#f8fafc; font-size:0.7rem; font-weight:800; padding:12px; border-bottom:1px solid #e2e8f0; }
.report-card-prof:hover { border-color: var(--control-primary); transform: translateY(-3px); transition: 0.3s; }
.eval-entry-card { border: 1px solid #f1f5f9; transition: 0.3s; }
.eval-entry-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }

/* Header Capsules Styling Alignment (Exact match with cards) */
.patient-status-badge, .badge-pastel {
    font-size: 0.6rem !important;
    padding: 3px 10px !important;
    border-radius: 8px !important;
    font-weight: 800 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(0,0,0,0.01);
}
.patient-status-badge.status-active { background: rgba(220, 252, 231, 0.6); color: #166534; }
.patient-status-badge.status-waiting_list { background: rgba(219, 234, 254, 0.6); color: #1e40af; }
.patient-status-badge.status-dropped_out { background: rgba(254, 226, 226, 0.6); color: #991b1b; }
.patient-status-badge.status-completed { background: rgba(240, 253, 244, 0.6); color: #166534; }
.patient-status-badge.status-closed { background: rgba(241, 245, 249, 0.6); color: #475569; }
.patient-status-badge.status-evaluation_only { background: rgba(254, 243, 199, 0.6); color: #92400e; }

.badge-age { background: rgba(243, 232, 255, 0.6); color: #7e22ce; }
.badge-gender { background: rgba(224, 242, 254, 0.6); color: #0369a1; }
.badge-nat { background: rgba(253, 242, 248, 0.6); color: #be185d; }
.badge-diagnosis { background: rgba(255, 241, 242, 0.6); color: #e11d48; }

@media (max-width: 1024px) { .patient-file-layout { flex-direction: column; } .p-internal-sidebar { width: 100% !important; position: static !important; } }
@media (max-width: 768px) { .wiz-grid, .wiz-grid-3 { grid-template-columns: 1fr; } }
</style>
</div>
