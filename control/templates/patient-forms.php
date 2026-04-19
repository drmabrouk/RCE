<?php
$specialists = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff WHERE role IN ('therapist', 'coach', 'specialist', 'occupational_therapist', 'physical_rehab', 'speech_therapist')");

$is_internal = Control_Auth::is_logged_in();
$strings = Control_I18n::get_all();
?>

<div id="patient-wizard-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10005; align-items:center; justify-content:center; backdrop-filter: blur(8px); padding: 20px;">
    <div class="control-card wizard-container" style="width:100%; max-width:1000px; padding:0; border-radius:24px; overflow:hidden; box-shadow:0 50px 100px -20px rgba(0,0,0,0.4); background:#fff; position:relative;">

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
                <div class="wiz-dot active" data-step="1"></div>
                <div class="wiz-dot" data-step="2"></div>
                <div class="wiz-dot" data-step="3"></div>
                <div class="wiz-dot" data-step="4"></div>
                <?php if($is_internal): ?><div class="wiz-dot" data-step="5"></div><?php endif; ?>
            </div>
        </div>

        <form id="patient-wizard-form" style="padding:40px; max-height:70vh; overflow-y:auto; direction: inherit;">
            <input type="hidden" name="id" id="wiz-patient-id">
            <input type="hidden" name="is_draft" value="0" id="wiz-is-draft">
            <input type="hidden" name="wizard_lang" id="wiz-selected-lang" value="ar">

            <!-- Step 1: Identity -->
            <div class="wiz-step" id="wiz-step-1">
                <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:30px;">
                    <div id="wiz-photo-preview" style="width:120px; height:120px; border-radius:50%; background:#f1f5f9; border:3px dashed var(--control-border); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; position:relative;">
                        <span class="dashicons dashicons-camera" style="font-size:40px; color:var(--control-muted);"></span>
                        <img src="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute;">
                    </div>
                    <button type="button" id="wiz-upload-btn" class="control-btn" style="background:none; color:var(--control-primary) !important; font-weight:800; margin-top:10px; font-size:0.8rem;"><?php echo Control_I18n::t('upload_photo'); ?></button>
                    <input type="hidden" name="profile_photo" id="wiz-photo-input">
                </div>

                <div class="wiz-grid">
                    <!-- Row 1: Identity -->
                    <div class="wiz-field">
                        <label data-t="child_name"><?php echo Control_I18n::t('child_name'); ?> *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label>
                        <input type="text" name="guardian_name" required>
                    </div>

                    <!-- Row 2: Demographics -->
                    <div class="wiz-field">
                        <label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label>
                        <input type="date" name="dob" id="wiz-dob-input" required>
                    </div>
                    <div class="wiz-field" style="display:flex; align-items:flex-end;">
                        <div id="wiz-age-display" style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:12px; width:100%; font-weight:700; color:var(--control-primary); font-size:0.9rem;">
                            <?php echo Control_I18n::t('age'); ?>: ---
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Physical & Gender -->
            <div class="wiz-step" id="wiz-step-2" style="display:none;">
                <div class="wiz-grid-3">
                    <div class="wiz-field">
                        <label data-t="height"><?php echo Control_I18n::t('height'); ?> (cm)</label>
                        <input type="number" name="height">
                    </div>
                    <div class="wiz-field">
                        <label data-t="weight"><?php echo Control_I18n::t('weight'); ?> (kg)</label>
                        <input type="number" name="weight">
                    </div>
                    <div class="wiz-field">
                        <label data-t="gender"><?php echo Control_I18n::t('gender'); ?></label>
                        <select name="gender">
                            <option value="male" data-t="male"><?php echo Control_I18n::t('male'); ?></option>
                            <option value="female" data-t="female"><?php echo Control_I18n::t('female'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="wiz-field" style="margin-top:20px;">
                    <label data-t="nationality"><?php echo Control_I18n::t('nationality'); ?></label>
                    <select name="nationality" id="wiz-nationality-sel">
                        <option value="SA">🇸🇦 Saudi Arabia</option>
                        <option value="EG">🇪🇬 Egypt</option>
                        <option value="AE">🇦🇪 UAE</option>
                        <option value="JO">🇯🇴 Jordan</option>
                        <option value="KW">🇰🇼 Kuwait</option>
                        <option value="BH">🇧🇭 Bahrain</option>
                        <option value="QA">🇶🇦 Qatar</option>
                        <option value="OM">🇴🇲 Oman</option>
                        <option value="US">🇺🇸 USA</option>
                        <option value="GB">🇬🇧 UK</option>
                    </select>
                </div>
            </div>

            <!-- Step 3: Contact Details -->
            <div class="wiz-step" id="wiz-step-3" style="display:none;">
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="guardian_phone"><?php echo Control_I18n::t('guardian_phone'); ?> *</label>
                        <input type="tel" name="father_phone" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="phone"><?php echo Control_I18n::t('phone'); ?> (2)</label>
                        <input type="tel" name="mother_phone">
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="email"><?php echo Control_I18n::t('email'); ?></label>
                    <input type="email" name="email">
                </div>
                <div class="wiz-field">
                    <label data-t="address"><?php echo Control_I18n::t('address'); ?></label>
                    <textarea name="address" rows="2"></textarea>
                </div>
            </div>

            <!-- Step 4: Initial Intake -->
            <div class="wiz-step" id="wiz-step-4" style="display:none;">
                <div class="wiz-field">
                    <label data-t="intake_reason"><?php echo Control_I18n::t('intake_reason'); ?></label>
                    <textarea name="intake_reason" rows="3"></textarea>
                </div>
                <div class="wiz-field">
                    <label data-t="medical_history"><?php echo Control_I18n::t('medical_history'); ?></label>
                    <textarea name="pregnancy_history" rows="3"></textarea>
                </div>
            </div>

            <!-- Step 5: Internal Admin (Only for logged in staff) -->
            <?php if($is_internal): ?>
            <div class="wiz-step" id="wiz-step-5" style="display:none;">
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="system_id"><?php echo Control_I18n::t('system_id'); ?></label>
                        <input type="text" name="system_id">
                    </div>
                    <div class="wiz-field">
                        <label data-t="internal_classification"><?php echo Control_I18n::t('internal_classification'); ?></label>
                        <select name="internal_classification">
                            <option value="gold">VIP / Gold</option>
                            <option value="standard">Standard</option>
                            <option value="charity">Social / Charity</option>
                        </select>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="internal_notes"><?php echo Control_I18n::t('internal_notes'); ?></label>
                    <textarea name="internal_notes" rows="3"></textarea>
                </div>
                <div class="wiz-field">
                    <label data-t="workflow_metadata"><?php echo Control_I18n::t('workflow_metadata'); ?></label>
                    <textarea name="workflow_metadata" rows="2" placeholder="JSON or encoded metadata..."></textarea>
                </div>
            </div>
            <?php endif; ?>

        </form>

        <div class="wiz-footer" style="padding:25px 40px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; border-radius: 0 0 24px 24px;">
            <button type="button" id="wiz-prev-btn" class="control-btn" style="background:#fff; border:1px solid #cbd5e1; color:#475569 !important; display:none; padding:10px 25px; font-weight:700;"><?php echo Control_I18n::t('prev'); ?></button>
            <div style="flex:1;"></div>
            <button type="button" id="wiz-next-btn" class="control-btn" style="background:var(--control-primary); border:none; padding:10px 40px; font-weight:800;"><?php echo Control_I18n::t('next'); ?></button>
            <button type="button" id="wiz-save-btn" class="control-btn" style="background:var(--control-accent); border:none; color:var(--control-primary) !important; padding:10px 40px; font-weight:900; display:none;"><?php echo Control_I18n::t('save'); ?></button>
        </div>
    </div>
</div>

<style>
.wiz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.wiz-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
.wiz-field { margin-bottom: 20px; }
.wiz-field label { display: block; font-size: 0.8rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 0.95rem; transition: 0.3s; background: #fff; }
.wiz-field input:focus { border-color: var(--control-primary); outline: none; box-shadow: 0 0 0 4px rgba(0,0,0,0.05); }

.wiz-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.3); transition: 0.4s; }
.wiz-dot.active { background: var(--control-accent); transform: scale(1.3); }

