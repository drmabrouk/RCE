<?php
$specialists = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$wpdb->prefix}control_staff WHERE role IN ('therapist', 'coach', 'specialist')");
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
    'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Karakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati',
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

<div id="patient-wizard-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10005; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card wizard-container" style="width:100%; max-width:900px; padding:0; border-radius:24px; overflow:hidden; box-shadow:0 50px 100px -20px rgba(0,0,0,0.3);">

        <!-- Header & Progress Bar -->
        <div class="wizard-header" style="background:var(--control-primary); color:#fff; padding:25px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <div>
                    <h3 id="wizard-title" style="margin:0; font-size:1.4rem; color:#fff;"><?php _e('تسجيل حالة طفل جديد', 'control'); ?></h3>
                    <div id="wiz-age-badge" style="display:none; background:var(--control-accent); color:var(--control-primary); font-size:0.75rem; padding:2px 10px; border-radius:10px; font-weight:800; margin-top:5px;"></div>
                </div>
                <button onclick="jQuery('#patient-wizard-modal').hide()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; cursor:pointer; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-no-alt"></span></button>
            </div>

            <div class="wizard-steps-indicator" style="display:flex; justify-content:space-between; position:relative; z-index:1;">
                <div class="step-line" style="position:absolute; top:12px; left:0; right:0; height:2px; background:rgba(255,255,255,0.2); z-index:-1;"></div>
                <div class="step-line-active" id="progress-bar" style="position:absolute; top:12px; left:0; height:2px; background:var(--control-accent); z-index:-1; transition:0.4s; width:0%;"></div>

                <div class="step-item active" data-step="1">
                    <div class="step-num">1</div>
                    <div class="step-label"><?php _e('الأساسية', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-num">2</div>
                    <div class="step-label"><?php _e('التواصل', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-num">3</div>
                    <div class="step-label"><?php _e('الطبي', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="4">
                    <div class="step-num">4</div>
                    <div class="step-label"><?php _e('التشخيص', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="5">
                    <div class="step-num">5</div>
                    <div class="step-label"><?php _e('الإدارة', 'control'); ?></div>
                </div>
            </div>
        </div>

        <form id="patient-wizard-form" style="padding:25px; background:#fff; max-height:65vh; overflow-y:auto;">
            <input type="hidden" name="id" id="wiz-patient-id">
            <input type="hidden" name="is_draft" value="0" id="wiz-is-draft">

            <!-- Step 1: Basic Info -->
            <div class="wizard-step-content" id="step-1">
                <div class="control-card compact-card" style="margin-bottom:20px; border:1px solid var(--control-border); padding:15px;">
                    <div style="display:flex; gap:20px; align-items:center;">
                        <div id="wiz-photo-preview" style="width:80px; height:80px; background:var(--control-bg); border:2px dashed var(--control-border); border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; position:relative; flex-shrink:0;">
                            <span class="dashicons dashicons-camera" style="font-size:28px; color:var(--control-muted);"></span>
                            <img src="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
                        </div>
                        <div style="flex:1;">
                            <button type="button" id="wiz-upload-photo" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); padding:4px 12px; font-size:0.75rem;"><?php _e('رفع صورة الطفل', 'control'); ?></button>
                            <input type="hidden" name="profile_photo" id="wiz-profile-photo">
                        </div>
                    </div>
                </div>

                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="control-form-group">
                        <input type="text" name="full_name" id="wiz-full-name" required placeholder=" ">
                        <label><?php _e('الاسم الكامل', 'control'); ?> *</label>
                    </div>
                    <div class="control-form-group">
                        <input type="date" name="dob" id="wiz-dob" required placeholder=" ">
                        <label><?php _e('تاريخ الميلاد', 'control'); ?> *</label>
                    </div>
                    <div class="control-form-group">
                        <select name="gender" id="wiz-gender">
                            <option value="male"><?php _e('ذكر', 'control'); ?></option>
                            <option value="female"><?php _e('أنثى', 'control'); ?></option>
                        </select>
                        <label><?php _e('الجنس', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <select name="nationality" id="wiz-nationality">
                            <option value=""><?php _e('الجنسية...', 'control'); ?></option>
                            <?php foreach($countries_list as $code => $name): ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label><?php _e('الجنسية', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="height" id="wiz-height" placeholder="cm">
                        <label><?php _e('الطول', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="weight" id="wiz-weight" placeholder="kg">
                        <label><?php _e('الوزن', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <select name="blood_type" id="wiz-blood-type">
                            <option value=""><?php _e('اختر فصيلة الدم...', 'control'); ?></option>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                        </select>
                        <label><?php _e('فصيلة الدم', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Parent/Guardian Info -->
            <div class="wizard-step-content" id="step-2" style="display:none;">
                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="control-form-group">
                        <input type="tel" name="father_phone" id="wiz-father-phone" required placeholder=" ">
                        <label><?php _e('هاتف الأب', 'control'); ?> *</label>
                    </div>
                    <div class="control-form-group">
                        <input type="tel" name="mother_phone" id="wiz-mother-phone" placeholder=" ">
                        <label><?php _e('هاتف الأم', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <input type="email" name="email" id="wiz-email" placeholder=" ">
                        <label><?php _e('البريد الإلكتروني', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <textarea name="address" id="wiz-address" rows="2" placeholder=" "></textarea>
                        <label><?php _e('العنوان بالتفصيل', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="emergency_contact" id="wiz-emergency" placeholder=" ">
                        <label><?php _e('جهة اتصال بديلة', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="drug_allergies" id="wiz-allergies" placeholder=" ">
                        <label><?php _e('حساسية الأدوية', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 3: Medical Info -->
            <div class="wizard-step-content" id="step-3" style="display:none;">
                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="control-form-group" style="grid-column: span 2;">
                        <textarea name="pregnancy_history" id="wiz-pregnancy" rows="2" placeholder=" "></textarea>
                        <label><?php _e('مضاعفات الحمل والولادة', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="milestones_walking" id="wiz-walk" placeholder=" ">
                        <label><?php _e('بداية المشي', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="milestones_speaking" id="wiz-speak" placeholder=" ">
                        <label><?php _e('بداية الكلام', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <textarea name="chronic_conditions" id="wiz-chronic" rows="2" placeholder=" "></textarea>
                        <label><?php _e('الأمراض المزمنة', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <textarea name="current_medications" id="wiz-meds" rows="2" placeholder=" "></textarea>
                        <label><?php _e('الأدوية الحالية', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 4: Diagnosis -->
            <div class="wizard-step-content" id="step-4" style="display:none;">
                <div class="control-form-group">
                    <textarea name="initial_diagnosis" id="wiz-init-diag" rows="2" placeholder=" "></textarea>
                    <label><?php _e('التشخيص الأولي', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="external_diagnosis_source" id="wiz-ext-source" placeholder=" ">
                    <label><?php _e('مصدر التشخيص', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <textarea name="initial_behavioral_observation" id="wiz-behavior" rows="3" placeholder=" "></textarea>
                    <label><?php _e('الملاحظة السلوكية الأولى', 'control'); ?></label>
                </div>
            </div>

            <!-- Step 5: Administration -->
            <div class="wizard-step-content" id="step-5" style="display:none;">
                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="control-form-group">
                        <select name="case_status" id="wiz-status">
                            <option value="waiting_list"><?php _e('قائمة الانتظار', 'control'); ?></option>
                            <option value="active"><?php _e('نشط', 'control'); ?></option>
                        </select>
                        <label><?php _e('الحالة الإدارية', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <select name="intake_status" id="wiz-intake-status">
                            <option value="pending"><?php _e('طلب قيد المراجعة', 'control'); ?></option>
                            <option value="approved"><?php _e('مقبول', 'control'); ?></option>
                            <option value="rejected"><?php _e('مرفوض', 'control'); ?></option>
                        </select>
                        <label><?php _e('حالة طلب الالتحاق', 'control'); ?></label>
                    </div>
                </div>
                <div style="margin-top:20px;">
                    <h5 style="margin:0 0 10px 0; font-weight:800; color:var(--control-primary); font-size:0.9rem;"><?php _e('تكليف الأخصائيين', 'control'); ?></h5>
                    <div class="specialists-selection-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                        <?php foreach($specialists as $sp): ?>
                            <label class="compact-sp-item">
                                <input type="checkbox" class="sp-check" value="<?php echo $sp->id; ?>">
                                <span style="font-size:0.8rem;"><?php echo esc_html($sp->first_name); ?> (<?php echo $sp->role; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="assigned_specialists" id="wiz-assigned-ids">
                </div>
            </div>
        </form>

        <div class="wizard-footer" style="padding:20px 30px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div id="wiz-autosave-status" style="font-size:0.7rem; color:#10b981; font-weight:700;"></div>
            <div style="display:flex; gap:10px;">
                <button type="button" id="wiz-prev" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); display:none; padding:6px 15px; font-size:0.85rem;"><?php _e('السابق', 'control'); ?></button>
                <button type="button" id="wiz-next" class="control-btn" style="background:var(--control-primary); border:none; min-width:100px; font-weight:800; padding:6px 15px; font-size:0.85rem;"><?php _e('التالي', 'control'); ?></button>
                <button type="button" id="wiz-submit" class="control-btn" style="background:var(--control-accent); color:var(--control-primary-soft) !important; border:none; display:none; min-width:120px; font-weight:900; padding:6px 15px; font-size:0.85rem;"><?php _e('حفظ الملف', 'control'); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
.wizard-header .step-item { flex:1; text-align:center; color:rgba(255,255,255,0.4); transition:0.3s; }
.wizard-header .step-item.active { color:#fff; }
.step-num { width:24px; height:24px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 5px; font-weight:800; font-size:0.75rem; }
.step-item.active .step-num { background:var(--control-accent); color:var(--control-primary); }
.step-label { font-size:0.65rem; font-weight:800; text-transform:uppercase; }

.compact-sp-item { display:flex; align-items:center; gap:8px; background:#f1f5f9; padding:8px 12px; border-radius:8px; cursor:pointer; }
.compact-sp-item input { width:16px; height:16px; }

.control-form-group { margin-bottom: 12px; }
.control-form-group input, .control-form-group select, .control-form-group textarea { padding: 8px 12px; font-size: 0.9rem; border-radius: 8px; border-color: #e2e8f0; }
.control-form-group label { font-size: 0.75rem; top: 10px; right: 12px; }

@media (max-width: 600px) {
    .control-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
function calculateAge(dobString) {
    if (!dobString) return '';
    const dob = new Date(dobString);
    const today = new Date();
    let years = today.getFullYear() - dob.getFullYear();
    let months = today.getMonth() - dob.getMonth();
    if (months < 0 || (months === 0 && today.getDate() < dob.getDate())) {
        years--;
        months += 12;
    }
    return `<?php _e('العمر:', 'control'); ?> ${years} <?php _e('سنة و', 'control'); ?> ${months} <?php _e('شهر', 'control'); ?>`;
}

function checkBirthdayAlert(dobString) {
    if (!dobString) return false;
    const dob = new Date(dobString);
    const today = new Date();
    const nextBday = new Date(today.getFullYear(), dob.getMonth(), dob.getDate());
    if (nextBday < today) nextBday.setFullYear(today.getFullYear() + 1);
    const diff = Math.ceil((nextBday - today) / (1000 * 60 * 60 * 24));
    return diff <= 30;
}

jQuery(document).ready(function($) {
    $('#wiz-dob').on('change', function() {
        const ageText = calculateAge($(this).val());
        $('#wiz-age-badge').text(ageText).fadeIn();

        if (checkBirthdayAlert($(this).val())) {
            $('#wiz-age-badge').append(' 🎂 <span style="color:#fff"><?php _e('عيد ميلاد قريب!', 'control'); ?></span>');
        }
    });
});
</script>
