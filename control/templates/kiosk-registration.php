<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$strings = Control_I18n::get_all();
?>

<div id="kiosk-app-root" style="direction:rtl; font-family:'Rubik', sans-serif; height:100vh; background:#f1f5f9; padding:0; display:flex; align-items:center; justify-content:center; box-sizing:border-box; overflow: hidden;">
    <div style="max-width:1000px; width:100%; height:100%; background:#fff; overflow:hidden; position:relative; display:flex; flex-direction:column;">

        <!-- Compact Progress Header -->
        <div id="k-header" style="background:var(--control-primary); padding:25px 30px; color:#fff; text-align:center; position:relative;">
            <div style="position:absolute; top:15px; left:20px; display:flex; gap:8px;">
                <button type="button" onclick="setKLang('ar')" class="lang-pill" id="btn-ar">AR</button>
                <button type="button" onclick="setKLang('en')" class="lang-pill" id="btn-en">EN</button>
            </div>
            <h3 id="k-header-title" style="margin:0; font-size:1.25rem; color:#fff; font-weight:800; letter-spacing:-0.5px;"><?php echo Control_I18n::t('registration_title'); ?></h3>
            <div style="display:flex; justify-content:center; gap:8px; margin-top:15px;">
                <div class="k-dot active" data-step="1"></div>
                <div class="k-dot" data-step="2"></div>
                <div class="k-dot" data-step="3"></div>
                <div class="k-dot" data-step="4"></div>
            </div>
        </div>

        <form id="kiosk-form" style="padding:25px 40px; flex: 1; overflow-y: auto; margin-bottom: 80px;">
            <input type="hidden" name="k_lang" id="k-selected-lang" value="ar">
            <input type="hidden" name="full_name" id="k-full-name-concat">

            <!-- Phase 1: Identity -->
            <div id="k-step-1" class="k-step-content">
                <h4 style="color:var(--control-primary); margin-bottom:20px; font-weight:800; border-bottom:2px solid #f1f5f9; padding-bottom:10px;" data-t="phase_1_title"><?php echo Control_I18n::t('phase_1_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-grid-3" style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div class="wiz-field"><label data-t="first_name"><?php echo Control_I18n::t('first_name'); ?> *</label><input type="text" name="name_first" required class="k-name-part" data-p="first_name"></div>
                        <div class="wiz-field"><label data-t="second_name"><?php echo Control_I18n::t('second_name'); ?> *</label><input type="text" name="name_second" required class="k-name-part" data-p="second_name"></div>
                        <div class="wiz-field"><label data-t="last_name"><?php echo Control_I18n::t('last_name'); ?> *</label><input type="text" name="name_last" required class="k-name-part" data-p="last_name"></div>
                    </div>
                    <div class="wiz-field"><label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label><input type="date" name="dob" required></div>
                    <div class="wiz-field"><label data-t="gender"><?php echo Control_I18n::t('gender'); ?></label>
                        <select name="gender" data-source="gender"></select>
                    </div>
                    <div class="wiz-field"><label data-t="nationality"><?php echo Control_I18n::t('nationality'); ?></label>
                        <select name="nationality" data-source="nationality"></select>
                    </div>
                    <div class="wiz-field"><label data-t="country_residence"><?php echo Control_I18n::t('country_residence'); ?></label><input type="text" name="country_residence" data-p="country_residence"></div>
                    <div class="wiz-field"><label data-t="city_residence"><?php echo Control_I18n::t('city_residence'); ?></label><input type="text" name="city_residence" data-p="city_residence"></div>
                    <div class="wiz-field"><label data-t="national_id"><?php echo Control_I18n::t('national_id'); ?></label><input type="text" name="national_id" data-p="national_id"></div>
                </div>
            </div>

            <!-- Phase 2: Guardian -->
            <div id="k-step-2" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:20px; font-weight:800; border-bottom:2px solid #f1f5f9; padding-bottom:10px;" data-t="phase_2_title"><?php echo Control_I18n::t('phase_2_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label><input type="text" name="guardian_name" required data-p="guardian_name"></div>
                    <div class="wiz-field"><label data-t="relationship"><?php echo Control_I18n::t('relationship'); ?></label>
                        <select name="guardian_relationship" data-source="relationship"></select>
                    </div>
                    <div class="wiz-field"><label data-t="father_phone"><?php echo Control_I18n::t('father_phone'); ?> *</label><input type="tel" name="father_phone" required class="k-numeric" data-p="phone"></div>
                    <div class="wiz-field"><label data-t="email"><?php echo Control_I18n::t('email'); ?></label><input type="email" name="email" data-p="email"></div>
                    <div class="wiz-field"><label data-t="emergency_contact"><?php echo Control_I18n::t('emergency_contact'); ?></label><input type="text" name="emergency_contact" data-p="emergency_contact"></div>
                    <div class="wiz-field"><label data-t="blood_type"><?php echo Control_I18n::t('blood_type'); ?></label>
                        <select name="blood_type" data-source="blood_type"></select>
                    </div>
                </div>
                <div class="wiz-field"><label data-t="address"><?php echo Control_I18n::t('address'); ?></label><input type="text" name="address" data-p="address"></div>
            </div>

            <!-- Phase 3: Medical Screening -->
            <div id="k-step-3" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:20px; font-weight:800; border-bottom:2px solid #f1f5f9; padding-bottom:10px;" data-t="phase_3_title"><?php echo Control_I18n::t('phase_3_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="diag_prev"><?php echo Control_I18n::t('diag_prev'); ?></label>
                        <select name="diag_prev" data-source="boolean"></select>
                    </div>
                    <div class="wiz-field"><label data-t="prev_rehab"><?php echo Control_I18n::t('prev_rehab'); ?></label>
                        <select name="prev_rehab_centers" data-source="boolean"></select>
                    </div>
                    <div class="wiz-field"><label data-t="motor_delay"><?php echo Control_I18n::t('motor_delay'); ?></label>
                        <select name="motor_delay" data-source="boolean"></select>
                    </div>
                    <div class="wiz-field"><label data-t="speech_delay"><?php echo Control_I18n::t('speech_delay'); ?></label>
                        <select name="speech_delay" data-source="boolean"></select>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="chronic_conditions"><?php echo Control_I18n::t('chronic_conditions'); ?></label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0;">
                        <label style="font-size:0.85rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="epilepsy"> <span data-t="epilepsy"><?php echo Control_I18n::t('epilepsy'); ?></span></label>
                        <label style="font-size:0.85rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="diabetes"> <span data-t="diabetes"><?php echo Control_I18n::t('diabetes'); ?></span></label>
                        <label style="font-size:0.85rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="hearing"> <span data-t="hearing_issues"><?php echo Control_I18n::t('hearing_issues'); ?></span></label>
                        <label style="font-size:0.85rem; display:flex; align-items:center; gap:10px;"><input type="checkbox" name="chronic_conditions[]" value="vision"> <span data-t="vision_issues"><?php echo Control_I18n::t('vision_issues'); ?></span></label>
                    </div>
                </div>
                <div class="wiz-field"><label data-t="medications"><?php echo Control_I18n::t('medications'); ?></label><input type="text" name="current_medications" data-p="medications"></div>
            </div>

            <!-- Phase 4: Functional Screening -->
            <div id="k-step-4" class="k-step-content" style="display:none;">
                <h4 style="color:var(--control-primary); margin-bottom:20px; font-weight:800; border-bottom:2px solid #f1f5f9; padding-bottom:10px;" data-t="phase_4_title"><?php echo Control_I18n::t('phase_4_title'); ?></h4>
                <div class="wiz-grid">
                    <div class="wiz-field"><label data-t="eval_social"><?php echo Control_I18n::t('eval_social'); ?></label>
                        <select name="eval_social" data-source="social"></select>
                    </div>
                    <div class="wiz-field"><label data-t="eval_language"><?php echo Control_I18n::t('eval_language'); ?></label>
                        <select name="eval_language" data-source="language"></select>
                    </div>
                </div>
                <div class="wiz-field">
                    <label data-t="intake_desc"><?php echo Control_I18n::t('intake_desc'); ?> *</label>
                    <textarea name="intake_reason" rows="3" required data-p="intake_desc"></textarea>
                </div>
                <div style="background:#fefce8; border:1px solid #fef08a; padding:15px; border-radius:12px; color:#854d0e; font-size:0.85rem;" data-t="screening_desc">⚠️ <?php echo Control_I18n::t('screening_desc'); ?></div>
            </div>

            <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: #fff; padding: 20px 40px; border-top: 1px solid #f1f5f9; display:flex; justify-content:space-between; gap:15px; box-sizing: border-box;">
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
.wiz-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
.wiz-field { margin-bottom: 15px; text-align: inherit; }
.wiz-field label { display: block; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 6px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 0.95rem; background:#fff; transition:0.2s; color: #1e293b; font-family: inherit; }
.wiz-field input:focus, .wiz-field select:focus, .wiz-field textarea:focus { border-color: var(--control-primary); outline:none; box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.05); }
.lang-pill { background:rgba(255,255,255,0.1); border:1.5px solid rgba(255,255,255,0.3); color:#fff; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:0.75rem; font-weight:bold; transition: 0.3s; }
.lang-pill:hover { background: rgba(255,255,255,0.2); }
.lang-pill.active { background: #fff; color: var(--control-primary); border-color: #fff; }
.k-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.2); transition: 0.4s; }
.k-dot.active { background: var(--control-accent); transform: scale(1.2); box-shadow: 0 0 10px rgba(212, 175, 55, 0.5); }
.k-btn-primary { background:var(--control-primary); color:#fff; border:none; padding:12px 30px; font-size:1rem; border-radius:12px; font-weight:700; cursor:pointer; transition: 0.3s; }
.k-btn-secondary { background:#f8fafc; color:#1e293b; border:1.5px solid #e2e8f0; padding:12px 30px; font-size:1rem; border-radius:12px; font-weight:700; cursor:pointer; transition: 0.3s; }
.k-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.k-btn-secondary:hover { background: #f1f5f9; }
@media (max-width: 768px) {
    .wiz-grid { grid-template-columns: 1fr; }
    #kiosk-app-root { padding: 10px; }
    #kiosk-form { padding: 20px; }
    .k-btn-primary, .k-btn-secondary { width: 100%; }
}
</style>

<script>
const kStrings = <?php echo json_encode($strings); ?>;
let kStep = 1;

const optionData = {
    gender: {
        ar: [{v:'male', t:'ذكر'}, {v:'female', t:'أنثى'}],
        en: [{v:'male', t:'Male'}, {v:'female', t:'Female'}]
    },
    nationality: {
        ar: [
            {v:'SA', t:'🇸🇦 السعودية'}, {v:'AE', t:'🇦🇪 الإمارات'}, {v:'EG', t:'🇪🇬 مصر'},
            {v:'KW', t:'🇰🇼 الكويت'}, {v:'QA', t:'🇶🇦 قطر'}, {v:'BH', t:'🇧🇭 البحرين'},
            {v:'OM', t:'🇴🇲 عمان'}, {v:'JO', t:'🇯🇴 الأردن'}, {v:'other', t:'أخرى'}
        ],
        en: [
            {v:'SA', t:'🇸🇦 Saudi Arabia'}, {v:'AE', t:'🇦🇪 UAE'}, {v:'EG', t:'🇪🇬 Egypt'},
            {v:'KW', t:'🇰🇼 Kuwait'}, {v:'QA', t:'🇶🇦 Qatar'}, {v:'BH', t:'🇧🇭 Bahrain'},
            {v:'OM', t:'🇴🇲 Oman'}, {v:'JO', t:'🇯🇴 Jordan'}, {v:'other', t:'Other'}
        ]
    },
    relationship: {
        ar: [{v:'father', t:'الأب'}, {v:'mother', t:'الأم'}, {v:'relative', t:'قريب'}, {v:'legal', t:'ولي أمر شرعي'}],
        en: [{v:'father', t:'Father'}, {v:'mother', t:'Mother'}, {v:'relative', t:'Relative'}, {v:'legal', t:'Legal Guardian'}]
    },
    blood_type: {
        ar: [{v:'', t:'-'}, {v:'A+', t:'A+'}, {v:'A-', t:'A-'}, {v:'B+', t:'B+'}, {v:'B-', t:'B-'}, {v:'AB+', t:'AB+'}, {v:'AB-', t:'AB-'}, {v:'O+', t:'O+'}, {v:'O-', t:'O-'}],
        en: [{v:'', t:'-'}, {v:'A+', t:'A+'}, {v:'A-', t:'A-'}, {v:'B+', t:'B+'}, {v:'B-', t:'B-'}, {v:'AB+', t:'AB+'}, {v:'AB-', t:'AB-'}, {v:'O+', t:'O+'}, {v:'O-', t:'O-'}]
    },
    boolean: {
        ar: [{v:'no', t:'لا'}, {v:'yes', t:'نعم'}],
        en: [{v:'no', t:'No'}, {v:'yes', t:'Yes'}]
    },
    social: {
        ar: [{v:'positive', t:'جيد'}, {v:'limited', t:'متوسط'}, {v:'isolated', t:'ضعيف'}],
        en: [{v:'positive', t:'Good'}, {v:'limited', t:'Average'}, {v:'isolated', t:'Poor'}]
    },
    language: {
        ar: [{v:'non_verbal', t:'غير ناطق'}, {v:'words', t:'كلمات'}, {v:'sentences', t:'جمل'}],
        en: [{v:'non_verbal', t:'Non-verbal'}, {v:'words', t:'Words'}, {v:'sentences', t:'Sentences'}]
    }
};

function setKLang(lang) {
    const $ = jQuery;
    $('#k-selected-lang').val(lang);
    $('.lang-pill').removeClass('active');
    $(`#btn-${lang}`).addClass('active');

    const s = kStrings[lang];
    $('#k-header-title').text(s.registration_title);

    // Update Labels
    $('[data-t]').each(function() {
        const key = $(this).data('t');
        if(s[key]) $(this).text(s[key]);
    });

    // Update Placeholders
    $('[data-p]').each(function() {
        const key = $(this).data('p');
        if(s[key]) $(this).attr('placeholder', s[key]);
    });

    // Update Dropdowns
    $('[data-source]').each(function() {
        const source = $(this).data('source');
        const currentVal = $(this).val();
        $(this).empty();
        if(optionData[source] && optionData[source][lang]) {
            optionData[source][lang].forEach(opt => {
                $(this).append($('<option>', { value: opt.v, text: opt.t }));
            });
        }
        $(this).val(currentVal);
    });

    $('#kiosk-app-root').css('direction', lang === 'ar' ? 'rtl' : 'ltr');
    updateKUI();
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
    const lang = jQuery('#k-selected-lang').val();
    const s = kStrings[lang];
    jQuery('#k-prev').toggle(kStep > 1).text(s.prev);
    jQuery('#k-next').toggle(kStep < 4).text(s.next);
    jQuery('#k-submit').toggle(kStep === 4).text(s.save);
    jQuery('.k-dot').removeClass('active');
    jQuery(`.k-dot[data-step="${kStep}"]`).addClass('active');
}

function validateStep(step) {
    let valid = true;
    jQuery(`#k-step-${step} [required]`).each(function() {
        const val = jQuery(this).val();
        if (!val || (jQuery(this).attr('type') === 'email' && !/^\S+@\S+\.\S+$/.test(val))) {
            jQuery(this).css('border-color', '#ef4444');
            valid = false;
        }
        else { jQuery(this).css('border-color', ''); }
    });
    return valid;
}

jQuery(document).ready(function($) {
    // Initialize Defaults
    setKLang('ar');

    $('.k-name-part').on('input', function() {
        $('#k-full-name-concat').val(`${$('[name="name_first"]').val()} ${$('[name="name_second"]').val()} ${$('[name="name_last"]').val()}`.trim());
    });

    $('.k-numeric').on('input', function() {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });

    $('#kiosk-form').on('submit', function(e) {
        e.preventDefault();
        if (typeof control_ajax === 'undefined') {
            alert('Error: System variables not loaded. Please refresh the page.');
            return;
        }

        const lang = $('#k-selected-lang').val();
        const $btn = $('#k-submit');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_submit_kiosk_registration&nonce=' + control_ajax.nonce, (res) => {
            if (res.success) {
                $('#kiosk-form, #k-header').hide();
                $('#kiosk-success').fadeIn();
            } else {
                let msg = 'Error';
                if(typeof res.data === 'string') msg = res.data;
                else if(res.data && res.data.message) msg = res.data.message;
                alert(msg);
                $btn.prop('disabled', false).text(originalText);
            }
        }).fail(function(xhr) {
            alert('Network error: ' + xhr.statusText);
            $btn.prop('disabled', false).text(originalText);
        });
    });
});
</script>
