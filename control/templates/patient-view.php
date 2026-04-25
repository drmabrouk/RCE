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
$is_reception = ! $can_view_clinical && Control_Auth::has_permission('pediatric_view_basic');

// Status Labels
$status_labels = array(
    'active'          => __('نشط', 'control'),
    'evaluation_only' => __('تقييم فقط', 'control'),
    'waiting_list'    => __('قائمة الانتظار', 'control'),
    'dropped_out'     => __('منقطع', 'control'),
    'completed'       => __('تم التأهيل', 'control'),
    'closed'          => __('ملف مغلق', 'control'),
);

// Tab Definitions with Permissions
$tabs = array(
    'demographics' => array('label' => Control_I18n::t('file_demographics'), 'icon' => 'admin-users', 'clinical' => false),
    'medical'      => array('label' => Control_I18n::t('file_medical'), 'icon' => 'heart', 'clinical' => true),
    'development'  => array('label' => Control_I18n::t('file_developmental'), 'icon' => 'chart-line', 'clinical' => true),
    'assessments'  => array('label' => Control_I18n::t('file_assessments'), 'icon' => 'clipboard', 'clinical' => true),
    'diagnosis'    => array('label' => Control_I18n::t('file_diagnosis'), 'icon' => 'visibility', 'clinical' => true),
    'treatment'    => array('label' => Control_I18n::t('file_treatment'), 'icon' => 'welcome-learn-more', 'clinical' => true),
    'sessions'     => array('label' => Control_I18n::t('file_sessions'), 'icon' => 'calendar-alt', 'clinical' => true),
    'behavior'     => array('label' => Control_I18n::t('file_behavior'), 'icon' => 'groups', 'clinical' => true),
    'referrals'    => array('label' => Control_I18n::t('referrals'), 'icon' => 'random', 'clinical' => true),
    'reports'      => array('label' => Control_I18n::t('file_reports'), 'icon' => 'analytics', 'clinical' => true),
    'attachments'  => array('label' => Control_I18n::t('file_attachments'), 'icon' => 'paperclip', 'clinical' => false),
    'attendance'   => array('label' => Control_I18n::t('file_attendance'), 'icon' => 'clock', 'clinical' => false),
    'billing'      => array('label' => Control_I18n::t('file_billing'), 'icon' => 'cart', 'clinical' => false),
    'notes'        => array('label' => Control_I18n::t('file_notes'), 'icon' => 'edit', 'clinical' => false),
    'staff'        => array('label' => Control_I18n::t('file_staff'), 'icon' => 'businessperson', 'clinical' => false),
);
?>