.lang-sel-btn { flex: 1; min-width: 150px; padding: 40px 20px; border: 2px solid #f1f5f9; border-radius: 20px; background: #fff; cursor: pointer; display: flex; flex-direction: column; align-items: center; transition: 0.3s; }
.lang-sel-btn:hover { border-color: var(--control-primary); background: #f8fafc; transform: translateY(-5px); }

@media (max-width: 768px) {
    .wiz-grid, .wiz-grid-3 { grid-template-columns: 1fr; }
    .wizard-container { border-radius: 0; height: 100%; max-height: 100%; }
}

[dir="rtl"] .wiz-field label { text-align: right; }
</style>

<script>
const wizStrings = <?php echo json_encode($strings); ?>;
let currentWizStep = 1;
const totalWizSteps = <?php echo $is_internal ? 5 : 4; ?>;

function setWizLang(lang) {
    jQuery('#wiz-selected-lang').val(lang);
    jQuery('#wiz-lang-overlay').fadeOut();

    // Persist to session
    jQuery.post(control_ajax.ajax_url, {
        action: 'control_update_session_lang',
        lang: lang,
        nonce: control_ajax.nonce
    });

    // Update UI Strings
    const s = wizStrings[lang];
    jQuery('#wiz-title-text').text(s.registration_title);
    jQuery('#wiz-next-btn').text(s.next);
    jQuery('#wiz-prev-btn').text(s.prev);
    jQuery('#wiz-save-btn').text(s.save);

    jQuery('[data-t]').each(function() {
        const key = jQuery(this).data('t');
        if(s[key]) jQuery(this).text(s[key]);
    });

    // Set form direction
    if(lang === 'ar') {
        jQuery('#patient-wizard-form').css('direction', 'rtl');
    } else {
        jQuery('#patient-wizard-form').css('direction', 'ltr');
    }
}

function calculateDetailedAge(dobString) {
    if(!dobString) return '---';
    const dob = new Date(dobString);
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

    const lang = jQuery('#wiz-selected-lang').val();
    const s = wizStrings[lang];

    return `${years} ${s.years}, ${months} ${s.months}, ${days} ${s.days}`;
}

jQuery(document).ready(function($) {
    $('#wiz-dob-input').on('change', function() {
        const ageStr = calculateDetailedAge($(this).val());
        const lang = $('#wiz-selected-lang').val();
        $('#wiz-age-display').text(wizStrings[lang].age + ': ' + ageStr);
        $('#wiz-age-badge').text(ageStr).fadeIn();
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

    $('#wiz-upload-btn, #wiz-photo-preview').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'Select Child Photo', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#wiz-photo-input').val(attachment.url);
            $('#wiz-photo-preview img').attr('src', attachment.url).show();
            $('#wiz-photo-preview span').hide();
        });
    });

    $('#wiz-save-btn').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');

        const formData = $('#patient-wizard-form').serialize() + '&action=control_save_patient&nonce=' + control_ajax.nonce;
        $.post(control_ajax.ajax_url, formData, function(res) {
            if(res.success) {
                location.reload();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('Save File');
            }
        });
    });
});

