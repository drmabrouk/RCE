<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Reuse the countries list
$countries_list = array(
    'AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa',
    'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria',
    'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados',
    'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda',
    'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island',
    'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso',
    'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China',
    'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo',
    'CD' => 'Congo, Democratic Republic of the', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => 'Cote d\'Ivoire', 'HR' => 'Croatia',
    'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti',
    'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)',
    'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana',
    'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia',
    'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland',
    'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey',
    'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island and McDonald Islands',
    'VA' => 'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland',
    'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic of', 'IQ' => 'Iraq', 'IE' => 'Ireland',
    'IM' => 'Isle of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan',
    'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati',
    'KP' => 'Korea, Democratic People\'s Republic of', 'KR' => 'Korea, Republic of', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Lao People\'s Democratic Republic',
    'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya',
    'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia, The former Yugoslav Republic of',
    'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali',
    'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius',
    'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States of', 'MD' => 'Moldova, Republic of', 'MC' => 'Monaco',
    'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique',
    'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands',
    'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger',
    'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway',
    'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama',
    'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn',
    'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion',
    'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena',
    'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre and Miquelon', 'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal',
    'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia',
    'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia and the South Sandwich Islands',
    'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen',
    'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan, Province of China',
    'TJ' => 'Tajikistan', 'TZ' => 'Tanzania, United Republic of', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo',
    'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey',
    'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Minor Outlying Islands', 'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands, British',
    'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis and Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
);
?>

