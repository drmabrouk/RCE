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
        <div class="wizard-header" style="background:var(--control-primary); color:#fff; padding:30px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="wizard-title" style="margin:0; font-size:1.4rem; color:#fff;"><?php _e('تسجيل حالة طفل جديد', 'control'); ?></h3>
                <button onclick="jQuery('#patient-wizard-modal').hide()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; cursor:pointer; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-no-alt"></span></button>
            </div>

            <div class="wizard-steps-indicator" style="display:flex; justify-content:space-between; position:relative; z-index:1;">
                <div class="step-line" style="position:absolute; top:15px; left:0; right:0; height:2px; background:rgba(255,255,255,0.2); z-index:-1;"></div>
                <div class="step-line-active" id="progress-bar" style="position:absolute; top:15px; left:0; height:2px; background:var(--control-accent); z-index:-1; transition:0.4s; width:0%;"></div>

                <div class="step-item active" data-step="1">
                    <div class="step-num">1</div>
                    <div class="step-label"><?php _e('البيانات الأساسية', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-num">2</div>
                    <div class="step-label"><?php _e('التواصل', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-num">3</div>
                    <div class="step-label"><?php _e('التاريخ الطبي', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="4">
                    <div class="step-num">4</div>
                    <div class="step-label"><?php _e('التشخيص', 'control'); ?></div>
                </div>
                <div class="step-item" data-step="5">
                    <div class="step-num">5</div>
                    <div class="step-label"><?php _e('الإجراء الإداري', 'control'); ?></div>
                </div>
            </div>
        </div>

        <form id="patient-wizard-form" style="padding:40px; background:#fff; max-height:65vh; overflow-y:auto;">
            <input type="hidden" name="id" id="wiz-patient-id">
            <input type="hidden" name="is_draft" value="0" id="wiz-is-draft">

            <!-- Step 1: Basic Info -->
            <div class="wizard-step-content" id="step-1">
                <div style="display:flex; gap:30px; align-items:center; margin-bottom:30px; background:var(--control-bg); padding:25px; border-radius:20px; border:1px solid var(--control-border);">
                    <div id="wiz-photo-preview" style="width:110px; height:110px; background:#fff; border:2px dashed var(--control-border); border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; position:relative; flex-shrink:0;">
                        <span class="dashicons dashicons-camera" style="font-size:36px; color:var(--control-muted);"></span>
                        <img src="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
                    </div>
                    <div style="flex:1;">
                        <button type="button" id="wiz-upload-photo" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);"><?php _e('رفع صورة الطفل', 'control'); ?></button>
                        <input type="hidden" name="profile_photo" id="wiz-profile-photo">
                        <p style="margin:10px 0 0 0; font-size:0.8rem; color:var(--control-muted);"><?php _e('صورة واضحة للوجه للتعرف السريع على الحالة.', 'control'); ?></p>
                    </div>
                </div>

                <div class="control-grid" style="grid-template-columns: 2fr 1fr; gap:20px;">
                    <div class="control-form-group">
                        <input type="text" name="full_name" id="wiz-full-name" required placeholder=" ">
                        <label><?php _e('الاسم الكامل للطفل', 'control'); ?> *</label>
                    </div>
                    <div class="control-form-group">
                        <input type="date" name="dob" id="wiz-dob" required placeholder=" ">
                        <label><?php _e('تاريخ الميلاد', 'control'); ?> *</label>
                    </div>
                </div>

                <div class="control-grid" style="grid-template-columns: 1fr 1fr 1fr; gap:20px;">
                    <div class="control-form-group">
                        <select name="gender" id="wiz-gender">
                            <option value="male"><?php _e('ذكر', 'control'); ?></option>
                            <option value="female"><?php _e('أنثى', 'control'); ?></option>
                        </select>
                        <label><?php _e('الجنس', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <select name="nationality" id="wiz-nationality" class="select2-searchable">
                            <option value=""><?php _e('اختر الجنسية...', 'control'); ?></option>
                            <?php foreach($countries_list as $code => $name): ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label><?php _e('الجنسية', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <select name="blood_type" id="wiz-blood-type">
                            <option value=""><?php _e('غير معروف', 'control'); ?></option>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                        </select>
                        <label><?php _e('فصيلة الدم', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Contact Info -->
            <div class="wizard-step-content" id="step-2" style="display:none;">
                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="control-form-group">
                        <div class="phone-input-wrapper" style="display:flex; gap:5px; direction:ltr;">
                            <input type="tel" name="father_phone" id="wiz-father-phone" required placeholder=" " style="flex:1;">
                        </div>
                        <label style="direction:rtl; right:12px; left:auto;"><?php _e('رقم هاتف الأب', 'control'); ?> *</label>
                    </div>
                    <div class="control-form-group">
                        <div class="phone-input-wrapper" style="display:flex; gap:5px; direction:ltr;">
                            <input type="tel" name="mother_phone" id="wiz-mother-phone" placeholder=" " style="flex:1;">
                        </div>
                        <label style="direction:rtl; right:12px; left:auto;"><?php _e('رقم هاتف الأم', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <input type="email" name="email" id="wiz-email" placeholder=" ">
                        <label><?php _e('البريد الإلكتروني للتواصل', 'control'); ?></label>
                    </div>
                    <div class="control-form-group" style="grid-column: span 2;">
                        <textarea name="address" id="wiz-address" rows="2" placeholder=" "></textarea>
                        <label><?php _e('العنوان السكني بالكامل', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="emergency_contact" id="wiz-emergency" placeholder=" ">
                        <label><?php _e('جهة اتصال للطوارئ (بديلة)', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <input type="text" name="drug_allergies" id="wiz-allergies" placeholder=" ">
                        <label><?php _e('حساسية الأدوية المعروفة', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 3: Medical History -->
            <div class="wizard-step-content" id="step-3" style="display:none;">
                <div class="control-card" style="border:1px solid var(--control-border); padding:20px; margin-bottom:20px;">
                    <h4 style="margin-top:0; color:var(--control-primary);"><?php _e('تاريخ الحمل والولادة', 'control'); ?></h4>
                    <div class="control-form-group">
                        <textarea name="pregnancy_history" id="wiz-pregnancy" rows="2" placeholder=" "></textarea>
                        <label><?php _e('مضاعفات الحمل', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <textarea name="birth_history" id="wiz-birth" rows="2" placeholder=" "></textarea>
                        <label><?php _e('مضاعفات الولادة (نقص أكسجين، دخول حضانة)', 'control'); ?></label>
                    </div>
                </div>

                <div class="control-card" style="border:1px solid var(--control-border); padding:20px;">
                    <h4 style="margin-top:0; color:var(--control-primary);"><?php _e('مراحل التطور النموذجي والأمراض', 'control'); ?></h4>
                    <div class="control-grid" style="grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-bottom:20px;">
                        <div class="control-form-group">
                            <input type="text" name="milestones_walking" id="wiz-walk" placeholder=" ">
                            <label><?php _e('بداية المشي', 'control'); ?></label>
                        </div>
                        <div class="control-form-group">
                            <input type="text" name="milestones_speaking" id="wiz-speak" placeholder=" ">
                            <label><?php _e('بداية الكلام', 'control'); ?></label>
                        </div>
                        <div class="control-form-group">
                            <input type="text" name="milestones_sitting" id="wiz-sit" placeholder=" ">
                            <label><?php _e('بداية الجلوس', 'control'); ?></label>
                        </div>
                    </div>
                    <div class="control-form-group">
                        <textarea name="chronic_conditions" id="wiz-chronic" rows="2" placeholder=" "></textarea>
                        <label><?php _e('الأمراض المزمنة / الإعاقات المصاحبة', 'control'); ?></label>
                    </div>
                    <div class="control-form-group">
                        <textarea name="current_medications" id="wiz-meds" rows="2" placeholder=" "></textarea>
                        <label><?php _e('الأدوية الحالية والجدول الزمني', 'control'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Step 4: Diagnosis & Observations -->
            <div class="wizard-step-content" id="step-4" style="display:none;">
                <div class="control-form-group">
                    <textarea name="initial_diagnosis" id="wiz-init-diag" rows="3" placeholder=" "></textarea>
                    <label><?php _e('التشخيص الأولي (عند القبول)', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="external_diagnosis_source" id="wiz-ext-source" placeholder=" ">
                    <label><?php _e('مصدر التشخيص الخارجي (اسم الطبيب/الجهة)', 'control'); ?></label>
                </div>
                <div class="control-form-group" style="margin-top:20px;">
                    <div style="background:#fff9c4; padding:15px; border-radius:12px; border:1px solid #fbc02d; margin-bottom:10px;">
                        <h5 style="margin:0 0 10px 0; color:#f57f17;"><span class="dashicons dashicons-visibility" style="vertical-align:middle;"></span> <?php _e('الملاحظة السلوكية للجلسة الأولى', 'control'); ?></h5>
                        <p style="font-size:0.8rem; margin:0; line-height:1.4;"><?php _e('سجل انطباعك عن التواصل البصري، تنفيذ التعليمات، السلوكيات النمطية، والمهارات التفاعلية.', 'control'); ?></p>
                    </div>
                    <textarea name="initial_behavioral_observation" id="wiz-behavior" rows="5" placeholder=" "></textarea>
                    <label><?php _e('تقرير الملاحظة الأولية', 'control'); ?></label>
                </div>
            </div>

            <!-- Step 5: Administration & Assignment -->
            <div class="wizard-step-content" id="step-5" style="display:none;">
                <div class="control-card" style="background:#f1f5f9; padding:25px; border:none; border-radius:20px;">
                    <div class="control-form-group">
                        <select name="case_status" id="wiz-status" style="background:#fff;">
                            <option value="waiting_list"><?php _e('قائمة الانتظار', 'control'); ?></option>
                            <option value="active"><?php _e('نشط - تأهيل مستمر', 'control'); ?></option>
                            <option value="dropped_out"><?php _e('منقطع', 'control'); ?></option>
                            <option value="completed"><?php _e('تم التأهيل بنجاح', 'control'); ?></option>
                        </select>
                        <label><?php _e('الحالة الإدارية للملف', 'control'); ?></label>
                    </div>

                    <div style="margin-top:25px;">
                        <h5 style="margin:0 0 15px 0; font-weight:800; color:var(--control-primary);"><?php _e('تكليف فريق العمل (الأخصائيين)', 'control'); ?></h5>
                        <div class="specialists-selection-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                            <?php foreach($specialists as $sp): ?>
                                <label class="specialist-check-item">
                                    <input type="checkbox" class="sp-check" value="<?php echo $sp->id; ?>" data-name="<?php echo esc_html($sp->first_name . ' ' . $sp->last_name); ?>">
                                    <div class="sp-info">
                                        <div class="sp-name"><?php echo esc_html($sp->first_name . ' ' . $sp->last_name); ?></div>
                                        <div class="sp-role"><?php echo $sp->role; ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="assigned_specialists" id="wiz-assigned-ids">
                    </div>
                </div>

                <div style="margin-top:30px; text-align:center;">
                    <p style="font-size:0.9rem; color:var(--control-muted);"><?php _e('بمجرد حفظ الملف، سيصل إشعار تلقائي للأخصائيين المحددين وسيظهر الطفل في لوحات تحكمهم.', 'control'); ?></p>
                </div>
            </div>
        </form>

        <!-- Footer Actions -->
        <div class="wizard-footer" style="padding:25px 40px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div id="wiz-autosave-status" style="font-size:0.75rem; color:#10b981; font-weight:700;"></div>

            <div style="display:flex; gap:15px;">
                <button type="button" id="wiz-prev" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); display:none;"><?php _e('السابق', 'control'); ?></button>
                <button type="button" id="wiz-next" class="control-btn" style="background:var(--control-primary); border:none; min-width:120px; font-weight:800;"><?php _e('التالي', 'control'); ?></button>
                <button type="button" id="wiz-submit" class="control-btn" style="background:var(--control-accent); color:var(--control-primary-soft) !important; border:none; display:none; min-width:150px; font-weight:900;"><?php _e('اعتماد وتسجيل الحالة', 'control'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function openPatientModal(data = null) {
    const $ = jQuery;
    const form = $('#patient-wizard-form');
    form[0].reset();
    $('#wiz-patient-id').val('');
    $('#wiz-is-draft').val('0');
    $('#wizard-title').text('<?php _e('تسجيل حالة طفل جديد', 'control'); ?>');
    $('#wiz-photo-preview img').hide();
    $('#wiz-photo-preview span').show();
    $('.sp-check').prop('checked', false);

    if (data) {
        $('#wizard-title').text('<?php _e('تعديل بيانات الطفل', 'control'); ?>');
        $('#wiz-patient-id').val(data.id);
        $('#wiz-full-name').val(data.full_name);
        $('#wiz-dob').val(data.dob);
        $('#wiz-gender').val(data.gender);
        $('#wiz-nationality').val(data.nationality).trigger('change');
        $('#wiz-blood-type').val(data.blood_type);
        $('#wiz-father-phone').val(data.father_phone);
        $('#wiz-mother-phone').val(data.mother_phone);
        $('#wiz-email').val(data.email);
        $('#wiz-address').val(data.address);
        $('#wiz-emergency').val(data.emergency_contact);
        $('#wiz-allergies').val(data.drug_allergies);
        $('#wiz-pregnancy').val(data.pregnancy_history);
        $('#wiz-birth').val(data.birth_history);
        $('#wiz-walk').val(data.milestones_walking);
        $('#wiz-speak').val(data.milestones_speaking);
        $('#wiz-sit').val(data.milestones_sitting);
        $('#wiz-chronic').val(data.chronic_conditions);
        $('#wiz-meds').val(data.current_medications);
        $('#wiz-init-diag').val(data.initial_diagnosis);
        $('#wiz-ext-source').val(data.external_diagnosis_source);
        $('#wiz-behavior').val(data.initial_behavioral_observation);
        $('#wiz-status').val(data.case_status);

        if (data.profile_photo) {
            $('#wiz-photo-preview img').attr('src', data.profile_photo).show();
            $('#wiz-photo-preview span').hide();
            $('#wiz-profile-photo').val(data.profile_photo);
        }

        if (data.assigned_specialists) {
            const ids = data.assigned_specialists.split(',');
            ids.forEach(id => $(`.sp-check[value="${id}"]`).prop('checked', true));
        }
    }

    goToStep(1);
    $('#patient-wizard-modal').css('display', 'flex');
    if (typeof updateFloatingLabels === 'function') updateFloatingLabels();
}

let wizCurrentStep = 1;
function goToStep(step) {
    const $ = jQuery;
    $('.wizard-step-content').hide();
    $(`#step-${step}`).show();

    $('.step-item').removeClass('active completed');
    for(let i=1; i<step; i++) $(`.step-item[data-step="${i}"]`).addClass('completed');
    $(`.step-item[data-step="${step}"]`).addClass('active');

    $('#wiz-prev').toggle(step > 1);
    $('#wiz-next').toggle(step < 5);
    $('#wiz-submit').toggle(step === 5);

    const progress = ((step - 1) / 4) * 100;
    $('#progress-bar').css('width', progress + '%');

    wizCurrentStep = step;
    if (typeof updateFloatingLabels === 'function') updateFloatingLabels();
}

jQuery(document).ready(function($) {
    $('#wiz-next').on('click', function() {
        if (validateWizStep(wizCurrentStep)) {
            autoSaveDraft();
            goToStep(wizCurrentStep + 1);
        }
    });

    $('#wiz-prev').on('click', function() {
        goToStep(wizCurrentStep - 1);
    });

    function validateWizStep(step) {
        let valid = true;
        $(`#step-${step} [required]`).each(function() {
            if (!$(this).val()) {
                $(this).css('border-color', '#ef4444');
                valid = false;
            } else {
                $(this).css('border-color', '');
            }
        });
        return valid;
    }

    $('#wiz-upload-photo, #wiz-photo-preview').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'اختر صورة الطفل', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#wiz-profile-photo').val(attachment.url);
            $('#wiz-photo-preview img').attr('src', attachment.url).show();
            $('#wiz-photo-preview span').hide();
            autoSaveDraft();
        });
    });

    function autoSaveDraft() {
        if (wizCurrentStep < 1) return;

        // Update assigned specialists hidden field before saving
        const ids = $('.sp-check:checked').map((_, el) => el.value).get().join(',');
        $('#wiz-assigned-ids').val(ids);

        const formData = $('#patient-wizard-form').serialize() + '&action=control_save_patient&nonce=' + control_ajax.nonce + '&is_draft=1';

        $('#wiz-autosave-status').text('<?php _e("جاري حفظ مسودة...", "control"); ?>');
        $.post(control_ajax.ajax_url, formData, function(res) {
            if (res.success) {
                if (!$('#wiz-patient-id').val()) $('#wiz-patient-id').val(res.data.id);
                $('#wiz-autosave-status').html('<span class="dashicons dashicons-yes"></span> <?php _e("تم حفظ التغييرات", "control"); ?>');
            }
        });
    }

    $('#wiz-submit').on('click', function() {
        const ids = $('.sp-check:checked').map((_, el) => el.value).get().join(',');
        $('#wiz-assigned-ids').val(ids);
        $('#wiz-is-draft').val('0');

        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e("جاري التسجيل النهائي...", "control"); ?>');

        const formData = $('#patient-wizard-form').serialize() + '&action=control_save_patient&nonce=' + control_ajax.nonce;
        $.post(control_ajax.ajax_url, formData, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('<?php _e("اعتماد وتسجيل الحالة", "control"); ?>');
            }
        });
    });
});
</script>

<style>
.wizard-header .step-item { flex:1; text-align:center; color:rgba(255,255,255,0.4); transition:0.3s; }
.wizard-header .step-item.active { color:#fff; }
.wizard-header .step-item.completed { color:var(--control-accent); }
.step-num { width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-weight:800; border:2px solid transparent; }
.step-item.active .step-num { background:var(--control-accent); color:var(--control-primary); border-color:#fff; transform:scale(1.1); box-shadow:0 0 15px rgba(212,175,55,0.4); }
.step-item.completed .step-num { background:var(--control-accent); color:var(--control-primary); }
.step-label { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; }

.specialist-check-item { display:flex; align-items:center; gap:12px; background:#fff; padding:12px; border-radius:12px; cursor:pointer; border:1px solid transparent; transition:0.2s; }
.specialist-check-item:hover { background:var(--control-bg); }
.specialist-check-item input[type="checkbox"] { width:20px; height:20px; cursor:pointer; }
.sp-name { font-size:0.85rem; font-weight:700; color:var(--control-text-dark); }
.sp-role { font-size:0.7rem; color:var(--control-muted); }
.specialist-check-item:has(input:checked) { border-color:var(--control-accent); background:var(--control-accent-soft); }

.wizard-step-content { animation: slideIn 0.4s ease-out; }
@keyframes slideIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }

/* Shared CSS Adjustments */
.control-form-group label { right:12px; left:auto; }
.phone-input-wrapper input { padding-right:12px !important; }
</style>
