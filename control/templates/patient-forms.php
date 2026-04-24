<?php
$specialists = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff WHERE role IN ('therapist', 'coach', 'specialist', 'occupational_therapist', 'physical_rehab', 'speech_therapist')");

$is_internal = Control_Auth::is_logged_in();
$strings = Control_I18n::get_all();
?>

<div id="patient-wizard-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10005; align-items:center; justify-content:center; backdrop-filter: blur(8px); padding: 20px;">
    <div class="control-card wizard-container" style="width:100%; max-width:1100px; padding:0; border-radius:24px; overflow:hidden; box-shadow:0 50px 100px -20px rgba(0,0,0,0.4); background:#fff; position:relative;">

        <!-- Language Selection Overlay -->
        <div id="wiz-lang-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background:#fff; z-index:100; display:flex; flex-direction:column; align-items:center; justify-content:center; border-radius:24px;">
            <h2 style="margin-bottom:30px; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('select_lang'); ?></h2>
            <div style="display:flex; gap:30px;">
                <button onclick="setWizLang('ar')" class="lang-sel-btn">
                    <span style="font-size:3rem; margin-bottom:10px;">🇸🇦</span>
                    <span style="font-weight:800;"><?php echo $strings['ar']['lang_ar']; ?></span>
                </button>
                <button onclick="setWizLang('en')" class="lang-sel-btn">
                    <span style="font-size:3rem; margin-bottom:10px;">🇺🇸</span>
                    <span style="font-weight:800;"><?php echo $strings['en']['lang_en']; ?></span>
                </button>
            </div>
        </div>

        <!-- Header & Progress -->
        <div class="wizard-header" style="background:var(--control-primary); color:#fff; padding:30px; text-align:center;">
            <h3 id="wiz-title-text" style="margin:0; font-size:1.6rem; color:#fff; font-weight:800;"><?php echo Control_I18n::t('registration_title'); ?></h3>
            <div id="wiz-age-badge" style="display:inline-block; background:var(--control-accent); color:var(--control-primary); font-size:0.85rem; padding:4px 15px; border-radius:15px; font-weight:900; margin-top:10px; display:none;"></div>

            <div class="wiz-progress-steps" style="display:flex; justify-content:center; gap:15px; margin-top:25px;">
                <div class="wiz-dot active" data-step="1" title="Phase 1: Identification"></div>
                <div class="wiz-dot" data-step="2" title="Phase 2: Guardian"></div>
                <div class="wiz-dot" data-step="3" title="Phase 3: Medical Screening"></div>
                <div class="wiz-dot" data-step="4" title="Phase 4: Functional Assessment"></div>
                <?php if($is_internal): ?>
                    <div class="wiz-dot" data-step="5" title="Phase 5: Evaluation"></div>
                    <div class="wiz-dot" data-step="6" title="Phase 6: Financial Setup"></div>
                    <div class="wiz-dot" data-step="7" title="Phase 7: Activation"></div>
                <?php endif; ?>
            </div>
        </div>

        <form id="patient-wizard-form" style="padding:40px; max-height:70vh; overflow-y:auto; direction: inherit;">
            <input type="hidden" name="id" id="wiz-patient-id">
            <input type="hidden" name="full_name" id="wiz-full-name-concat">
            <input type="hidden" name="is_draft" value="0" id="wiz-is-draft">
            <input type="hidden" name="wizard_lang" id="wiz-selected-lang" value="ar">
            <input type="hidden" name="intake_status" id="wiz-intake-status" value="pending">

            <!-- Phase 1: Child Identification Data -->
            <div class="wiz-step" id="wiz-step-1">
                <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:30px;">
                    <div id="wiz-photo-preview" style="width:110px; height:110px; border-radius:50%; background:#f1f5f9; border:3px dashed var(--control-border); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; position:relative;">
                        <span class="dashicons dashicons-camera" style="font-size:35px; color:var(--control-muted);"></span>
                        <img src="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute;">
                    </div>
                    <button type="button" id="wiz-upload-btn" class="control-btn" style="background:none; color:var(--control-primary) !important; font-weight:800; margin-top:10px; font-size:0.75rem;"><?php echo Control_I18n::t('upload_photo'); ?></button>
                    <input type="hidden" name="profile_photo" id="wiz-photo-input">
                </div>

                <div class="wiz-grid">
                    <div class="wiz-grid-3" style="grid-column: 1 / -1;">
                        <div class="wiz-field">
                            <label data-t="name_first"><?php echo Control_I18n::t('name_first'); ?> *</label>
                            <input type="text" name="name_first" required class="name-part">
                        </div>
                        <div class="wiz-field">
                            <label data-t="name_second"><?php echo Control_I18n::t('name_second'); ?> *</label>
                            <input type="text" name="name_second" required class="name-part">
                        </div>
                        <div class="wiz-field">
                            <label data-t="name_last"><?php echo Control_I18n::t('name_last'); ?> *</label>
                            <input type="text" name="name_last" required class="name-part">
                        </div>
                    </div>
                    <div class="wiz-field">
                        <label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label>
                        <input type="date" name="dob" id="wiz-dob-input" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="gender"><?php echo Control_I18n::t('gender'); ?></label>
                        <select name="gender">
                            <option value="male" data-t="male"><?php echo Control_I18n::t('male'); ?></option>
                            <option value="female" data-t="female"><?php echo Control_I18n::t('female'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="nationality"><?php echo Control_I18n::t('nationality'); ?></label>
                        <select name="nationality">
                            <option value="SA">🇸🇦 Saudi Arabia</option>
                            <option value="AE">🇦🇪 United Arab Emirates</option>
                            <option value="EG">🇪🇬 Egypt</option>
                            <option value="KW">🇰🇼 Kuwait</option>
                            <option value="QA">🇶🇦 Qatar</option>
                            <option value="BH">🇧🇭 Bahrain</option>
                            <option value="OM">🇴🇲 Oman</option>
                            <option value="JO">🇯🇴 Jordan</option>
                            <option value="LB">🇱🇧 Lebanon</option>
                            <option value="SY">🇸🇾 Syria</option>
                            <option value="IQ">🇮🇶 Iraq</option>
                            <option value="SD">🇸🇩 Sudan</option>
                            <option value="MA">🇲🇦 Morocco</option>
                            <option value="DZ">🇩🇿 Algeria</option>
                            <option value="TN">🇹🇳 Tunisia</option>
                            <option value="LY">🇱🇾 Libya</option>
                            <option value="YE">🇾🇪 Yemen</option>
                            <option value="PS">🇵🇸 Palestine</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="country_residence"><?php echo Control_I18n::t('country_residence'); ?></label>
                        <input type="text" name="country_residence">
                    </div>
                    <div class="wiz-field">
                        <label data-t="city_residence"><?php echo Control_I18n::t('city_residence'); ?></label>
                        <input type="text" name="city_residence">
                    </div>
                    <div class="wiz-field">
                        <label data-t="national_id"><?php echo Control_I18n::t('national_id'); ?></label>
                        <input type="text" name="national_id">
                    </div>
                </div>
            </div>

            <!-- Phase 2: Guardian Information -->
            <div class="wiz-step" id="wiz-step-2" style="display:none;">
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label>
                        <input type="text" name="guardian_name" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="relationship"><?php echo Control_I18n::t('relationship'); ?></label>
                        <select name="guardian_relationship">
                            <option value="father">Father</option>
                            <option value="mother">Mother</option>
                            <option value="relative">Relative</option>
                            <option value="legal">Legal Guardian</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="guardian_id"><?php echo Control_I18n::t('guardian_id'); ?></label>
                        <input type="text" name="guardian_id">
                    </div>
                    <div class="wiz-field">
                        <label data-t="father_phone"><?php echo Control_I18n::t('father_phone'); ?> *</label>
                        <input type="tel" name="father_phone" required placeholder="0000000000">
                    </div>
                    <div class="wiz-field">
                        <label data-t="mother_phone"><?php echo Control_I18n::t('mother_phone'); ?></label>
                        <input type="tel" name="mother_phone">
                    </div>
                    <div class="wiz-field">
                        <label data-t="email"><?php echo Control_I18n::t('email'); ?></label>
                        <input type="email" name="email" placeholder="example@email.com">
                    </div>
                    <div class="wiz-field">
                        <label data-t="guardian_nationality"><?php echo Control_I18n::t('guardian_nationality'); ?></label>
                        <input type="text" name="guardian_nationality">
                    </div>
                    <div class="wiz-field">
                        <label data-t="guardian_country"><?php echo Control_I18n::t('guardian_country'); ?></label>
                        <input type="text" name="guardian_country">
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="address"><?php echo Control_I18n::t('address'); ?></label>
                    <input type="text" name="address">
                </div>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="guardian_workplace"><?php echo Control_I18n::t('guardian_workplace'); ?></label>
                        <input type="text" name="guardian_workplace">
                    </div>
                    <div class="wiz-field">
                        <label data-t="emergency_contact"><?php echo Control_I18n::t('emergency_contact'); ?></label>
                        <input type="text" name="emergency_contact">
                    </div>
                    <div class="wiz-field">
                        <label data-t="blood_type"><?php echo Control_I18n::t('blood_type'); ?></label>
                        <select name="blood_type">
                            <option value="">-</option>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="communication_status"><?php echo Control_I18n::t('communication_status'); ?></label>
                        <select name="communication_status">
                            <option value="active">Active</option>
                            <option value="periodic">Periodic</option>
                            <option value="urgent_only">Urgent Only</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Phase 3: Medical Screening -->
            <div class="wiz-step" id="wiz-step-3" style="display:none;">
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="diag_prev"><?php echo Control_I18n::t('diag_prev'); ?></label>
                        <select name="diag_prev">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="diag_prev_details"><?php echo Control_I18n::t('diag_prev_details'); ?></label>
                        <input type="text" name="diag_prev_details">
                    </div>
                    <div class="wiz-field">
                        <label data-t="prev_rehab"><?php echo Control_I18n::t('prev_rehab'); ?></label>
                        <select name="prev_rehab_centers">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="chronic_conditions"><?php echo Control_I18n::t('chronic_conditions'); ?></label>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; background:#f8fafc; padding:15px; border-radius:12px; border:1.5px solid #e2e8f0;">
                            <label style="font-size:0.8rem; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="chronic_conditions[]" value="epilepsy"> <span data-t="epilepsy"><?php echo Control_I18n::t('epilepsy'); ?></span></label>
                            <label style="font-size:0.8rem; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="chronic_conditions[]" value="diabetes"> <span data-t="diabetes"><?php echo Control_I18n::t('diabetes'); ?></span></label>
                            <label style="font-size:0.8rem; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="chronic_conditions[]" value="hearing"> <span data-t="hearing_issues"><?php echo Control_I18n::t('hearing_issues'); ?></span></label>
                            <label style="font-size:0.8rem; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="chronic_conditions[]" value="vision"> <span data-t="vision_issues"><?php echo Control_I18n::t('vision_issues'); ?></span></label>
                        </div>
                    </div>
                    <div class="wiz-field">
                        <label data-t="drug_allergies"><?php echo Control_I18n::t('drug_allergies'); ?></label>
                        <input type="text" name="drug_allergies">
                    </div>
                    <div class="wiz-field">
                        <label data-t="medications"><?php echo Control_I18n::t('medications'); ?></label>
                        <input type="text" name="current_medications">
                    </div>
                    <div class="wiz-field">
                        <label data-t="pregnancy_history"><?php echo Control_I18n::t('pregnancy_history'); ?></label>
                        <textarea name="pregnancy_history" rows="2"></textarea>
                    </div>
                    <div class="wiz-field">
                        <label data-t="birth_history"><?php echo Control_I18n::t('birth_history'); ?></label>
                        <textarea name="birth_history" rows="2"></textarea>
                    </div>
                </div>

                <div class="wiz-grid-3">
                    <div class="wiz-field">
                        <label data-t="motor_delay"><?php echo Control_I18n::t('motor_delay'); ?></label>
                        <select name="motor_delay">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="speech_delay"><?php echo Control_I18n::t('speech_delay'); ?></label>
                        <select name="speech_delay">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="sleep_issues"><?php echo Control_I18n::t('sleep_issues'); ?></label>
                        <select name="sleep_issues">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="feeding_issues"><?php echo Control_I18n::t('feeding_issues'); ?></label>
                        <select name="feeding_issues">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Phase 4: Functional & Behavioral Screening -->
            <div class="wiz-step" id="wiz-step-4" style="display:none;">
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="eval_attention"><?php echo Control_I18n::t('eval_attention'); ?></label>
                        <select name="eval_attention">
                            <option value="good" data-t="good"><?php echo Control_I18n::t('good'); ?></option>
                            <option value="moderate" data-t="average"><?php echo Control_I18n::t('average'); ?></option>
                            <option value="poor" data-t="poor"><?php echo Control_I18n::t('poor'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_name_response"><?php echo Control_I18n::t('eval_name_response'); ?></label>
                        <select name="eval_name_response">
                            <option value="good" data-t="good"><?php echo Control_I18n::t('good'); ?></option>
                            <option value="moderate" data-t="average"><?php echo Control_I18n::t('average'); ?></option>
                            <option value="poor" data-t="poor"><?php echo Control_I18n::t('poor'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_eye_contact"><?php echo Control_I18n::t('eval_eye_contact'); ?></label>
                        <select name="eval_eye_contact">
                            <option value="good" data-t="good"><?php echo Control_I18n::t('good'); ?></option>
                            <option value="moderate" data-t="average"><?php echo Control_I18n::t('average'); ?></option>
                            <option value="poor" data-t="poor"><?php echo Control_I18n::t('poor'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_social"><?php echo Control_I18n::t('eval_social'); ?></label>
                        <select name="eval_social">
                            <option value="positive" data-t="good"><?php echo Control_I18n::t('good'); ?></option>
                            <option value="limited" data-t="average"><?php echo Control_I18n::t('average'); ?></option>
                            <option value="isolated" data-t="poor"><?php echo Control_I18n::t('poor'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_tantrums"><?php echo Control_I18n::t('eval_tantrums'); ?></label>
                        <select name="eval_tantrums">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_instructions"><?php echo Control_I18n::t('eval_instructions'); ?></label>
                        <select name="eval_instructions">
                            <option value="high" data-t="high"><?php echo Control_I18n::t('high'); ?></option>
                            <option value="moderate" data-t="moderate"><?php echo Control_I18n::t('moderate'); ?></option>
                            <option value="low" data-t="low"><?php echo Control_I18n::t('low'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_activity_level"><?php echo Control_I18n::t('eval_activity_level'); ?></label>
                        <select name="eval_activity_level">
                            <option value="low" data-t="low"><?php echo Control_I18n::t('low'); ?></option>
                            <option value="normal" data-t="average"><?php echo Control_I18n::t('average'); ?></option>
                            <option value="high" data-t="high"><?php echo Control_I18n::t('high'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_independence"><?php echo Control_I18n::t('eval_independence'); ?></label>
                        <select name="eval_independence">
                            <option value="high" data-t="high"><?php echo Control_I18n::t('high'); ?></option>
                            <option value="medium" data-t="moderate"><?php echo Control_I18n::t('moderate'); ?></option>
                            <option value="low" data-t="low"><?php echo Control_I18n::t('low'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_language"><?php echo Control_I18n::t('eval_language'); ?></label>
                        <select name="eval_language">
                            <option value="non_verbal">Non-verbal</option>
                            <option value="words">Words</option>
                            <option value="sentences">Sentences</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="eval_anxiety"><?php echo Control_I18n::t('eval_anxiety'); ?></label>
                        <select name="eval_anxiety">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="behavioral_observation"><?php echo Control_I18n::t('behavioral_observation'); ?></label>
                    <textarea name="initial_behavioral_observation" rows="3"></textarea>
                </div>
                <div class="wiz-field">
                    <label data-t="initial_diagnosis"><?php echo Control_I18n::t('initial_diagnosis'); ?></label>
                    <textarea name="initial_diagnosis" rows="2"></textarea>
                </div>
            </div>

            <!-- Phase 5 & 6 are internal only -->
            <?php if($is_internal): ?>
            <div class="wiz-step" id="wiz-step-5" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:8px;" data-t="phase_5_title"><?php echo Control_I18n::t('phase_5_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="case_classification"><?php echo Control_I18n::t('case_classification'); ?></label>
                        <select name="case_classification">
                            <option value="normal">Normal</option>
                            <option value="possible_delay">Possible Delay</option>
                            <option value="needs_evaluation">Needs Evaluation</option>
                            <option value="needs_intervention">Needs Intervention</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="priority_level"><?php echo Control_I18n::t('priority_level'); ?></label>
                        <select name="priority_level">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="suggested_pathway"><?php echo Control_I18n::t('suggested_pathway'); ?></label>
                        <select name="suggested_pathway">
                            <option value="consultation">Consultation</option>
                            <option value="assessment">Assessment</option>
                            <option value="rehab_program">Rehabilitation Program</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="final_decision"><?php echo Control_I18n::t('final_decision'); ?></label>
                        <select name="final_decision">
                            <option value="pre_acceptance">Pre-acceptance</option>
                            <option value="rejected">Rejected</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="temp_id"><?php echo Control_I18n::t('temp_id'); ?></label>
                        <input type="text" name="temp_id" id="wiz-temp-id" readonly style="background:#f8fafc;">
                    </div>
                    <div class="wiz-field">
                        <label data-t="assigned_team"><?php echo Control_I18n::t('assigned_team'); ?></label>
                        <textarea name="assigned_specialists" rows="2"></textarea>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="internal_notes"><?php echo Control_I18n::t('internal_notes'); ?></label>
                    <textarea name="internal_notes" rows="3"></textarea>
                </div>
            </div>

            <div class="wiz-step" id="wiz-step-6" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:8px;" data-t="financial_setup"><?php echo Control_I18n::t('financial_setup'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="registration_cost"><?php echo Control_I18n::t('registration_cost'); ?></label>
                        <input type="number" name="registration_cost" step="0.01">
                    </div>
                    <div class="wiz-field">
                        <label data-t="payment_model"><?php echo Control_I18n::t('payment_model'); ?></label>
                        <select name="payment_model">
                            <option value="one_time" data-t="one_time"><?php echo Control_I18n::t('one_time'); ?></option>
                            <option value="daily" data-t="daily"><?php echo Control_I18n::t('daily'); ?></option>
                            <option value="weekly" data-t="weekly"><?php echo Control_I18n::t('weekly'); ?></option>
                            <option value="monthly" data-t="monthly"><?php echo Control_I18n::t('monthly'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="billing_type"><?php echo Control_I18n::t('billing_type'); ?></label>
                        <select name="billing_type">
                            <option value="session_based" data-t="session_based"><?php echo Control_I18n::t('session_based'); ?></option>
                            <option value="subscription_based" data-t="subscription_based"><?php echo Control_I18n::t('subscription_based'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="amount_per_cycle"><?php echo Control_I18n::t('amount_per_cycle'); ?></label>
                        <input type="number" name="amount_per_cycle" step="0.01">
                    </div>
                </div>
            </div>

            <div class="wiz-step" id="wiz-step-7" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:8px;" data-t="phase_6_title"><?php echo Control_I18n::t('phase_6_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="permanent_id"><?php echo Control_I18n::t('permanent_id'); ?></label>
                        <input type="text" name="permanent_id" id="wiz-perm-id">
                    </div>
                    <div class="wiz-field">
                        <label data-t="case_status"><?php echo Control_I18n::t('case_status'); ?></label>
                        <select name="case_status">
                            <option value="active" data-t="active"><?php echo Control_I18n::t('active'); ?></option>
                            <option value="evaluation_only" data-t="evaluation_only"><?php echo Control_I18n::t('evaluation_only'); ?></option>
                            <option value="waiting_list" data-t="waiting_list"><?php echo Control_I18n::t('waiting_list'); ?></option>
                            <option value="completed" data-t="completed"><?php echo Control_I18n::t('completed'); ?></option>
                            <option value="closed" data-t="closed"><?php echo Control_I18n::t('closed'); ?></option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label data-t="account_activation"><?php echo Control_I18n::t('account_activation'); ?></label>
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="activate_account" value="1"> <span data-t="account_activation">Activate Guardian Account</span>
                        </label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </form>

        <div class="wiz-footer" style="padding:20px 40px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; border-radius: 0 0 24px 24px;">
            <button type="button" id="wiz-prev-btn" class="control-btn" style="background:#fff; border:1px solid #cbd5e1; color:#475569 !important; display:none; padding:8px 25px; font-weight:700;"><?php echo Control_I18n::t('prev'); ?></button>
            <div style="flex:1;"></div>
            <button type="button" id="wiz-next-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:8px 40px; font-weight:800;"><?php echo Control_I18n::t('next'); ?></button>
            <button type="button" id="wiz-save-btn" class="control-btn" style="background:var(--control-accent); border:none; color:var(--control-primary) !important; padding:8px 40px; font-weight:900; display:none;"><?php echo Control_I18n::t('save'); ?></button>
        </div>
    </div>
</div>

<style>
.wiz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.wiz-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
.wiz-field { margin-bottom: 15px; }
.wiz-field label { display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.9rem; transition: 0.3s; }
.wiz-field input:focus { border-color: var(--control-primary); outline: none; }
.wiz-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.3); transition: 0.4s; }
.wiz-dot.active { background: var(--control-accent); transform: scale(1.3); }
@media (max-width: 768px) { .wiz-grid, .wiz-grid-3 { grid-template-columns: 1fr; } }
</style>

<script>
const wizStrings = <?php echo json_encode($strings); ?>;
let currentWizStep = 1;
const isInternalUser = <?php echo $is_internal ? 'true' : 'false'; ?>;
const totalWizSteps = isInternalUser ? 7 : 4;

function setWizLang(lang) {
    jQuery('#wiz-selected-lang').val(lang);
    jQuery('#wiz-lang-overlay').fadeOut();
    const s = wizStrings[lang];
    jQuery('#wiz-title-text').text(s.registration_title);
    jQuery('#wiz-next-btn').text(s.next);
    jQuery('#wiz-prev-btn').text(s.prev);
    jQuery('#wiz-save-btn').text(s.save);
    jQuery('[data-t]').each(function() {
        const key = jQuery(this).data('t');
        if(s[key]) jQuery(this).text(s[key]);
    });
    jQuery('#patient-wizard-form').css('direction', lang === 'ar' ? 'rtl' : 'ltr');

    // Persist language to session
    jQuery.post(control_ajax.ajax_url, { action: 'control_update_session_lang', lang: lang, nonce: control_ajax.nonce });
}

jQuery(document).ready(function($) {
    $('.name-part').on('input', function() {
        const f = $('[name="name_first"]').val().trim();
        const s = $('[name="name_second"]').val().trim();
        const l = $('[name="name_last"]').val().trim();
        $('#wiz-full-name-concat').val(`${f} ${s} ${l}`.trim());
    });

    $('[name="has_meds"]').on('change', function() {
        $('#wiz-meds-details').css('display', $(this).val() === 'yes' ? 'flex' : 'none');
    });

    $('#wiz-dob-input').on('change', function() {
        const dobVal = $(this).val();
        if (!dobVal) return;
        const dob = new Date(dobVal);
        const now = new Date();

        let years = now.getFullYear() - dob.getFullYear();
        let months = now.getMonth() - dob.getMonth();
        let days = now.getDate() - dob.getDate();

        if (days < 0) {
            months--;
            const lastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
            days += lastMonth.getDate();
        }
        if (months < 0) {
            years--;
            months += 12;
        }

        const s = wizStrings[$('#wiz-selected-lang').val()];
        $('#wiz-age-badge').text(`${years} ${s.years}, ${months} ${s.months}, ${days} ${s.days}`).fadeIn();
    });

    $('#wiz-next-btn').on('click', function() {
        if(currentWizStep < totalWizSteps) {
            $(`#wiz-step-${currentWizStep}`).hide();
            currentWizStep++;
            $(`#wiz-step-${currentWizStep}`).fadeIn();
            updateWizUI();
        }
    });

    $('#wiz-prev-btn').on('click', function() {
        if(currentWizStep > 1) {
            $(`#wiz-step-${currentWizStep}`).hide();
            currentWizStep--;
            $(`#wiz-step-${currentWizStep}`).fadeIn();
            updateWizUI();
        }
    });

    function updateWizUI() {
        $('#wiz-prev-btn').toggle(currentWizStep > 1);
        $('#wiz-next-btn').toggle(currentWizStep < totalWizSteps);
        $('#wiz-save-btn').toggle(currentWizStep === totalWizSteps);
        $('.wiz-dot').removeClass('active');
        $(`.wiz-dot[data-step="${currentWizStep}"]`).addClass('active');
    }

    $('#wiz-save-btn').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        const formData = $('#patient-wizard-form').serialize() + '&action=control_save_patient&nonce=' + control_ajax.nonce;
        $.post(control_ajax.ajax_url, formData, (res) => {
            if(res.success) {
                const kioskUrl = '<?php echo get_permalink(get_page_by_path("kiosk-registration")); ?>';
                if(kioskUrl && !$('#wiz-patient-id').val()) {
                    window.location.href = kioskUrl + (kioskUrl.includes('?') ? '&' : '?') + 'resume_id=' + res.data.id;
                } else {
                    location.reload();
                }
            } else {
                const msg = (typeof res.data === 'string') ? res.data : (res.data.message || 'Error occurred');
                alert(msg);
                $btn.prop('disabled', false).text(wizStrings[$('#wiz-selected-lang').val()].save);
            }
        });
    });

    window.updateWizUI = updateWizUI;
});

window.openPatientModal = function(data = null) {
    const $ = jQuery;
    $('#patient-wizard-form')[0].reset();
    $('#wiz-meds-details').hide();
    $('#wiz-photo-preview img').hide();
    $('#wiz-photo-preview span').show();
    $('#wiz-lang-overlay').show();
    currentWizStep = 1;
    window.updateWizUI();
    $('.wiz-step').hide();
    $('#wiz-step-1').show();

    if(!data) {
        // Auto-generate temp ID for new records
        $('#wiz-temp-id').val('TEMP-' + Math.random().toString(36).substr(2, 9).toUpperCase());
    }

    if(data) {
        $('#wiz-lang-overlay').hide();
        setWizLang('ar');
        $('#wiz-patient-id').val(data.id);
        Object.keys(data).forEach(key => {
            const field = $(`#patient-wizard-form [name="${key}"]`);
            if (field.length) {
                if(field.is(':checkbox')) {
                    if(data[key]) {
                        const vals = data[key].split(', ');
                        field.each(function() { if(vals.includes($(this).val())) $(this).prop('checked', true); });
                    }
                } else {
                    field.val(data[key]);
                }
            }
        });
        $('#wiz-dob-input').trigger('change');
    }
    $('#patient-wizard-modal').css('display', 'flex');
}
</script>
