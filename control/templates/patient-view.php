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
    'assessments'  => array('label' => Control_I18n::t('file_assessments'), 'icon' => 'clipboard', 'specialist' => false),
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
        <div class="control-card" style="padding:20px; border-radius:20px; background:var(--control-primary); color:#fff; margin-bottom:20px; text-align:center;">
            <div id="clickable-profile-img" style="width:100px; height:100px; border-radius:25px; overflow:hidden; border:4px solid rgba(255,255,255,0.2); margin:0 auto 15px; background:#fff; cursor:pointer; position:relative;" title="<?php echo Control_I18n::t('upload_photo'); ?>">
                <?php if($patient->profile_photo): ?><img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;"><?php else: ?><div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:50px;"></span></div><?php endif; ?>
                <div style="position:absolute; bottom:0; left:0; width:100%; background:rgba(0,0,0,0.5); font-size:0.6rem; padding:4px 0; color:#fff;"><?php echo Control_I18n::t('edit_title'); ?></div>
            </div>
            <h3 style="margin:0; color:#fff; font-size:1.2rem; font-weight:800;"><?php echo esc_html($patient->full_name); ?></h3>

            <div style="display:flex; flex-wrap:wrap; gap:5px; justify-content:center; margin-top:15px;">
                <span class="patient-status-badge status-<?php echo esc_attr($patient->case_status); ?>" style="font-size:0.55rem; padding:2px 8px; border-radius:6px; background:rgba(255,255,255,0.1); color:#fff; font-weight:800;"><?php echo $patient->case_status; ?></span>
                <span class="badge-pastel" style="background:rgba(255,255,255,0.1); color:#fff; border:none; font-size:0.55rem;"><?php echo $patient->nationality; ?></span>
                <span class="badge-pastel" id="sidebar-age-badge" style="background:rgba(255,255,255,0.1); color:#fff; border:none; font-size:0.55rem;">--</span>
                <span class="badge-pastel" style="background:rgba(255,255,255,0.1); color:#fff; border:none; font-size:0.55rem;"><?php echo esc_html($patient->initial_diagnosis ?: 'No Diagnosis'); ?></span>
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

            <!-- Tab: Demographics (3 Distinct Boxes) -->
            <div id="tab-demographics" class="p-file-pane active">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">

                    <!-- Box A: Child Identity -->
                    <div class="control-card profile-section-box" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid var(--control-primary); box-shadow:0 15px 35px rgba(15,23,42,0.04);">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;"><span class="dashicons dashicons-admin-users"></span> <?php echo Control_I18n::t('basic_info'); ?></h4>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="name_first" placeholder="<?php echo Control_I18n::t('placeholder_first_name'); ?> *" value="<?php echo esc_attr($patient->name_first); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_second" placeholder="<?php echo Control_I18n::t('placeholder_father_name'); ?> *" value="<?php echo esc_attr($patient->name_second); ?>" required></div>
                            <div class="wiz-field no-label"><input type="text" name="name_last" placeholder="<?php echo Control_I18n::t('placeholder_family_name'); ?> *" value="<?php echo esc_attr($patient->name_last); ?>" required></div>
                        </div>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="date" name="dob" class="dob-input-calc" value="<?php echo esc_attr($patient->dob); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" class="age-display-field" readonly style="background:#f8fafc; font-weight:700;" placeholder="<?php echo Control_I18n::t('age'); ?>"></div>
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

                    <!-- Box B: Communication -->
                    <div class="control-card profile-section-box" style="border-radius:24px; padding:35px; margin-bottom:30px; border-right:6px solid #10b981; box-shadow:0 15px 35px rgba(16,185,129,0.04);">
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

                    <!-- Box C: Emergency -->
                    <div class="control-card profile-section-box" style="border-radius:24px; padding:35px; border-right:6px solid #ef4444; box-shadow:0 15px 35px rgba(239,68,68,0.04);">
                        <h4 style="margin:0 0 30px 0; color:#be123c; font-weight:800; display:flex; align-items:center; gap:10px;"><span class="dashicons dashicons-warning"></span> <?php echo Control_I18n::t('emergency_contact'); ?></h4>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><input type="text" name="emergency_contact" placeholder="<?php echo Control_I18n::t('emergency_name'); ?>" value="<?php echo esc_attr($patient->emergency_contact); ?>"></div>
                            <div class="wiz-field no-label"><input type="tel" name="emergency_contact_alt" placeholder="<?php echo Control_I18n::t('emergency_phone'); ?>" value="<?php echo esc_attr($patient->emergency_contact_alt); ?>"></div>
                            <div class="wiz-field no-label"><input type="text" name="emergency_relationship" placeholder="<?php echo Control_I18n::t('emergency_relation'); ?>" value="<?php echo esc_attr($patient->emergency_relationship); ?>"></div>
                        </div>
                        <div class="wiz-field no-label">
                            <select name="emergency_lang">
                                <option value=""><?php echo Control_I18n::t('emergency_lang'); ?>...</option>
                                <option value="ar" <?php selected($patient->emergency_lang, 'ar'); ?>>العربية</option>
                                <option value="en" <?php selected($patient->emergency_lang, 'en'); ?>>English</option>
                            </select>
                        </div>
                    </div>

                    <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                    <div class="save-feedback" style="display:none; margin-top:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                </form>
            </div>

            <!-- Tab: Medical -->
            <div id="tab-medical" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800; border-bottom:2px solid #f8fafc; padding-bottom:15px;"><?php echo Control_I18n::t('file_medical'); ?></h4>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><select name="birth_type"><option value="normal" <?php selected($patient->birth_type,'normal'); ?>><?php echo Control_I18n::t('birth_normal'); ?></option><option value="csection" <?php selected($patient->birth_type,'csection'); ?>><?php echo Control_I18n::t('birth_csection'); ?></option></select></div>
                            <div class="wiz-field no-label"><textarea name="birth_complications" placeholder="<?php echo Control_I18n::t('birth_complications'); ?>" rows="2"><?php echo esc_textarea($patient->birth_complications); ?></textarea></div>
                        </div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="chronic_conditions" placeholder="<?php echo Control_I18n::t('chronic_conditions'); ?>" rows="2"><?php echo esc_textarea($patient->chronic_conditions); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="current_medications" placeholder="<?php echo Control_I18n::t('medications'); ?>" rows="2"><?php echo esc_textarea($patient->current_medications); ?></textarea></div>
                        </div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="drug_allergies" placeholder="<?php echo Control_I18n::t('drug_allergies'); ?>" rows="2"><?php echo esc_textarea($patient->drug_allergies); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="medical_surgeries" placeholder="<?php echo Control_I18n::t('surgeries'); ?>" rows="2"><?php echo esc_textarea($patient->medical_surgeries); ?></textarea></div>
                        </div>
                        <div class="wiz-grid">
                            <div class="wiz-field no-label"><textarea name="neurological_conditions" placeholder="<?php echo Control_I18n::t('neuro_conditions'); ?>" rows="2"><?php echo esc_textarea($patient->neurological_conditions); ?></textarea></div>
                            <div class="wiz-field no-label"><textarea name="sensory_issues" placeholder="<?php echo Control_I18n::t('sensory_issues'); ?>" rows="2"><?php echo esc_textarea($patient->sensory_issues); ?></textarea></div>
                        </div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                        <div class="save-feedback" style="display:none; margin-top:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                    </div>
                </form>
            </div>

            <!-- Specialized Evaluation Modules -->
            <?php foreach($specialist_modules as $mod_id => $mod): if(in_array($user_role, $mod['roles'])): ?>
                <div id="tab-eval_<?php echo $mod_id; ?>" class="p-file-pane" style="display:none;">
                    <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                        <div class="control-card" style="border-radius:24px; padding:40px; border-top:8px solid var(--control-accent);">
                            <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800;"><?php echo $mod['label']; ?></h4>
                            <?php if($mod_id === 'psych'): ?>
                                <div class="wiz-field no-label"><textarea name="eval_psych_cognitive" placeholder="<?php echo Control_I18n::t('cognitive_assessment'); ?>" rows="4"><?php echo esc_textarea($patient->eval_psych_cognitive); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_psych_emotional" placeholder="<?php echo Control_I18n::t('emotional_behavior'); ?>" rows="4"><?php echo esc_textarea($patient->eval_psych_emotional); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_psych_tests" placeholder="<?php echo Control_I18n::t('psych_tests'); ?>" rows="4"><?php echo esc_textarea($patient->eval_psych_tests); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_psych_interpretation" placeholder="<?php echo Control_I18n::t('diagnosis_interpretation'); ?>" rows="4"><?php echo esc_textarea($patient->eval_psych_interpretation); ?></textarea></div>
                            <?php elseif($mod_id === 'ot'): ?>
                                <div class="wiz-field no-label"><textarea name="eval_ot_fine_motor" placeholder="<?php echo Control_I18n::t('fine_motor'); ?>" rows="4"><?php echo esc_textarea($patient->eval_ot_fine_motor); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_ot_adl" placeholder="<?php echo Control_I18n::t('adl'); ?>" rows="4"><?php echo esc_textarea($patient->eval_ot_adl); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_ot_sensory" placeholder="<?php echo Control_I18n::t('sensory_processing'); ?>" rows="4"><?php echo esc_textarea($patient->eval_ot_sensory); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_ot_functional" placeholder="<?php echo Control_I18n::t('functional_independence'); ?>" rows="4"><?php echo esc_textarea($patient->eval_ot_functional); ?></textarea></div>
                            <?php elseif($mod_id === 'phys'): ?>
                                <div class="wiz-field no-label"><textarea name="eval_phys_gross_motor" placeholder="<?php echo Control_I18n::t('gross_motor'); ?>" rows="4"><?php echo esc_textarea($patient->eval_phys_gross_motor); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_phys_strength" placeholder="<?php echo Control_I18n::t('strength_mobility'); ?>" rows="4"><?php echo esc_textarea($patient->eval_phys_strength); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_phys_balance" placeholder="<?php echo Control_I18n::t('balance_coordination'); ?>" rows="4"><?php echo esc_textarea($patient->eval_phys_balance); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_phys_performance" placeholder="<?php echo Control_I18n::t('physical_tracking'); ?>" rows="4"><?php echo esc_textarea($patient->eval_phys_performance); ?></textarea></div>
                            <?php elseif($mod_id === 'beh'): ?>
                                <div class="wiz-field no-label"><textarea name="eval_beh_tracking" placeholder="<?php echo Control_I18n::t('behavior_tracking'); ?>" rows="4"><?php echo esc_textarea($patient->eval_beh_tracking); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_beh_regulation" placeholder="<?php echo Control_I18n::t('emotional_regulation'); ?>" rows="4"><?php echo esc_textarea($patient->eval_beh_regulation); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_beh_response" placeholder="<?php echo Control_I18n::t('intervention_response'); ?>" rows="4"><?php echo esc_textarea($patient->eval_beh_response); ?></textarea></div>
                                <div class="wiz-field no-label"><textarea name="eval_beh_plans" placeholder="<?php echo Control_I18n::t('improvement_plans'); ?>" rows="4"><?php echo esc_textarea($patient->eval_beh_plans); ?></textarea></div>
                            <?php endif; ?>
                            <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                            <div class="save-feedback" style="display:none; margin-top:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                        </div>
                    </form>
                </div>
            <?php endif; endforeach; ?>

            <!-- Tab: Notes (Professional Categories) -->
            <div id="tab-notes" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_notes'); ?></h4>
                        <button class="control-btn" onclick="jQuery('#clinical-note-modal').css('display','flex')" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><span class="dashicons dashicons-plus" style="margin-left:5px;"></span><?php _e('إضافة ملاحظة مهنية', 'control'); ?></button>
                    </div>

                    <div style="display:flex; gap:10px; margin-bottom:25px; background:#f8fafc; padding:12px; border-radius:15px;">
                        <button class="note-filter-btn active" data-cat="all"><?php _e('الكل', 'control'); ?></button>
                        <button class="note-filter-btn" data-cat="clinical"><?php echo Control_I18n::t('clinical_notes'); ?></button>
                        <button class="note-filter-btn" data-cat="behavioral"><?php echo Control_I18n::t('behavioral_notes'); ?></button>
                        <button class="note-filter-btn" data-cat="administrative"><?php echo Control_I18n::t('administrative_notes'); ?></button>
                    </div>

                    <div id="patient-notes-history" style="display:flex; flex-direction:column; gap:20px;">
                        <?php $notes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_clinical_notes WHERE patient_id = %d ORDER BY created_at DESC", $patient->id));
                        if($notes): foreach($notes as $n): ?>
                            <div class="structured-note-card" data-cat="<?php echo $n->note_category; ?>" style="background:#fff; border:1.5px solid #eef2f6; border-radius:18px; padding:25px;">
                                <div style="display:flex; gap:15px; align-items:center; margin-bottom:15px;">
                                    <div style="width:40px; height:40px; border-radius:50%; background:var(--control-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem;"><?php echo substr($n->author_name,0,1); ?></div>
                                    <div><div style="font-weight:800; font-size:0.9rem;"><?php echo esc_html($n->author_name); ?></div><div style="font-size:0.65rem; color:var(--control-muted);"><?php echo $n->author_role; ?> • <?php echo $n->created_at; ?></div></div>
                                </div>
                                <div style="font-size:0.9rem; line-height:1.7; color:#334155;"><?php echo nl2br(esc_html($n->content)); ?></div>
                                <div style="margin-top:15px; text-align:left;"><button class="delete-clinical-note" data-id="<?php echo $n->id; ?>" style="background:none; border:none; color:#cbd5e1; cursor:pointer;" onmouseover="this.style.color='#ef4444'"><span class="dashicons dashicons-trash"></span></button></div>
                            </div>
                        <?php endforeach; else: ?><div style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد ملاحظات مسجلة.', 'control'); ?></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Reports (Categorized & Role-Based) -->
            <div id="tab-reports" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:35px; border-bottom:1px solid #f8fafc; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_reports'); ?></h4>
                        <button class="control-btn add-document-btn" data-cat="report" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><span class="dashicons dashicons-upload" style="margin-left:5px;"></span><?php _e('رفع تقرير جديد', 'control'); ?></button>
                    </div>
                    <div class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                        <?php $reports = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_documents WHERE patient_id = %d AND doc_category = 'report' ORDER BY uploaded_at DESC", $patient->id));
                        if($reports): foreach($reports as $rep):
                            if (!$can_manage && $rep->specialist_role !== $user_role) continue; ?>
                            <div class="doc-attachment-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:18px; padding:20px;">
                                <div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;"><span class="dashicons dashicons-media-document" style="font-size:30px; color:var(--control-primary);"></span><div><div style="font-weight:800; font-size:0.8rem;"><?php echo esc_html($rep->doc_name); ?></div><small style="color:var(--control-muted);"><?php echo $rep->specialist_role; ?> • <?php echo date('Y-m-d', strtotime($rep->uploaded_at)); ?></small></div></div>
                                <a href="<?php echo esc_url($rep->doc_url); ?>" target="_blank" class="control-btn" style="width:100%; font-size:0.7rem; background:var(--control-primary); border:none; border-radius:10px;"><?php _e('تحميل التقرير', 'control'); ?></a>
                            </div>
                        <?php endforeach; else: ?><div style="grid-column:1/-1; text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد تقيير مرفوعة.', 'control'); ?></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Attendance (Activated Module) -->
            <div id="tab-attendance" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_attendance'); ?></h4>
                        <button class="control-btn" style="background:var(--control-primary); border:none; font-size:0.75rem; border-radius:10px;"><span class="dashicons dashicons-calendar-alt" style="margin-left:5px;"></span><?php _e('جدولة جلسة', 'control'); ?></button>
                    </div>
                    <div style="background:#f8fafc; border:1.5px solid #eef2f6; border-radius:20px; padding:30px; text-align:center; margin-bottom:30px;">
                        <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:10px;">
                            <?php $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; foreach($days as $day): ?><div style="font-weight:800; font-size:0.7rem; color:var(--control-muted);"><?php echo $day; ?></div><?php endforeach; ?>
                            <?php for($i=1; $i<=31; $i++): ?>
                                <div style="aspect-ratio:1; display:flex; align-items:center; justify-content:center; background:#fff; border:1.5px solid #eef2f6; border-radius:12px; font-size:0.85rem; cursor:pointer;" onmouseover="this.style.borderColor='var(--control-accent)'" onmouseout="this.style.borderColor='#eef2f6'"><?php echo $i; ?></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <table class="control-table">
                        <thead><tr><th><?php _e('تاريخ الجلسة', 'control'); ?></th><th><?php _e('الحالة', 'control'); ?></th><th><?php _e('إجراءات', 'control'); ?></th></tr></thead>
                        <tbody><tr><td>2023-11-01 09:30 AM</td><td><span style="color:#059669; font-weight:800;">Present</span></td><td><button class="control-btn" style="padding:4px 10px; font-size:0.65rem; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('تغيير الموعد', 'control'); ?></button></td></tr></tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Billing (Activated Integrated System) -->
            <div id="tab-billing" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_billing'); ?></h4>
                        <div class="wiz-grid-3">
                            <div class="wiz-field no-label"><select name="billing_plan"><option value="session" <?php selected($patient->billing_plan,'session'); ?>><?php echo Control_I18n::t('session_based'); ?></option><option value="weekly" <?php selected($patient->billing_plan,'weekly'); ?>><?php echo Control_I18n::t('weekly'); ?></option><option value="monthly" <?php selected($patient->billing_plan,'monthly'); ?>><?php echo Control_I18n::t('monthly'); ?></option></select></div>
                            <div class="wiz-field no-label"><input type="number" name="registration_cost" placeholder="<?php echo Control_I18n::t('registration_cost'); ?>" value="<?php echo esc_attr($patient->registration_cost); ?>"></div>
                            <div class="wiz-field no-label"><input type="number" name="amount_per_cycle" placeholder="<?php echo Control_I18n::t('amount_per_cycle'); ?>" value="<?php echo esc_attr($patient->amount_per_cycle); ?>"></div>
                        </div>
                        <div style="background:var(--control-primary); border-radius:24px; padding:35px; margin-top:20px; display:flex; justify-content:space-around; color:#fff; text-align:center; box-shadow:0 15px 30px rgba(15,23,42,0.1);">
                            <div><small style="opacity:0.6; display:block; margin-bottom:5px;"><?php _e('إجمالي المستحق', 'control'); ?></small><strong style="font-size:1.6rem; color:var(--control-accent);"><?php echo number_format($patient->total_expected_revenue, 2); ?></strong></div>
                            <div><small style="opacity:0.6; display:block; margin-bottom:5px;"><?php _e('الرصيد المتبقي', 'control'); ?></small><strong style="font-size:1.6rem; color:#f87171;">--</strong></div>
                        </div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                        <div class="save-feedback" style="display:none; margin-top:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Staff (HR Link) -->
            <div id="tab-staff" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:40px;">
                        <h4 style="margin:0 0 35px 0; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('file_staff'); ?></h4>
                        <div class="wiz-field no-label">
                            <?php $staff = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff ORDER BY first_name ASC"); ?>
                            <select name="assigned_specialists">
                                <option value=""><?php echo Control_I18n::t('primary_specialist'); ?>...</option>
                                <?php foreach($staff as $s): ?>
                                    <option value="<?php echo $s->id; ?>" <?php selected($patient->assigned_specialists, $s->id); ?>><?php echo esc_html($s->first_name . ' ' . $s->last_name . ' (' . $s->role . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="wiz-field no-label"><textarea name="intake_notes" placeholder="<?php echo Control_I18n::t('case_team'); ?>..." rows="4"><?php echo esc_textarea($patient->intake_notes); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 60px; border-radius:12px; font-weight:800;"><?php echo Control_I18n::t('save'); ?></button></div>
                        <div class="save-feedback" style="display:none; margin-top:20px; padding:15px; border-radius:12px; background:#ecfdf5; color:#065f46; font-weight:700; text-align:center;"></div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div id="clinical-note-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10006; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('تدوين ملاحظة مهنية', 'control'); ?></h3>
        <form id="file-clinical-note-form">
            <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
            <div class="wiz-field no-label"><select name="note_category" required><option value="clinical"><?php echo Control_I18n::t('clinical_notes'); ?></option><option value="behavioral"><?php echo Control_I18n::t('behavioral_notes'); ?></option><option value="administrative"><?php echo Control_I18n::t('administrative_notes'); ?></option></select></div>
            <div class="wiz-field no-label"><textarea name="content" placeholder="<?php _e('اكتب نص الملاحظة هنا...', 'control'); ?>" rows="6" required></textarea></div>
            <div style="display:flex; gap:10px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ الملاحظة', 'control'); ?></button><button type="button" onclick="jQuery('#clinical-note-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#clickable-profile-img').on('click', function() {
        const frame = wp.media({ title: 'Select Profile Photo', multiple: false }).open();
        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            $.post(control_ajax.ajax_url, { action:'control_save_patient', id:<?php echo $patient->id; ?>, profile_photo:attachment.url, nonce:control_ajax.nonce }, () => location.reload());
        });
    });

    function calculateAge(dob) {
        if (!dob) return "";
        const birthDate = new Date(dob); const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) { years--; months += 12; }
        return years + " " + "<?php echo Control_I18n::t('years'); ?>";
    }

    $('.dob-input-calc').on('change', function() {
        const age = calculateAge($(this).val());
        $(this).closest('form').find('.age-display-field').val(age);
        $('#sidebar-age-badge').text(age);
    }).trigger('change');

    $('.p-nav-item').on('click', function() {
        $('.p-nav-item').removeClass('active'); $(this).addClass('active');
        $('.p-file-pane').hide(); $('#' + $(this).data('tab')).fadeIn(300);
    });

    $('.note-filter-btn').on('click', function() {
        $('.note-filter-btn').removeClass('active'); $(this).addClass('active');
        const cat = $(this).data('cat');
        if (cat === 'all') { $('.structured-note-card').fadeIn(); }
        else { $('.structured-note-card').hide(); $(`.structured-note-card[data-cat="${cat}"]`).fadeIn(); }
    });

    $('.clinical-save-form').on('submit', function(e) {
        e.preventDefault(); const $form = $(this); const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).text('...');
        $.post(control_ajax.ajax_url, $form.serialize() + '&action=control_save_patient&id=' + $form.data('patient-id') + '&nonce=' + control_ajax.nonce, (res) => {
            $btn.prop('disabled', false).text('<?php echo Control_I18n::t("save"); ?>');
            if(res.success) $form.find('.save-feedback').text('<?php _e("تم حفظ التعديلات بنجاح في هذا القسم.", "control"); ?>').fadeIn().delay(3000).fadeOut();
            else alert(res.data.message);
        });
    });

    $('#file-clinical-note-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_clinical_note&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload(); else alert(res.data.message);
        });
    });

    $(document).on('click', '.delete-clinical-note', function() {
        if(!confirm('<?php _e("حذف الملاحظة نهائياً؟", "control"); ?>')) return;
        $.post(control_ajax.ajax_url, { action:'control_delete_clinical_note', id:$(this).data('id'), nonce:control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.wiz-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.wiz-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
.wiz-field.no-label { margin-bottom: 12px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 15px 20px; border-radius: 16px; border: 1.5px solid #eef2f6; font-size: 0.95rem; background:#fcfdfe; transition:0.3s; color:#1e293b; }
.wiz-field input:focus { border-color: var(--control-accent); background:#fff; outline:none; box-shadow:0 0 0 4px rgba(212,175,55,0.05); }
.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.note-filter-btn { background:#fff; border:1.5px solid #e2e8f0; padding:6px 20px; border-radius:10px; font-size:0.75rem; font-weight:700; cursor:pointer; color:var(--control-muted); transition:0.2s; }
.note-filter-btn.active { background:var(--control-primary); color:#fff; border-color:var(--control-primary); }
.control-table th { background:#f8fafc; color:var(--control-muted); font-size:0.7rem; font-weight:800; text-transform:uppercase; padding:15px; border-bottom:1px solid #e2e8f0; }
@media (max-width: 1024px) { .patient-file-layout { flex-direction: column; } .p-internal-sidebar { width: 100% !important; position: static !important; } }
@media (max-width: 768px) { .wiz-grid, .wiz-grid-3 { grid-template-columns: 1fr; } }
</style>
</div>