<div id="kiosk-app-root" style="direction:rtl; font-family:'Rubik', sans-serif; min-height:100vh; background:#f1f5f9; padding:20px;">
    <div style="max-width:800px; margin: 0 auto; background:#fff; border-radius:30px; box-shadow:0 20px 50px rgba(0,0,0,0.1); overflow:hidden;">

        <!-- Language Selection Overlay -->
        <div id="k-lang-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; background:#fff; z-index:100; display:flex; flex-direction:column; align-items:center; justify-content:center; border-radius:30px;">
            <h2 style="margin-bottom:30px; color:var(--control-primary); font-weight:800;"><?php echo Control_I18n::t('select_lang'); ?></h2>
            <div style="display:flex; gap:30px;">
                <button onclick="setKLang('ar')" class="lang-sel-btn">
                    <span style="font-size:3rem; margin-bottom:10px;">🇸🇦</span>
                    <span style="font-weight:800;">العربية</span>
                </button>
                <button onclick="setKLang('en')" class="lang-sel-btn">
                    <span style="font-size:3rem; margin-bottom:10px;">🇺🇸</span>
                    <span style="font-weight:800;">English</span>
                </button>
            </div>
        </div>

        <!-- Welcome Screen -->
        <div id="kiosk-welcome" class="kiosk-screen" style="display:none; padding:60px 40px; text-align:center;">
            <div style="width:120px; height:120px; background:var(--control-primary); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; color:#fff;">
                <span class="dashicons dashicons-welcome-learn-more" style="font-size:60px; width:60px; height:60px;"></span>
            </div>
            <h1 style="font-size:2.5rem; font-weight:800; color:var(--control-primary);"><?php _e('أهلاً بك في المركز', 'control'); ?></h1>
            <p style="font-size:1.2rem; color:var(--control-muted); margin-bottom:40px;"><?php _e('يرجى تسجيل بيانات طفلك للبدء في إجراءات الاستقبال.', 'control'); ?></p>
            <button onclick="startKiosk()" class="control-btn" style="padding:20px 60px; font-size:1.4rem; border-radius:50px; background:var(--control-accent); color:var(--control-primary); border:none; font-weight:800; cursor:pointer; box-shadow:0 10px 20px rgba(212,175,55,0.3);"><?php _e('ابدأ التسجيل الآن', 'control'); ?></button>
        </div>

        <!-- Multi-step Form -->
        <form id="kiosk-form" class="kiosk-screen" style="display:none; padding:40px;">
            <input type="hidden" name="k_lang" id="k-selected-lang" value="ar">
            <div id="kiosk-progress" style="display:flex; gap:10px; margin-bottom:40px;">
                <div class="k-p-step active" data-step="1"></div>
                <div class="k-p-step" data-step="2"></div>
                <div class="k-p-step" data-step="3"></div>
            </div>

            <div id="k-step-1" class="k-step-content">
                <h2 style="margin-bottom:30px;" data-t="basic_info"><?php echo Control_I18n::t('basic_info'); ?></h2>
                <div class="wiz-grid">
                    <div class="wiz-field">
                        <label data-t="child_name"><?php echo Control_I18n::t('child_name'); ?> *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="guardian_name"><?php echo Control_I18n::t('guardian_name'); ?> *</label>
                        <input type="text" name="guardian_name" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="dob"><?php echo Control_I18n::t('dob'); ?> *</label>
                        <input type="date" name="dob" id="k-dob-input" required>
                    </div>
                    <div class="wiz-field">
                        <label data-t="age"><?php echo Control_I18n::t('age'); ?></label>
                        <div id="k-age-display" style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:12px; font-weight:700;">---</div>
                    </div>
                </div>
                <div class="wiz-grid-3" style="margin-top:20px;">
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
                    <select name="nationality">
                        <?php foreach($countries_list as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php selected($code, 'EG'); ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="k-step-2" class="k-step-content" style="display:none;">
                <h2 style="margin-bottom:30px;"><?php _e('بيانات ولي الأمر والتواصل', 'control'); ?></h2>
                <div class="control-form-group large">
                    <input type="text" name="guardian_name" required placeholder=" ">
                    <label><?php _e('اسم ولي الأمر الكامل', 'control'); ?></label>
                </div>
                <div class="control-form-group large">
                    <input type="tel" name="father_phone" required placeholder=" ">
                    <label><?php _e('رقم الهاتف للتواصل', 'control'); ?></label>
                </div>
                <div class="control-form-group large">
                    <input type="email" name="email" placeholder=" ">
                    <label><?php _e('البريد الإلكتروني (اختياري)', 'control'); ?></label>
                </div>
            </div>

            <div id="k-step-3" class="k-step-content" style="display:none;">
                <h2 style="margin-bottom:30px;"><?php _e('سبب الزيارة والملاحظات', 'control'); ?></h2>
                <div class="control-form-group large">
                    <textarea name="intake_reason" rows="4" required placeholder=" "></textarea>
                    <label><?php _e('ما هو سبب زيارتكم للمركز اليوم؟', 'control'); ?></label>
                </div>
                <div class="control-form-group large">
                    <textarea name="intake_notes" rows="4" placeholder=" "></textarea>
                    <label><?php _e('هل هناك أي ملاحظات إضافية ترغبون في ذكرها؟', 'control'); ?></label>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:50px;">
                <button type="button" id="k-prev" onclick="prevKStep()" class="k-btn-secondary" style="display:none;"><?php _e('السابق', 'control'); ?></button>
                <div style="flex:1;"></div>
                <button type="button" id="k-next" onclick="nextKStep()" class="k-btn-primary"><?php _e('التالي', 'control'); ?></button>
                <button type="submit" id="k-submit" style="display:none;" class="k-btn-primary"><?php _e('إرسال الطلب الآن', 'control'); ?></button>
            </div>
        </form>

        <!-- Success Screen -->
        <div id="kiosk-success" class="kiosk-screen" style="display:none; padding:80px 40px; text-align:center;">
            <div style="width:100px; height:100px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; color:#10b981;">
                <span class="dashicons dashicons-yes-alt" style="font-size:60px; width:60px; height:60px;"></span>
            </div>
            <h1 style="font-weight:800; color:#065f46;"><?php _e('تم استلام طلبكم بنجاح', 'control'); ?></h1>
            <p style="font-size:1.2rem; color:var(--control-muted); margin-bottom:40px;"><?php _e('شكراً لكم. يرجى الانتظار، سيقوم موظف الاستقبال بمناداتكم قريباً.', 'control'); ?></p>
            <button onclick="resetKiosk()" class="control-btn" style="background:var(--control-bg); color:var(--control-text-dark) !important; border:1px solid var(--control-border); padding:15px 40px; border-radius:12px; font-weight:700;"><?php _e('عودة للرئيسية', 'control'); ?></button>
        </div>

    </div>
</div>

<style>
.wiz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.wiz-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
.wiz-field { margin-bottom: 20px; text-align: right; }
.wiz-field label { display: block; font-size: 0.9rem; font-weight: 800; color: #64748b; margin-bottom: 8px; }
.wiz-field input, .wiz-field select, .wiz-field textarea { width: 100%; padding: 15px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1.1rem; }

.lang-sel-btn { flex: 1; min-width: 150px; padding: 40px 20px; border: 2px solid #f1f5f9; border-radius: 20px; background: #fff; cursor: pointer; display: flex; flex-direction: column; align-items: center; transition: 0.3s; }
.lang-sel-btn:hover { border-color: var(--control-primary); background: #f8fafc; transform: translateY(-5px); }

.k-p-step { flex:1; height:8px; background:#e2e8f0; border-radius:4px; transition:0.3s; }
.k-p-step.active { background:var(--control-accent); }

.k-btn-primary { background:var(--control-primary); color:#fff; border:none; padding:15px 50px; font-size:1.3rem; border-radius:15px; font-weight:800; cursor:pointer; }
.k-btn-secondary { background:#f8fafc; color:var(--control-text-dark); border:2px solid #e2e8f0; padding:15px 50px; font-size:1.3rem; border-radius:15px; font-weight:800; cursor:pointer; }
</style>

<script>
const kStrings = <?php echo json_encode(Control_I18n::get_all()); ?>;
let kStep = 1;

function setKLang(lang) {
    jQuery('#k-selected-lang').val(lang);
    jQuery('#k-lang-overlay').fadeOut();
    jQuery('#kiosk-welcome').fadeIn();

    // UI Update
    const s = kStrings[lang];
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

function calculateDetailedAge(dobString) {
    if(!dobString) return '---';
    const dob = new Date(dobString);
    const now = new Date();
    let years = now.getFullYear() - dob.getFullYear();
    let months = now.getMonth() - dob.getMonth();
    let days = now.getDate() - dob.getDate();
    if (days < 0) { months--; const lastMonth = new Date(now.getFullYear(), now.getMonth(), 0); days += lastMonth.getDate(); }
    if (months < 0) { years--; months += 12; }
    const lang = jQuery('#k-selected-lang').val();
    const s = kStrings[lang];
    return `${years} ${s.years}, ${months} ${s.months}, ${days} ${s.days}`;
}

function startKiosk() {
    jQuery('#kiosk-welcome').hide();
    jQuery('#kiosk-form').fadeIn();
}

function nextKStep() {
    const $ = jQuery;
    if (validateKStep(kStep)) {
        $(`#k-step-${kStep}`).hide();
        kStep++;
        $(`#k-step-${kStep}`).fadeIn();
        updateKUI();
    }
}

function prevKStep() {
    const $ = jQuery;
    $(`#k-step-${kStep}`).hide();
    kStep--;
    $(`#k-step-${kStep}`).fadeIn();
    updateKUI();
}

function updateKUI() {
    const $ = jQuery;
    $('#k-prev').toggle(kStep > 1);
    $('#k-next').toggle(kStep < 3);
    $('#k-submit').toggle(kStep === 3);
    $('.k-p-step').removeClass('active');
    $(`.k-p-step[data-step="${kStep}"]`).addClass('active');
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
    $('#k-dob-input').on('change', function() {
        const ageStr = calculateDetailedAge($(this).val());
        const lang = $('#k-selected-lang').val();
        $('#k-age-display').text(ageStr);
    });

    $('#kiosk-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#k-submit');
        $btn.prop('disabled', true).text('<?php _e("جاري الإرسال...", "control"); ?>');

        const formData = $(this).serialize() + '&action=control_submit_kiosk_registration&nonce=<?php echo wp_create_nonce("control_nonce"); ?>';
        $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(res) {
            if (res.success) {
                $('#kiosk-form').hide();
                $('#kiosk-success').fadeIn();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('<?php _e("إرسال الطلب الآن", "control"); ?>');
            }
        });
    });
});
</script>