window.openPatientModal = function(data = null) {
    const $ = jQuery;
    $('#patient-wizard-form')[0].reset();
    $('#wiz-patient-id').val('');
    $('#wiz-photo-preview img').hide();
    $('#wiz-photo-preview span').show();
    $('#wiz-lang-overlay').show();

    currentWizStep = 1;
    updateWizUI();
    $('.wiz-step').hide();
    $('#wiz-step-1').show();

    if(data) {
        $('#wiz-lang-overlay').hide();
        setWizLang('ar');
        $('#wiz-patient-id').val(data.id);
        $('[name="full_name"]').val(data.full_name);
        $('[name="guardian_name"]').val(data.guardian_name);
        $('[name="dob"]').val(data.dob).trigger('change');
        $('[name="height"]').val(data.height);
        $('[name="weight"]').val(data.weight);
        $('[name="gender"]').val(data.gender);
        $('[name="nationality"]').val(data.nationality);
        $('[name="father_phone"]').val(data.father_phone);
        $('[name="mother_phone"]').val(data.mother_phone);
        $('[name="email"]').val(data.email);
        $('[name="address"]').val(data.address);
        $('[name="intake_reason"]').val(data.intake_reason);
        $('[name="pregnancy_history"]').val(data.pregnancy_history);
        $('[name="system_id"]').val(data.system_id);
        $('[name="internal_classification"]').val(data.internal_classification);
        $('[name="internal_notes"]').val(data.internal_notes);
        $('[name="workflow_metadata"]').val(data.workflow_metadata);

        if (data.profile_photo) {
            $('#wiz-photo-preview img').attr('src', data.profile_photo).show();
            $('#wiz-photo-preview span').hide();
            $('#wiz-photo-input').val(data.profile_photo);
        }
    }

    $('#patient-wizard-modal').css('display', 'flex');
}
</script>
