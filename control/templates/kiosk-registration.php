<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$strings = Control_I18n::get_all();
?>

<div id="kiosk-app-root" style="direction:rtl; font-family:'Rubik', sans-serif; min-height:100vh; background:#f1f5f9; padding:20px; position:relative; overflow:hidden;">
    <div style="max-width:900px; margin: 0 auto; background:#fff; border-radius:30px; box-shadow:0 25px 60px rgba(0,0,0,0.1); overflow:hidden; position:relative; min-height:600px;">

        <!-- Language Selection Overlay -->
        <div id="k-lang-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background:#fff; z-index:100; display:flex; flex-direction:column; align-items:center; justify-content:center; border-radius:30px;">
            <h2 style="margin-bottom:30px; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('select_lang'); ?></h2>
            <div style="display:flex; gap:30px;">
                <button onclick="setKLang('ar')" class="lang-sel-btn">
                    <span style="font-size:3.5rem; margin-bottom:15px;">🇸🇦</span>
                    <span style="font-weight:800; font-size:1.2rem;"><?php echo $strings['ar']['lang_ar']; ?></span>
                </button>
                <button onclick="setKLang('en')" class="lang-sel-btn">
                    <span style="font-size:3.5rem; margin-bottom:15px;">🇺🇸</span>
                    <span style="font-weight:800; font-size:1.2rem;"><?php echo $strings['en']['lang_en']; ?></span>
                </button>
            </div>
        </div>

        <!-- Welcome Screen -->
        <div id="kiosk-welcome" class="kiosk-screen" style="display:none; padding:80px 40px; text-align:center;">
            <div style="width:140px; height:140px; background:var(--control-primary); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 40px; color:#fff; box-shadow:0 15px 35px rgba(0,0,0,0.1);">
                <span class="dashicons dashicons-welcome-learn-more" style="font-size:70px; width:70px; height:70px;"></span>
            </div>
            <h1 id="k-welcome-title" style="font-size:2.8rem; font-weight:800; color:var(--control-primary); margin-bottom:20px;" data-t="welcome_title"><?php echo Control_I18n::t('welcome_title'); ?></h1>
            <p id="k-welcome-desc" style="font-size:1.3rem; color:var(--control-muted); margin-bottom:50px;" data-t="welcome_desc"><?php echo Control_I18n::t('welcome_desc'); ?></p>
            <button onclick="startKiosk()" class="control-btn" style="padding:22px 80px; font-size:1.5rem; border-radius:50px; background:var(--control-accent); color:var(--control-primary); border:none; font-weight:900; cursor:pointer; box-shadow:0 15px 30px rgba(212,175,55,0.4);" data-t="start_btn"><?php echo Control_I18n::t('start_btn'); ?></button>
        </div>

        <!-- Progress Header (Visible during steps) -->
        <div id="k-header" style="display:none; background:var(--control-primary); padding:30px 40px; color:#fff; text-align:center;">
            <h3 id="k-header-title" style="margin:0; font-size:1.4rem; color:#fff; font-weight:800;"><?php echo Control_I18n::t('registration_title'); ?></h3>
            <div style="display:flex; justify-content:center; gap:12px; margin-top:20px;">
                <div class="k-dot active" data-step="1"></div>
                <div class="k-dot" data-step="2"></div>
                <div class="k-dot" data-step="3"></div>
                <div class="k-dot" data-step="4"></div>
            </div>
        </div>

        <form id="kiosk-form" class="kiosk-screen" style="display:none; padding:40px; min-height:500px; overflow-y:auto;">
            <input type="hidden" name="k_lang" id="k-selected-lang" value="ar">
            <input type="hidden" name="full_name" id="k-full-name-concat">
            <input type="hidden" name="intake_status" value="pending">

            <!-- Phase 1: Identification -->
            <div id="k-step-1" class="k-step-content">
                <h4 style="color:var(--control-primary); margin-bottom:25px; font-weight:800;" data-t="phase_1_title"><?php echo Control_I18n::t('phase_1_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="first_name"><?php echo Control_I18n::t('first_name'); ?> *</label>
                        <input type="text" name="name_first" required class="k-name-part">
                    </div>
                    <div class="wiz-field">
                        <label data-t="second_name"><?php echo Control_I18n::t('second_name'); ?> *</label>
                        <input type="text" name="name_second" required class="k-name-part">
                    </div>
                    <div class="wiz-field">
                        <label data-t="third_name"><?php echo Control_I18n::t('third_name'); ?> *</label>
                        <input type="text" name="name_third" required class="k-name-part">
                    </div>
                    <div class="wiz-field">
                        <label data-t="last_name"><?php echo Control_I18n::t('last_name'); ?> *</label>
                        <input type="text" name="name_last" required class="k-name-part">
                    </div>
                    <div class="wiz-field">
                        <label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label>
                        <input type="date" name="dob" id="k-dob-input" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="gender"><?php echo Control_I18n::t('gender'); ?></label>
                        <select name="gender">
                            <option value="male" data-t="male"><?php echo Control_I18n::t('male'); ?></option>
                            <option value="female" data-t="female"><?php echo Control_I18n::t('female'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Phase 2: Guardian Info -->
            <div id="k-step-2" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:25px; font-weight:800;" data-t="phase_2_title"><?php echo Control_I18n::t('phase_2_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label>
                        <input type="text" name="guardian_name" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="father_phone"><?php echo Control_I18n::t('father_phone'); ?> *</label>
                        <input type="tel" name="father_phone" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="mother_phone"><?php echo Control_I18n::t('mother_phone'); ?></label>
                        <input type="tel" name="mother_phone">
                    </div>
                    <div class="wiz-field">
                        <label data-t="email"><?php echo Control_I18n::t('email'); ?></label>
                        <input type="email" name="email">
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="address"><?php echo Control_I18n::t('address'); ?></label>
                    <input type="text" name="address">
                </div>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="emergency_contact"><?php echo Control_I18n::t('emergency_contact'); ?></label>
                        <input type="text" name="emergency_contact">
                    </div>
                    <div class="wiz-field">
                        <label data-t="blood_type"><?php echo Control_I18n::t('blood_type'); ?></label>
                        <select name="blood_type">
                            <option value="">-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Phase 3: Medical Screening -->
            <div id="k-step-3" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:25px; font-weight:800;" data-t="phase_3_title"><?php echo Control_I18n::t('phase_3_title'); ?></h4>
                <div class="wiz-grid">
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
                        <label data-t="walking"><?php echo Control_I18n::t('walking'); ?></label>
                        <input type="text" name="milestones_walking">
                    </div>
                    <div class="wiz-field">
                        <label data-t="speaking"><?php echo Control_I18n::t('speaking'); ?></label>
                        <input type="text" name="milestones_speaking">
                    </div>
                    <div class="wiz-field">
                        <label data-t="sitting"><?php echo Control_I18n::t('sitting'); ?></label>
                        <input type="text" name="milestones_sitting">
                    </div>
                </div>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="chronic_conditions"><?php echo Control_I18n::t('chronic_conditions'); ?></label>
                        <textarea name="chronic_conditions" rows="2"></textarea>
                    </div>
                    <div class="wiz-field">
                        <label data-t="medications"><?php echo Control_I18n::t('medications'); ?></label>
                        <textarea name="current_medications" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Phase 4: Functional/Intake -->
            <div id="k-step-4" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:25px; font-weight:800;" data-t="phase_4_title"><?php echo Control_I18n::t('phase_4_title'); ?></h4>
                <div class="wiz-field">
                    <label data-t="intake_desc"><?php echo Control_I18n::t('intake_desc'); ?></label>
                    <textarea name="intake_reason" rows="3" required></textarea>
                </div>
                <div class="wiz-field">
                    <label data-t="behavioral_observation"><?php echo Control_I18n::t('behavioral_observation'); ?></label>
                    <textarea name="initial_behavioral_observation" rows="3" placeholder="Eye contact, simple instructions..."></textarea>
                </div>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="drug_allergies"><?php echo Control_I18n::t('drug_allergies'); ?></label>
                        <input type="text" name="drug_allergies">
                    </div>
                    <div class="wiz-field">
                        <label data-t="initial_diagnosis"><?php echo Control_I18n::t('initial_diagnosis'); ?> (<?php echo $strings['ar']['lang_ar'] === 'العربية' ? 'إن وجد' : 'if any'; ?>)</label>
                        <input type="text" name="initial_diagnosis">
                    </div>
                </div>
                <div style="background:#fefce8; border:1px solid #fef08a; padding:15px; border-radius:15px; color:#854d0e; font-size:0.85rem;" data-t="screening_desc">
                    ⚠️ <?php echo Control_I18n::t('screening_desc'); ?>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:50px; padding-bottom:20px;">
                <button type="button" id="k-prev" onclick="prevKStep()" class="k-btn-secondary" style="display:none;"><?php echo Control_I18n::t('prev'); ?></button>
                <div style="flex:1;"></div>
                <button type="button" id="k-next" onclick="nextKStep()" class="k-btn-primary"><?php echo Control_I18n::t('next'); ?></button>
                <button type="submit" id="k-submit" style="display:none;" class="k-btn-primary"><?php echo Control_I18n::t('save'); ?></button>
            </div>
        </form>

        <!-- Success Screen -->
        <div id="kiosk-success" class="kiosk-screen" style="display:none; padding:100px 40px; text-align:center;">
            <div style="width:120px; height:120px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 40px; color:#10b981; box-shadow:0 15px 35px rgba(16,185,129,0.1);">
                <span class="dashicons dashicons-yes-alt" style="font-size:80px; width:80px; height:80px;"></span>
            </div>
            <h1 style="font-weight:800; color:#065f46; font-size:2.5rem;" data-t="success_title"><?php echo Control_I18n::t('success_title'); ?></h1>
            <p style="font-size:1.4rem; color:var(--control-muted); margin-bottom:50px; max-width:600px; margin-left:auto; margin-right:auto;" data-t="success_desc"><?php echo Control_I18n::t('success_desc'); ?></p>
            <button onclick="resetKiosk()" class="control-btn" style="background:var(--control-bg); color:var(--control-text-dark) !important; border:1px solid var(--control-border); padding:20px 60px; border-radius:15px; font-weight:800; font-size:1.1rem;" data-t="finish_btn"><?php echo Control_I18n::t('finish_btn'); ?></button>
        </div>

    </div>
</div>

<style>
.wiz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
.wiz-field { margin-bottom: 25px; }
.wiz-field label { display: block; font-size: 0.95rem; font-weight: 800; color: #475569; margin-bottom: 10px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 18px; border-radius: 15px; border: 2.5px solid #e2e8f0; font-size: 1.15rem; background:#fcfcfc; transition:0.3s; }
.wiz-field input:focus { border-color: var(--control-primary); background:#fff; outline:none; }

.lang-sel-btn { flex: 1; min-width: 180px; padding: 60px 30px; border: 3px solid #f1f5f9; border-radius: 25px; background: #fff; cursor: pointer; display: flex; flex-direction: column; align-items: center; transition: 0.4s; }
.lang-sel-btn:hover { border-color: var(--control-primary); background: #f8fafc; transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }

.k-dot { width: 15px; height: 15px; border-radius: 50%; background: rgba(255,255,255,0.3); transition: 0.4s; }
.k-dot.active { background: var(--control-accent); transform: scale(1.3); }

.k-btn-primary { background:var(--control-primary); color:#fff; border:none; padding:18px 60px; font-size:1.4rem; border-radius:18px; font-weight:800; cursor:pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.15); transition:0.3s; }
.k-btn-primary:hover { transform:translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
.k-btn-secondary { background:#f8fafc; color:var(--control-text-dark); border:2px solid #e2e8f0; padding:18px 60px; font-size:1.4rem; border-radius:18px; font-weight:800; cursor:pointer; transition:0.3s; }

@media (max-width: 768px) {
    .wiz-grid { grid-template-columns: 1fr; }
    #kiosk-app-root { padding: 10px; }
    .k-btn-primary, .k-btn-secondary { width: 100%; padding: 15px; margin-bottom: 10px; }
}
</style>

<script>
const kStrings = <?php echo json_encode($strings); ?>;
let kStep = 1;
const totalKSteps = 4;

function setKLang(lang) {
    jQuery('#k-selected-lang').val(lang);
    jQuery('#k-lang-overlay').fadeOut();
    jQuery('#kiosk-welcome').fadeIn();

    const s = kStrings[lang];
    jQuery('#k-header-title').text(s.registration_title);

    jQuery('[data-t]').each(function() {
        const key = jQuery(this).data('t');
        if(s[key]) jQuery(this).text(s[key]);
    });

    if(lang === 'ar') {
        jQuery('#kiosk-app-root').css('direction', 'rtl');
        jQuery('.wiz-field').css('text-align', 'right');
    } else {
        jQuery('#kiosk-app-root').css('direction', 'ltr');
        jQuery('.wiz-field').css('text-align', 'left');
    }
}

function startKiosk() {
    jQuery('#kiosk-welcome').hide();
    jQuery('#k-header').show();
    jQuery('#kiosk-form').fadeIn();
}

function nextKStep() {
    if (validateKStep(kStep)) {
        if (kStep < totalKSteps) {
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
    const $ = jQuery;
    const lang = $('#k-selected-lang').val();
    const s = kStrings[lang];

    $('#k-prev').toggle(kStep > 1).text(s.prev);
    $('#k-next').toggle(kStep < totalKSteps).text(s.next);
    $('#k-submit').toggle(kStep === totalKSteps).text(s.save);

    $('.k-dot').removeClass('active');
    $(`.k-dot[data-step="${kStep}"]`).addClass('active');
}

function validateKStep(step) {
    let valid = true;
    jQuery(`#k-step-${step} [required]`).each(function() {
        if (!jQuery(this).val()) {
            jQuery(this).css('border-color', '#ef4444');
            valid = false;
        } else {
            jQuery(this).css('border-color', '');
        }
    });
    return valid;
}

function resetKiosk() {
    location.reload();
}

jQuery(document).ready(function($) {
    // 4-Part Name Concat (Public)
    $('.k-name-part').on('input', function() {
        const f = $('[name="name_first"]').val().trim();
        const s = $('[name="name_second"]').val().trim();
        const t = $('[name="name_third"]').val().trim();
        const l = $('[name="name_last"]').val().trim();
        $('#k-full-name-concat').val(`${f} ${s} ${t} ${l}`.trim());
    });

    $('#kiosk-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#k-submit');
        $btn.prop('disabled', true).text('Processing...');

        const formData = $(this).serialize() + '&action=control_submit_kiosk_registration&nonce=<?php echo wp_create_nonce("control_nonce"); ?>';
        $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(res) {
            if (res.success) {
                $('#k-header').hide();
                $('#kiosk-form').hide();
                $('#kiosk-success').fadeIn();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('Try Again');
            }
        });
    });
});
</script>