<div class="patient-file-layout" style="display:flex; gap:30px; align-items:flex-start;">

    <!-- Right Sidebar Navigation (Sticky) -->
    <div class="p-internal-sidebar" style="width:280px; flex-shrink:0; position:sticky; top:100px;">
        <!-- Patient Identity Summary (Mini Card) -->
        <div class="control-card" style="padding:20px; border-radius:20px; background:var(--control-primary); color:#fff; margin-bottom:20px; text-align:center;">
            <div style="width:80px; height:80px; border-radius:20px; overflow:hidden; border:3px solid rgba(255,255,255,0.2); margin:0 auto 15px; background:#fff;">
                <?php if($patient->profile_photo): ?>
                    <img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:40px; width:40px; height:40px;"></span></div>
                <?php endif; ?>
            </div>
            <h3 style="margin:0; color:#fff; font-size:1.1rem; font-weight:800;"><?php echo esc_html($patient->full_name); ?></h3>
            <div style="margin-top:8px;">
                <span class="patient-status-badge status-<?php echo esc_attr($patient->case_status); ?>" style="font-size:0.6rem; padding:2px 10px; border-radius:20px; background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.2);">
                    <?php echo $status_labels[$patient->case_status] ?? $patient->case_status; ?>
                </span>
            </div>
            <div style="margin-top:10px; font-size:0.75rem; color:rgba(255,255,255,0.6); font-family:monospace;">#<?php echo esc_html($patient->permanent_id ?: $patient->id); ?></div>
        </div>

        <div class="control-card" style="padding:10px; border-radius:20px; background:#fff; border:1px solid #f1f5f9; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
            <?php foreach($tabs as $id => $tab):
                if ($tab['clinical'] && ! $can_view_clinical && ! $can_manage) continue;
            ?>
                <div class="p-nav-item <?php echo $id === 'demographics' ? 'active' : ''; ?>" data-tab="tab-<?php echo $id; ?>" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                    <div class="nav-icon-box" style="width:32px; height:32px; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:var(--control-muted);">
                        <span class="dashicons dashicons-<?php echo $tab['icon']; ?>"></span>
                    </div>
                    <span style="font-weight:700; flex:1; font-size:0.85rem;"><?php echo $tab['label']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <button onclick="window.print()" class="control-btn" style="width:100%; margin-top:20px; background:#f8fafc; color:var(--control-primary) !important; border:1px solid #e2e8f0; font-weight:800; border-radius:15px;">
            <span class="dashicons dashicons-printer" style="margin-left:8px;"></span><?php _e('طباعة الملف الشامل', 'control'); ?>
        </button>
    </div>

    <!-- Main Content Area -->
    <div style="flex:1; min-width:0;">
        <div id="patient-file-content">

            <!-- Tab: Demographics -->
            <div id="tab-demographics" class="p-file-pane active">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-admin-users"></span> <?php echo Control_I18n::t('file_demographics'); ?>
                        </h4>
                        <div class="wiz-grid">
                            <div class="wiz-field"><label><?php echo Control_I18n::t('name_first'); ?></label><input type="text" name="name_first" value="<?php echo esc_attr($patient->name_first); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('name_second'); ?></label><input type="text" name="name_second" value="<?php echo esc_attr($patient->name_second); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('name_last'); ?></label><input type="text" name="name_last" value="<?php echo esc_attr($patient->name_last); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('dob'); ?></label><input type="date" name="dob" class="dob-input-calc" value="<?php echo esc_attr($patient->dob); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('age'); ?></label><input type="text" class="age-display-field" readonly style="background:#f8fafc; font-weight:700;"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('gender'); ?></label>
                                <select name="gender">
                                    <option value="male" <?php selected($patient->gender, 'male'); ?>><?php echo Control_I18n::t('male'); ?></option>
                                    <option value="female" <?php selected($patient->gender, 'female'); ?>><?php echo Control_I18n::t('female'); ?></option>
                                </select>
                            </div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('nationality'); ?></label><input type="text" name="nationality" value="<?php echo esc_attr($patient->nationality); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('referral_source'); ?></label><input type="text" name="referral_source" value="<?php echo esc_attr($patient->referral_source); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('national_id'); ?></label><input type="text" name="national_id" value="<?php echo esc_attr($patient->national_id); ?>"></div>
                        </div>
                        <div style="margin-top:30px; padding-top:20px; border-top:1px solid #f1f5f9;">
                            <h5 style="margin:0 0 20px 0; font-weight:800;"><?php echo Control_I18n::t('contact_info'); ?></h5>
                            <div class="wiz-grid">
                                <div class="wiz-field"><label><?php echo Control_I18n::t('guardian_name'); ?></label><input type="text" name="guardian_name" value="<?php echo esc_attr($patient->guardian_name); ?>"></div>
                                <div class="wiz-field"><label><?php echo Control_I18n::t('father_phone'); ?></label><input type="tel" name="father_phone" value="<?php echo esc_attr($patient->father_phone); ?>"></div>
                                <div class="wiz-field"><label><?php echo Control_I18n::t('mother_phone'); ?></label><input type="tel" name="mother_phone" value="<?php echo esc_attr($patient->mother_phone); ?>"></div>
                                <div class="wiz-field"><label><?php echo Control_I18n::t('email'); ?></label><input type="email" name="email" value="<?php echo esc_attr($patient->email); ?>"></div>
                            </div>
                            <div class="wiz-field" style="margin-top:15px;"><label><?php echo Control_I18n::t('address'); ?></label><textarea name="address" rows="2"><?php echo esc_textarea($patient->address); ?></textarea></div>
                        </div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Medical History -->
            <div id="tab-medical" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-heart"></span> <?php echo Control_I18n::t('file_medical'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('pregnancy_history'); ?></label><textarea name="pregnancy_history" rows="3"><?php echo esc_textarea($patient->pregnancy_history); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('birth_history'); ?></label><textarea name="birth_history" rows="3"><?php echo esc_textarea($patient->birth_history); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('chronic_conditions'); ?></label><textarea name="chronic_conditions" rows="3"><?php echo esc_textarea($patient->chronic_conditions); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('surgeries'); ?></label><textarea name="medical_surgeries" rows="2"><?php echo esc_textarea($patient->medical_surgeries); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('medications'); ?></label><textarea name="current_medications" rows="2"><?php echo esc_textarea($patient->current_medications); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('drug_allergies'); ?></label><textarea name="drug_allergies" rows="2"><?php echo esc_textarea($patient->drug_allergies); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Developmental History -->
            <div id="tab-development" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-chart-line"></span> <?php echo Control_I18n::t('file_developmental'); ?>
                        </h4>
                        <div class="wiz-grid">
                            <div class="wiz-field"><label><?php echo Control_I18n::t('sitting'); ?></label><input type="text" name="milestones_sitting" value="<?php echo esc_attr($patient->milestones_sitting); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('crawling'); ?></label><input type="text" name="milestones_crawling" value="<?php echo esc_attr($patient->milestones_crawling); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('walking'); ?></label><input type="text" name="milestones_walking" value="<?php echo esc_attr($patient->milestones_walking); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('first_word'); ?></label><input type="text" name="lang_first_word" value="<?php echo esc_attr($patient->lang_first_word); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('sentences'); ?></label><input type="text" name="lang_sentences" value="<?php echo esc_attr($patient->lang_sentences); ?>"></div>
                        </div>
                        <div class="wiz-field" style="margin-top:20px;"><label><?php echo Control_I18n::t('social_skills'); ?></label><textarea name="dev_social_skills" rows="3"><?php echo esc_textarea($patient->dev_social_skills); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('observed_delays'); ?></label><textarea name="dev_observed_delays" rows="3"><?php echo esc_textarea($patient->dev_observed_delays); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Assessments -->
            <div id="tab-assessments" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-clipboard"></span> <?php echo Control_I18n::t('file_assessments'); ?>
                        </h4>
                        <button class="control-btn add-assessment-btn" data-patient-id="<?php echo $patient->id; ?>" style="background:var(--control-accent); color:var(--control-primary) !important; border:none; font-weight:800; padding:8px 20px; border-radius:12px;">
                            <span class="dashicons dashicons-plus" style="margin-left:5px;"></span><?php _e('إضافة تقييم جديد', 'control'); ?>
                        </button>
                    </div>

                    <table class="control-table" style="background:#fcfcfc;">
                        <thead>
                            <tr>
                                <th><?php _e('اسم الاختبار / التقييم', 'control'); ?></th>
                                <th><?php _e('النتيجة المستخلصة', 'control'); ?></th>
                                <th><?php _e('التاريخ', 'control'); ?></th>
                                <th><?php _e('الفاحص', 'control'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $assessments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_assessments WHERE patient_id = %d ORDER BY test_date DESC", $patient->id));
                            if($assessments): foreach($assessments as $a): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($a->test_name); ?></strong></td>
                                    <td><?php echo nl2br(esc_html($a->test_result)); ?></td>
                                    <td><?php echo esc_html($a->test_date); ?></td>
                                    <td><small><?php echo esc_html($a->assessor_id); ?></small></td>
                                    <td style="text-align:left;">
                                        <button class="delete-assessment-btn" data-id="<?php echo $a->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('لا توجد تقييمات مسجلة حالياً.', 'control'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Diagnosis -->
            <div id="tab-diagnosis" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-visibility"></span> <?php echo Control_I18n::t('file_diagnosis'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('initial_diagnosis'); ?></label><textarea name="initial_diagnosis" rows="2" style="font-weight:700; font-size:1.1rem;"><?php echo esc_textarea($patient->initial_diagnosis); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('secondary_diagnosis'); ?></label><textarea name="diagnosis_secondary" rows="3"><?php echo esc_textarea($patient->diagnosis_secondary); ?></textarea></div>
                        <div class="wiz-grid">
                            <div class="wiz-field"><label><?php echo Control_I18n::t('severity_level'); ?></label><input type="text" name="diagnosis_severity" value="<?php echo esc_attr($patient->diagnosis_severity); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('diagnosis_source'); ?></label><input type="text" name="external_diagnosis_source" value="<?php echo esc_attr($patient->external_diagnosis_source); ?>"></div>
                        </div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Treatment Plan -->
            <div id="tab-treatment" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-welcome-learn-more"></span> <?php echo Control_I18n::t('file_treatment'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('short_goals'); ?></label><textarea name="tp_goals_short" rows="4"><?php echo esc_textarea($patient->tp_goals_short); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('long_goals'); ?></label><textarea name="tp_goals_long" rows="4"><?php echo esc_textarea($patient->tp_goals_long); ?></textarea></div>
                        <div class="wiz-grid">
                            <div class="wiz-field"><label><?php echo Control_I18n::t('tp_frequency'); ?></label><input type="text" name="tp_frequency" value="<?php echo esc_attr($patient->tp_frequency); ?>"></div>
                            <div class="wiz-field"><label><?php echo Control_I18n::t('routing_dept'); ?></label><input type="text" name="routing_dept" value="<?php echo esc_attr($patient->routing_dept); ?>"></div>
                        </div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Sessions & Progress -->
            <div id="tab-sessions" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-calendar-alt"></span> <?php echo Control_I18n::t('file_sessions'); ?>
                        </h4>
                        <button class="control-btn add-session-btn" data-patient-id="<?php echo $patient->id; ?>" style="background:var(--control-primary); border:none; font-weight:800; padding:8px 20px; border-radius:12px;">
                            <?php _e('تسجيل جلسة جديدة', 'control'); ?>
                        </button>
                    </div>

                    <div class="session-timeline" style="display:flex; flex-direction:column; gap:20px;">
                        <?php $sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_fin_sessions WHERE patient_id = %d ORDER BY session_date DESC", $patient->id));
                        if($sessions): foreach($sessions as $s): ?>
                            <div class="session-log-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:15px; padding:20px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                                    <div>
                                        <span style="background:var(--control-accent); color:var(--control-primary); font-size:0.7rem; font-weight:900; padding:2px 10px; border-radius:8px; text-transform:uppercase;"><?php echo esc_html($s->session_date); ?></span>
                                        <h5 style="margin:8px 0 0 0; font-weight:800;"><?php echo esc_html($s->specialist_id); ?></h5>
                                    </div>
                                    <div style="text-align:left;">
                                        <span style="font-size:1.1rem; font-weight:800; color:#059669;"><?php echo $s->progress_percentage; ?>%</span>
                                        <div style="font-size:0.6rem; color:var(--control-muted); font-weight:700;"><?php _e('نسبة الإنجاز', 'control'); ?></div>
                                    </div>
                                </div>
                                <div style="font-size:0.85rem; line-height:1.6; color:#475569;">
                                    <p style="margin-bottom:10px;"><strong><?php echo Control_I18n::t('clinical_notes'); ?>:</strong> <?php echo nl2br(esc_html($s->clinical_notes)); ?></p>
                                    <p><strong><?php echo Control_I18n::t('child_response'); ?>:</strong> <?php echo esc_html($s->child_response); ?></p>
                                </div>
                                <div style="margin-top:15px; text-align:left;"><button class="control-btn delete-fin-session" data-id="<?php echo $s->id; ?>" style="background:none; border:none; color:#ef4444; font-size:0.75rem;"><span class="dashicons dashicons-trash"></span></button></div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="text-align:center; padding:40px; color:var(--control-muted); border:2px dashed #f1f5f9; border-radius:20px;"><?php _e('لم يتم تسجيل أي جلسات علاجية حتى الآن.', 'control'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Referrals -->
            <div id="tab-referrals" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-random"></span> <?php echo Control_I18n::t('referrals'); ?>
                        </h4>
                        <button class="control-btn add-referral-btn" data-patient-id="<?php echo $patient->id; ?>" style="background:var(--control-primary); border:none; font-weight:800; padding:8px 20px; border-radius:12px;">
                            <?php _e('إضافة تحويل جديد', 'control'); ?>
                        </button>
                    </div>
                    <table class="control-table">
                        <thead><tr><th><?php _e('من قسم', 'control'); ?></th><th><?php _e('إلى قسم', 'control'); ?></th><th><?php _e('التاريخ', 'control'); ?></th><th><?php _e('ملاحظات', 'control'); ?></th><th></th></tr></thead>
                        <tbody>
                            <?php $referrals = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_referrals WHERE patient_id = %d ORDER BY referral_date DESC", $patient->id));
                            if($referrals): foreach($referrals as $r): ?>
                                <tr><td><?php echo esc_html($r->from_department); ?></td><td><strong><?php echo esc_html($r->to_department); ?></strong></td><td><?php echo esc_html($r->referral_date); ?></td><td><?php echo esc_html($r->notes); ?></td><td style="text-align:left;"><button class="delete-referral-btn" data-id="<?php echo $r->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button></td></tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--control-muted);"><?php _e('لا توجد سجلات تحويل.', 'control'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Behavior Plan -->
            <div id="tab-behavior" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-groups"></span> <?php echo Control_I18n::t('file_behavior'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('target_behaviors'); ?></label><textarea name="bp_target_behaviors" rows="3"><?php echo esc_textarea($patient->bp_target_behaviors); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('reinforcement'); ?></label><textarea name="bp_reinforcement_strategies" rows="3"><?php echo esc_textarea($patient->bp_reinforcement_strategies); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('interventions'); ?></label><textarea name="bp_intervention_techniques" rows="3"><?php echo esc_textarea($patient->bp_intervention_techniques); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Reports -->
            <div id="tab-reports" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px; text-align:center;">
                    <div style="width:100px; height:100px; background:#f0f9ff; color:#0ea5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px;">
                        <span class="dashicons dashicons-analytics" style="font-size:50px; width:50px; height:50px;"></span>
                    </div>
                    <h2 style="font-weight:800; color:var(--control-primary);"><?php _e('منصة التقارير الذكية', 'control'); ?></h2>
                    <p style="color:var(--control-muted); margin-bottom:35px;"><?php _e('يمكنك توليد تقارير شاملة عن حالة الطفل وتطور أدائه خلال فترات زمنية محددة.', 'control'); ?></p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px;">
                        <button class="control-btn" style="background:#fff; color:var(--control-primary) !important; border:1px solid #e2e8f0; height:120px; flex-direction:column; gap:10px; border-radius:20px;">
                            <span class="dashicons dashicons-calendar-alt"></span><strong><?php _e('تقرير شهري', 'control'); ?></strong>
                        </button>
                        <button class="control-btn" style="background:#fff; color:var(--control-primary) !important; border:1px solid #e2e8f0; height:120px; flex-direction:column; gap:10px; border-radius:20px;">
                            <span class="dashicons dashicons-chart-bar"></span><strong><?php _e('تقرير ربع سنوي', 'control'); ?></strong>
                        </button>
                        <button class="control-btn" style="background:var(--control-primary); height:120px; flex-direction:column; gap:10px; border-radius:20px;">
                            <span class="dashicons dashicons-awards"></span><strong><?php _e('تقرير ختامي', 'control'); ?></strong>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: Attachments -->
            <div id="tab-attachments" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h4 style="margin:0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-paperclip"></span> <?php echo Control_I18n::t('file_attachments'); ?>
                        </h4>
                        <button class="control-btn add-document-btn" data-patient-id="<?php echo $patient->id; ?>" style="background:var(--control-primary); border:none; font-weight:800; padding:8px 20px; border-radius:12px;">
                            <span class="dashicons dashicons-upload" style="margin-left:5px;"></span><?php _e('رفع مرفق جديد', 'control'); ?>
                        </button>
                    </div>
                    <div class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:20px;">
                        <?php $docs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_patient_documents WHERE patient_id = %d ORDER BY uploaded_at DESC", $patient->id));
                        if($docs): foreach($docs as $d): ?>
                            <div class="doc-attachment-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:15px; padding:15px; text-align:center;">
                                <span class="dashicons dashicons-media-document" style="font-size:35px; color:var(--control-primary); margin-bottom:10px;"></span>
                                <h6 style="margin:0; font-size:0.75rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo esc_html($d->doc_name); ?></h6>
                                <div style="margin-top:10px; display:flex; gap:8px;">
                                    <a href="<?php echo esc_url($d->doc_url); ?>" target="_blank" class="control-btn" style="flex:1; padding:4px; font-size:0.65rem; background:#f1f5f9; color:var(--control-primary) !important; border:none;"><?php _e('فتح', 'control'); ?></a>
                                    <button class="delete-document-btn" data-id="<?php echo $d->id; ?>" style="background:none; border:none; color:#ef4444; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="grid-column:1/-1; text-align:center; padding:40px; color:var(--control-muted); border:2px dashed #f1f5f9; border-radius:20px;"><?php _e('لا توجد مرفقات مرتبطة بهذا الملف.', 'control'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Attendance -->
            <div id="tab-attendance" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <span class="dashicons dashicons-clock"></span> <?php echo Control_I18n::t('file_attendance'); ?>
                    </h4>
                    <div style="background:#f8fafc; padding:30px; border-radius:20px; text-align:center; border:2px dashed #e2e8f0;">
                        <span class="dashicons dashicons-calendar-alt" style="font-size:40px; color:var(--control-muted); margin-bottom:15px;"></span>
                        <p style="font-weight:700; color:var(--control-primary);"><?php _e('وحدة تتبع الحضور والجدولة قيد التحسين', 'control'); ?></p>
                        <small style="color:var(--control-muted);"><?php _e('سيتم توفير تقويم تفاعلي لربط الحضور بنظام الفوترة قريباً.', 'control'); ?></small>
                    </div>
                </div>
            </div>

            <!-- Tab: Billing -->
            <div id="tab-billing" class="p-file-pane" style="display:none;">
                <div class="control-card" style="border-radius:24px; padding:35px;">
                    <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <span class="dashicons dashicons-cart"></span> <?php echo Control_I18n::t('file_billing'); ?>
                    </h4>
                    <div class="wiz-grid" style="margin-bottom:30px;">
                        <div style="background:#ecfdf5; border:1px solid #bbf7d0; border-radius:15px; padding:20px; text-align:center;">
                            <span style="font-size:0.7rem; font-weight:800; color:#065f46; text-transform:uppercase;"><?php _e('إجمالي المدفوعات', 'control'); ?></span>
                            <div style="font-size:1.5rem; font-weight:900; color:#047857; margin-top:5px;"><?php
                                $paid = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}control_fin_payments p JOIN {$wpdb->prefix}control_fin_invoices i ON p.invoice_id = i.id WHERE i.patient_id = %d", $patient->id)) ?: 0;
                                echo number_format($paid, 2);
                            ?> <small>AED</small></div>
                        </div>
                        <div style="background:#fff1f2; border:1px solid #fecaca; border-radius:15px; padding:20px; text-align:center;">
                            <span style="font-size:0.7rem; font-weight:800; color:#9f1239; text-transform:uppercase;"><?php _e('الرصيد المستحق', 'control'); ?></span>
                            <div style="font-size:1.5rem; font-weight:900; color:#be123c; margin-top:5px;"><?php
                                $total = $wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM {$wpdb->prefix}control_fin_invoices WHERE patient_id = %d", $patient->id)) ?: 0;
                                echo number_format($total - $paid, 2);
                            ?> <small>AED</small></div>
                        </div>
                    </div>
                    <table class="control-table">
                        <thead><tr><th><?php _e('رقم الفاتورة', 'control'); ?></th><th><?php _e('التاريخ', 'control'); ?></th><th><?php _e('المبلغ', 'control'); ?></th><th><?php _e('الحالة', 'control'); ?></th></tr></thead>
                        <tbody>
                            <?php $invoices = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}control_fin_invoices WHERE patient_id = %d ORDER BY invoice_date DESC", $patient->id));
                            if($invoices): foreach($invoices as $inv): ?>
                                <tr><td><strong><?php echo esc_html($inv->invoice_number); ?></strong></td><td><?php echo esc_html($inv->invoice_date); ?></td><td><?php echo number_format($inv->total_amount, 2); ?></td><td><span class="status-badge inv-<?php echo $inv->status; ?>" style="font-size:0.65rem; padding:2px 8px; border-radius:10px; background:<?php echo $inv->status === 'paid' ? '#ecfdf5' : '#fff1f2'; ?>; color:<?php echo $inv->status === 'paid' ? '#065f46' : '#9f1239'; ?>; font-weight:700;"><?php echo $inv->status; ?></span></td></tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:20px;"><?php _e('لا توجد فواتير مسجلة.', 'control'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Notes -->
            <div id="tab-notes" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-edit"></span> <?php echo Control_I18n::t('file_notes'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('specialist_notes'); ?></label><textarea name="notes_specialist" rows="5"><?php echo esc_textarea($patient->notes_specialist); ?></textarea></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('guardian_notes'); ?></label><textarea name="notes_guardian" rows="5"><?php echo esc_textarea($patient->notes_guardian); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

            <!-- Tab: Staff -->
            <div id="tab-staff" class="p-file-pane" style="display:none;">
                <form class="clinical-save-form" data-patient-id="<?php echo $patient->id; ?>">
                    <div class="control-card" style="border-radius:24px; padding:35px;">
                        <h4 style="margin:0 0 30px 0; color:var(--control-primary); font-weight:800; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                            <span class="dashicons dashicons-businessperson"></span> <?php echo Control_I18n::t('file_staff'); ?>
                        </h4>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('primary_specialist'); ?></label><input type="text" name="assigned_specialists" value="<?php echo esc_attr($patient->assigned_specialists); ?>" placeholder="أدخل اسم الأخصائي المسؤول..."></div>
                        <div class="wiz-field"><label><?php echo Control_I18n::t('case_team'); ?></label><textarea name="intake_notes" rows="4" placeholder="أدخل أسماء فريق التدخل (تخاطب - حركي - وظيفي - نفسي)..."><?php echo esc_textarea($patient->intake_notes); ?></textarea></div>
                        <div style="text-align:left; margin-top:30px;"><button type="submit" class="control-btn" style="background:var(--control-primary); border:none; padding:12px 40px; border-radius:12px;"><?php echo Control_I18n::t('save'); ?></button></div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- All existing modals preserved but context-updated -->
<div id="pediatric-toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#1e293b; color:#fff; padding:12px 30px; border-radius:50px; z-index:100000; box-shadow:0 10px 30px rgba(0,0,0,0.2); font-weight:700;"></div>

<!-- Assessment Modal (Preserved & Re-Styled) -->
<div id="assessment-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10006; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:30px; border-radius:20px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('إضافة نتيجة اختبار', 'control'); ?></h3>
        <form id="assessment-form">
            <input type="hidden" name="patient_id" id="assessment-patient-id">
            <div class="wiz-field" style="margin-bottom:15px;"><label><?php _e('اسم الاختبار / التقييم', 'control'); ?></label><input type="text" name="test_name" required></div>
            <div class="wiz-field" style="margin-bottom:15px;"><label><?php _e('النتيجة / الملاحظات', 'control'); ?></label><textarea name="test_result" rows="4" required></textarea></div>
            <div class="wiz-field" style="margin-bottom:20px;"><label><?php _e('التاريخ', 'control'); ?></label><input type="date" name="test_date" required value="<?php echo date('Y-m-d'); ?>"></div>
            <div style="display:flex; gap:10px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ البيانات', 'control'); ?></button><button type="button" onclick="jQuery('#assessment-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<!-- Session Modal (New for Clinical Entry) -->
<!-- Referral Modal (New) -->
<div id="referral-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10006; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('إضافة تحويل داخلي', 'control'); ?></h3>
        <form id="file-referral-form">
            <input type="hidden" name="patient_id" id="referral-patient-id">
            <div class="wiz-field"><label><?php _e('من قسم', 'control'); ?></label><input type="text" name="from_department" required></div>
            <div class="wiz-field"><label><?php _e('إلى قسم', 'control'); ?></label><input type="text" name="to_department" required></div>
            <div class="wiz-field"><label><?php _e('تاريخ التحويل', 'control'); ?></label><input type="date" name="referral_date" required value="<?php echo date('Y-m-d'); ?>"></div>
            <div class="wiz-field"><label><?php _e('ملاحظات التحويل', 'control'); ?></label><textarea name="notes" rows="3"></textarea></div>
            <div style="display:flex; gap:10px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ التحويل', 'control'); ?></button><button type="button" onclick="jQuery('#referral-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<div id="session-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10006; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="control-card" style="width:100%; max-width:550px; padding:35px; border-radius:24px;">
        <h3 style="margin-top:0; color:var(--control-primary); font-weight:800;"><?php _e('تسجيل جلسة علاجية', 'control'); ?></h3>
        <form id="file-session-form">
            <input type="hidden" name="patient_id" id="session-patient-id">
            <div class="wiz-grid">
                <div class="wiz-field"><label><?php _e('تاريخ الجلسة', 'control'); ?></label><input type="date" name="session_date" required value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="wiz-field"><label><?php _e('اسم الأخصائي', 'control'); ?></label><input type="text" name="specialist_id" required></div>
            </div>
            <div class="wiz-field"><label><?php echo Control_I18n::t('clinical_notes'); ?></label><textarea name="clinical_notes" rows="3" required></textarea></div>
            <div class="wiz-field"><label><?php echo Control_I18n::t('child_response'); ?></label><textarea name="child_response" rows="2"></textarea></div>
            <div class="wiz-grid">
                <div class="wiz-field"><label><?php echo Control_I18n::t('progress_pct'); ?></label><input type="number" name="progress_percentage" min="0" max="100" value="0"></div>
                <div class="wiz-field"><label><?php echo Control_I18n::t('duration_minutes'); ?></label><input type="number" name="duration_minutes" value="60"></div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;"><button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('اعتماد الجلسة', 'control'); ?></button><button type="button" onclick="jQuery('#session-modal').hide()" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none;"><?php _e('إلغاء', 'control'); ?></button></div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Age Calculation Logic
    function calculateAge(dob) {
        if (!dob) return "";
        const birthDate = new Date(dob);
        const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
            years--;
            months += 12;
        }
        return years + " " + "<?php echo Control_I18n::t('years'); ?>" + "، " + months + " " + "<?php echo Control_I18n::t('months'); ?>";
    }

    $('.dob-input-calc').on('change', function() {
        $(this).closest('form').find('.age-display-field').val(calculateAge($(this).val()));
    }).trigger('change');

    // Navigation Logic
    $('.p-nav-item').on('click', function() {
        $('.p-nav-item').removeClass('active');
        $(this).addClass('active');
        const tab = $(this).data('tab');
        $('.p-file-pane').hide();
        $('#' + tab).fadeIn(300);
    });

    // Independent Section Saving
    $('.clinical-save-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const patientId = $(this).data('patient-id');
        $btn.prop('disabled', true).text('<?php _e("جاري الحفظ...", "control"); ?>');

        const formData = $(this).serialize() + '&action=control_save_patient&id=' + patientId + '&nonce=' + control_ajax.nonce;

        $.post(control_ajax.ajax_url, formData, (res) => {
            $btn.prop('disabled', false).text('<?php echo Control_I18n::t("save"); ?>');
            if(res.success) {
                showToast('<?php _e("تم حفظ التعديلات بنجاح.", "control"); ?>');
            } else {
                alert(res.data.message || 'Error occurred');
            }
        });
    });

    // Assessment Logic
    $('.add-assessment-btn').on('click', function() {
        $('#assessment-patient-id').val($(this).data('patient-id'));
        $('#assessment-modal').css('display', 'flex');
    });

    $('#assessment-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_assessment&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    // Referral Logic
    $('.add-referral-btn').on('click', function() {
        $('#referral-patient-id').val($(this).data('patient-id'));
        $('#referral-modal').css('display', 'flex');
    });

    $('#file-referral-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_referral&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    // Session Logic
    $('.add-session-btn').on('click', function() {
        $('#session-patient-id').val($(this).data('patient-id'));
        $('#session-modal').css('display', 'flex');
    });

    $('#file-session-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_fin_session&nonce=' + control_ajax.nonce, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    function showToast(message) {
        $('#pediatric-toast').text(message).fadeIn().delay(3000).fadeOut();
    }

    $(document).on('click', '.delete-assessment-btn', function() {
        if(!confirm('<?php _e("حذف نتيجة الاختبار؟", "control"); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_patient_assessment', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });

    $(document).on('click', '.delete-fin-session', function() {
        if(!confirm('<?php _e("حذف سجل الجلسة؟", "control"); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_fin_session', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });

    $(document).on('click', '.delete-referral-btn', function() {
        if(!confirm('<?php _e("حذف سجل التحويل؟", "control"); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_delete_patient_referral', id: $(this).data('id'), nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.p-nav-item:not(.active):hover { background: #f8fafc; transform: translateX(-5px); }
.wiz-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.wiz-field { margin-bottom: 15px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 0.9rem; background:#fff; transition:0.3s; }
.wiz-field input:focus { border-color: var(--control-primary); outline:none; }
.wiz-field label { display:block; font-size:0.75rem; font-weight:800; color:var(--control-muted); margin-bottom:8px; text-transform:uppercase; }
.control-table th { background:#f8fafc; color:var(--control-muted); font-size:0.75rem; font-weight:800; text-transform:uppercase; padding:15px; border-bottom:1px solid #e2e8f0; }
.control-table td { padding:15px; border-bottom:1px solid #f1f5f9; font-size:0.9rem; }
.patient-status-badge.status-active { background: #ecfdf5; color: #059669; }
.patient-status-badge.status-waiting_list { background: #fff7ed; color: #d97706; }

@media (max-width: 1024px) {
    .patient-file-layout { flex-direction: column; }
    .p-internal-sidebar { width: 100% !important; position: static !important; margin-bottom: 30px; }
}
@media (max-width: 768px) {
    .wiz-grid { grid-template-columns: 1fr; }
}
</style>
</div>
