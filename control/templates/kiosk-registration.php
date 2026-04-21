<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$strings = Control_I18n::get_all();
?>

<div id="kiosk-app-root" style="direction:rtl; font-family:'Rubik', sans-serif; min-height:600px; background:#f8fafc; padding:40px 20px; display:flex; align-items:center; justify-content:center;">
    <div style="max-width:850px; width:100%; background:#fff; border-radius:30px; box-shadow:0 25px 60px rgba(0,0,0,0.08); overflow:hidden; position:relative;">

        <!-- Progress Header -->
        <div id="k-header" style="background:var(--control-primary); padding:35px 40px; color:#fff; text-align:center; position:relative;">
            <div style="position:absolute; top:20px; left:20px; display:flex; gap:10px;">
                <button type="button" onclick="setKLang('ar')" style="background:rgba(255,255,255,0.1); border:1.5px solid rgba(255,255,255,0.3); color:#fff; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:0.8rem; font-weight:bold;">AR</button>
                <button type="button" onclick="setKLang('en')" style="background:rgba(255,255,255,0.1); border:1.5px solid rgba(255,255,255,0.3); color:#fff; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:0.8rem; font-weight:bold;">EN</button>
            </div>
            <h3 id="k-header-title" style="margin:0; font-size:1.5rem; color:#fff; font-weight:800;"><?php echo Control_I18n::t('registration_title'); ?></h3>
            <div style="display:flex; justify-content:center; gap:12px; margin-top:25px;">
                <div class="k-dot active" data-step="1"></div>
                <div class="k-dot" data-step="2"></div>
                <div class="k-dot" data-step="3"></div>
                <div class="k-dot" data-step="4"></div>
            </div>
        </div>

        <form id="kiosk-form" style="padding:45px;">
            <input type="hidden" name="k_lang" id="k-selected-lang" value="ar">
            <input type="hidden" name="full_name" id="k-full-name-concat">

            <!-- Phase 1: Identity -->
            <div id="k-step-1" class="k-step-content">
                <h4 style="color:var(--control-primary); margin-bottom:30px; font-weight:800;" data-t="phase_1_title"><?php echo Control_I18n::t('phase_1_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="first_name"><?php echo Control_I18n::t('first_name'); ?> *</label><input type="text" name="name_first" required class="k-name-part"></div>
                    <div class="wiz-field"><label data-t="second_name"><?php echo Control_I18n::t('second_name'); ?> *</label><input type="text" name="name_second" required class="k-name-part"></div>
                    <div class="wiz-field"><label data-t="third_name"><?php echo Control_I18n::t('third_name'); ?> *</label><input type="text" name="name_third" required class="k-name-part"></div>
                    <div class="wiz-field"><label data-t="last_name"><?php echo Control_I18n::t('last_name'); ?> *</label><input type="text" name="name_last" required class="k-name-part"></div>
                    <div class="wiz-field"><label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label><input type="date" name="dob" required></div>
                    <div class="wiz-field"><label data-t="gender"><?php echo Control_I18n::t('gender'); ?></label>
                        <select name="gender"><option value="male" data-t="male"><?php echo Control_I18n::t('male'); ?></option><option value="female" data-t="female"><?php echo Control_I18n::t('female'); ?></option></select>
                    </div>
                    <div class="wiz-field"><label data-t="nationality"><?php echo Control_I18n::t('nationality'); ?></label>
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
                </div>
            </div>

            <!-- Phase 2: Contact -->
            <div id="k-step-2" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:30px; font-weight:800;" data-t="phase_2_title"><?php echo Control_I18n::t('phase_2_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label><input type="text" name="guardian_name" required></div>
                    <div class="wiz-field"><label data-t="father_phone"><?php echo Control_I18n::t('father_phone'); ?> *</label><input type="tel" name="father_phone" required pattern="[0-9]*"></div>
                    <div class="wiz-field"><label data-t="mother_phone"><?php echo Control_I18n::t('mother_phone'); ?></label><input type="tel" name="mother_phone" pattern="[0-9]*"></div>
                    <div class="wiz-field"><label data-t="email"><?php echo Control_I18n::t('email'); ?></label><input type="email" name="email"></div>
                </div>
                <div class="wiz-field"><label data-t="address"><?php echo Control_I18n::t('address'); ?></label><input type="text" name="address"></div>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="emergency_contact"><?php echo Control_I18n::t('emergency_contact'); ?></label><input type="text" name="emergency_contact"></div>
                    <div class="wiz-field"><label data-t="blood_type"><?php echo Control_I18n::t('blood_type'); ?></label>
                        <select name="blood_type"><option value="">-</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option></select>
                    </div>
                </div>
            </div>

            <!-- Phase 3: Medical -->
            <div id="k-step-3" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:30px; font-weight:800;" data-t="phase_3_title"><?php echo Control_I18n::t('phase_3_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="pregnancy_history"><?php echo Control_I18n::t('pregnancy_history'); ?></label><textarea name="pregnancy_history" rows="2"></textarea></div>
                    <div class="wiz-field"><label data-t="birth_history"><?php echo Control_I18n::t('birth_history'); ?></label><textarea name="birth_history" rows="2"></textarea></div>
                </div>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="walking"><?php echo Control_I18n::t('walking'); ?></label>
                        <select name="milestones_walking"><option value="on_time" data-t="on_time"><?php echo Control_I18n::t('on_time'); ?></option><option value="early" data-t="early"><?php echo Control_I18n::t('early'); ?></option><option value="delayed" data-t="delayed"><?php echo Control_I18n::t('delayed'); ?></option></select>
                    </div>
                    <div class="wiz-field"><label data-t="speaking"><?php echo Control_I18n::t('speaking'); ?></label>
                        <select name="milestones_speaking"><option value="on_time" data-t="on_time"><?php echo Control_I18n::t('on_time'); ?></option><option value="early" data-t="early"><?php echo Control_I18n::t('early'); ?></option><option value="delayed" data-t="delayed"><?php echo Control_I18n::t('delayed'); ?></option></select>
                    </div>
                    <div class="wiz-field"><label data-t="sitting"><?php echo Control_I18n::t('sitting'); ?></label>
                        <select name="milestones_sitting"><option value="on_time" data-t="on_time"><?php echo Control_I18n::t('on_time'); ?></option><option value="early" data-t="early"><?php echo Control_I18n::t('early'); ?></option><option value="delayed" data-t="delayed"><?php echo Control_I18n::t('delayed'); ?></option></select>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="chronic_conditions"><?php echo Control_I18n::t('chronic_conditions'); ?></label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; background:#f8fafc; padding:20px; border-radius:15px; border:1.5px solid #e2e8f0;">
                        <label style="font-size:0.95rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="epilepsy"> <span data-t="epilepsy"><?php echo Control_I18n::t('epilepsy'); ?></span></label>
                        <label style="font-size:0.95rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="diabetes"> <span data-t="diabetes"><?php echo Control_I18n::t('diabetes'); ?></span></label>
                        <label style="font-size:0.95rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="hearing"> <span data-t="hearing_issues"><?php echo Control_I18n::t('hearing_issues'); ?></span></label>
                        <label style="font-size:0.95rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="vision"> <span data-t="vision_issues"><?php echo Control_I18n::t('vision_issues'); ?></span></label>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="medications"><?php echo Control_I18n::t('medications'); ?></label>
                    <div style="display:flex; gap:20px; margin-bottom:15px;">
                        <label style="font-size:1rem;"><input type="radio" name="has_meds" value="no" checked> <span data-t="medication_no"><?php echo Control_I18n::t('medication_no'); ?></span></label>
                        <label style="font-size:1rem;"><input type="radio" name="has_meds" value="yes"> <span data-t="medication_yes"><?php echo Control_I18n::t('medication_yes'); ?></span></label>
                    </div>
                    <div id="k-meds-details" style="display:none; gap:12px; flex-direction:column;">
                        <input type="text" name="current_medications" data-t="medications" placeholder="<?php echo Control_I18n::t('medications'); ?>">
                    </div>
                </div>
            </div>

            <!-- Phase 4: Assessment -->
            <div id="k-step-4" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:30px; font-weight:800;" data-t="phase_4_title"><?php echo Control_I18n::t('phase_4_title'); ?></h4>
                <div class="wiz-field">
                    <label data-t="intake_desc"><?php echo Control_I18n::t('intake_desc'); ?> *</label>
                    <textarea name="intake_reason" rows="4" required></textarea>
                </div>
                <div style="background:#fefce8; border:1px solid #fef08a; padding:20px; border-radius:15px; color:#854d0e; font-size:0.95rem;" data-t="screening_desc">⚠️ <?php echo Control_I18n::t('screening_desc'); ?></div>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:50px;">
                <button type="button" id="k-prev" onclick="prevKStep()" class="k-btn-secondary" style="display:none;"><?php echo Control_I18n::t('prev'); ?></button>
                <div style="flex:1;"></div>
                <button type="button" id="k-next" onclick="nextKStep()" class="k-btn-primary"><?php echo Control_I18n::t('next'); ?></button>
                <button type="submit" id="k-submit" style="display:none;" class="k-btn-primary"><?php echo Control_I18n::t('save'); ?></button>
            </div>
        </form>

        <div id="kiosk-success" style="display:none; padding:100px 40px; text-align:center;">
            <div style="width:130px; height:130px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 40px; color:#10b981; box-shadow:0 15px 35px rgba(16,185,129,0.1);"><span class="dashicons dashicons-yes-alt" style="font-size:85px; width:85px; height:85px;"></span></div>
            <h1 style="font-weight:800; color:#065f46; font-size:2.8rem;" data-t="success_title"><?php echo Control_I18n::t('success_title'); ?></h1>
            <p style="font-size:1.5rem; color:#475569; margin-bottom:50px;" data-t="success_desc"><?php echo Control_I18n::t('success_desc'); ?></p>
            <button onclick="location.reload()" class="control-btn" style="background:var(--control-primary); color:#fff !important; padding:20px 80px; border-radius:18px; font-weight:800; font-size:1.2rem;" data-t="finish_btn"><?php echo Control_I18n::t('finish_btn'); ?></button>
        </div>
    </div>
</div>

<style>
.wiz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
.wiz-field { margin-bottom: 25px; }
.wiz-field label { display: block; font-size: 1rem; font-weight: 800; color: #475569; margin-bottom: 10px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 18px; border-radius: 15px; border: 2.5px solid #e2e8f0; font-size: 1.2rem; background:#fcfcfc; transition:0.3s; }
.wiz-field input:focus { border-color: var(--control-primary); background:#fff; outline:none; }
.lang-sel-btn { flex: 1; min-width: 200px; padding: 70px 40px; border: 4px solid #f1f5f9; border-radius: 30px; background: #fff; cursor: pointer; display: flex; flex-direction: column; align-items: center; transition: 0.4s; }
.lang-sel-btn:hover { border-color: var(--control-primary); transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
.k-dot { width: 15px; height: 15px; border-radius: 50%; background: rgba(255,255,255,0.3); transition: 0.4s; }
.k-dot.active { background: var(--control-accent); transform: scale(1.4); }
.k-btn-primary { background:var(--control-primary); color:#fff; border:none; padding:20px 70px; font-size:1.5rem; border-radius:20px; font-weight:800; cursor:pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
.k-btn-secondary { background:#f8fafc; color:#1e293b; border:2px solid #e2e8f0; padding:20px 70px; font-size:1.5rem; border-radius:20px; font-weight:800; cursor:pointer; }
@media (max-width: 768px) { .wiz-grid { grid-template-columns: 1fr; } .k-btn-primary, .k-btn-secondary { width: 100%; padding: 18px; margin-bottom: 12px; } }
</style>

<script>
const kStrings = <?php echo json_encode($strings); ?>;
let kStep = 1;

function setKLang(lang) {
    jQuery('#k-selected-lang').val(lang);
    const s = kStrings[lang];
    jQuery('#k-header-title').text(s.registration_title);
    jQuery('[data-t]').each(function() {
        const key = jQuery(this).data('t');
        if(s[key]) jQuery(this).text(s[key]);
    });
    jQuery('#kiosk-app-root').css('direction', lang === 'ar' ? 'rtl' : 'ltr');
}

function nextKStep() {
    if (validateStep(kStep)) {
        if (kStep < 4) {
            jQuery(`#k-step-${kStep}`).hide();
            kStep++;
            jQuery(`#k-step-${kStep}`).fadeIn();
            updateKUI();
        }
    }
}

function prevKStep() {
    if (kStep > 1) {
        jQuery(`#k-step-${kStep}`).hide();
        kStep--;
        jQuery(`#k-step-${kStep}`).fadeIn();
        updateKUI();
    }
}

function updateKUI() {
    const s = kStrings[jQuery('#k-selected-lang').val()];
    jQuery('#k-prev').toggle(kStep > 1).text(s.prev);
    jQuery('#k-next').toggle(kStep < 4).text(s.next);
    jQuery('#k-submit').toggle(kStep === 4).text(s.save);
    jQuery('.k-dot').removeClass('active');
    jQuery(`.k-dot[data-step="${kStep}"]`).addClass('active');
}

function validateStep(step) {
    let valid = true;
    jQuery(`#k-step-${step} [required]`).each(function() {
        if (!jQuery(this).val()) { jQuery(this).css('border-color', '#ef4444'); valid = false; }
        else { jQuery(this).css('border-color', ''); }
    });
    return valid;
}

jQuery(document).ready(function($) {
    $('.k-name-part').on('input', function() {
        $('#k-full-name-concat').val(`${$('[name="name_first"]').val()} ${$('[name="name_second"]').val()} ${$('[name="name_third"]').val()} ${$('[name="name_last"]').val()}`.trim());
    });

    $('[name="has_meds"]').on('change', function() {
        $('#k-meds-details').css('display', $(this).val() === 'yes' ? 'flex' : 'none');
    });

    $('#kiosk-form').on('submit', function(e) {
        e.preventDefault();
        if (typeof control_ajax === 'undefined') {
            alert('Error: System variables not loaded. Please refresh the page.');
            return;
        }
        const $btn = $('#k-submit'); $btn.prop('disabled', true).text('...');
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_submit_kiosk_registration&nonce=' + control_ajax.nonce, (res) => {
            if (res.success) { $('#kiosk-form, #k-header').hide(); $('#kiosk-success').fadeIn(); }
            else { alert(res.data || 'Error saving data.'); $btn.prop('disabled', false).text(kStrings[$('#k-selected-lang').val()].save); }
        }).fail(function() {
            alert('Network error. Please check your connection.');
            $btn.prop('disabled', false).text(kStrings[$('#k-selected-lang').val()].save);
        });
    });
});
</script>
