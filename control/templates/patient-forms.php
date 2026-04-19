<!-- Patient Admission/Edit Modal -->
<div id="patient-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10005; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:800px; padding:0; border-radius:20px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="background:var(--control-primary); color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center;">
            <h3 id="patient-modal-title" style="color:#fff; margin:0; font-size:1.1rem;"><?php _e('تسجيل حالة طفل جديد', 'control'); ?></h3>
            <button onclick="jQuery('#patient-modal').hide()" style="background:none; border:none; color:#fff; cursor:pointer;"><span class="dashicons dashicons-no-alt"></span></button>
        </div>

        <form id="patient-form" style="padding:30px; max-height:75vh; overflow-y:auto;">
            <input type="hidden" name="id" id="patient-id">

            <!-- Profile Photo Upload -->
            <div style="display:flex; gap:25px; margin-bottom:25px; align-items:center; background:var(--control-bg); padding:20px; border-radius:16px; border:1px solid var(--control-border);">
                <div id="patient-photo-preview" style="width:90px; height:90px; background:#fff; border:2px dashed var(--control-border); border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; position:relative; flex-shrink:0;">
                    <span class="dashicons dashicons-camera" style="font-size:32px; color:var(--control-muted);"></span>
                    <img src="" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
                </div>
                <div style="flex:1;">
                    <button type="button" id="upload-patient-photo" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); padding:6px 15px; font-size:0.8rem; min-height:36px;"><?php _e('رفع صورة الطفل', 'control'); ?></button>
                    <input type="hidden" name="profile_photo" id="p-profile-photo">
                    <p style="margin:8px 0 0 0; font-size:0.7rem; color:var(--control-muted);"><?php _e('الصورة الشخصية للتعرف السريع على الطفل.', 'control'); ?></p>
                </div>
            </div>

            <!-- Section 1: Basic & Contact -->
            <h4 class="form-section-title"><?php _e('1. البيانات الأساسية والتواصل', 'control'); ?></h4>
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="control-form-group">
                    <input type="text" name="full_name" id="p-full-name" required placeholder=" ">
                    <label><?php _e('الاسم الكامل للطفل', 'control'); ?> *</label>
                </div>
                <div class="control-form-group">
                    <input type="date" name="dob" id="p-dob" required placeholder=" ">
                    <label><?php _e('تاريخ الميلاد', 'control'); ?> *</label>
                </div>
                <div class="control-form-group">
                    <select name="gender" id="p-gender">
                        <option value="male"><?php _e('ذكر', 'control'); ?></option>
                        <option value="female"><?php _e('أنثى', 'control'); ?></option>
                    </select>
                    <label><?php _e('الجنس', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="blood_type" id="p-blood-type" placeholder=" ">
                    <label><?php _e('فصيلة الدم', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="tel" name="father_phone" id="p-father-phone" placeholder=" ">
                    <label><?php _e('رقم هاتف الأب', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="tel" name="mother_phone" id="p-mother-phone" placeholder=" ">
                    <label><?php _e('رقم هاتف الأم', 'control'); ?></label>
                </div>
                <div class="control-form-group" style="grid-column: span 2;">
                    <input type="email" name="email" id="p-email" placeholder=" ">
                    <label><?php _e('البريد الإلكتروني للتواصل', 'control'); ?></label>
                </div>
                <div class="control-form-group" style="grid-column: span 2;">
                    <textarea name="address" id="p-address" rows="2" placeholder=" "></textarea>
                    <label><?php _e('العنوان السكني بالكامل', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="emergency_contact" id="p-emergency" placeholder=" ">
                    <label><?php _e('جهة اتصال للطوارئ (بديلة)', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="drug_allergies" id="p-allergies" placeholder=" ">
                    <label><?php _e('حساسية الأدوية المعروفة', 'control'); ?></label>
                </div>
            </div>

            <!-- Section 2: Medical History -->
            <h4 class="form-section-title"><?php _e('2. التاريخ الطبي والنمائي', 'control'); ?></h4>
            <div class="control-form-group">
                <textarea name="pregnancy_history" id="p-pregnancy" rows="2" placeholder=" "></textarea>
                <label><?php _e('تاريخ ومضاعفات الحمل', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <textarea name="birth_history" id="p-birth" rows="2" placeholder=" "></textarea>
                <label><?php _e('تاريخ الولادة (نقص أكسجين، حضانة، إلخ)', 'control'); ?></label>
            </div>
            <div class="control-grid" style="grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                <div class="control-form-group">
                    <input type="text" name="milestones_walking" id="p-walk" placeholder=" ">
                    <label><?php _e('بداية المشي', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="milestones_speaking" id="p-speak" placeholder=" ">
                    <label><?php _e('بداية الكلام', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="milestones_sitting" id="p-sit" placeholder=" ">
                    <label><?php _e('بداية الجلوس', 'control'); ?></label>
                </div>
            </div>
            <div class="control-form-group">
                <textarea name="chronic_conditions" id="p-chronic" rows="2" placeholder=" "></textarea>
                <label><?php _e('الأمراض المزمنة (سكر، صرع، ضعف سمع/بصر)', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <textarea name="current_medications" id="p-meds" rows="2" placeholder=" "></textarea>
                <label><?php _e('الأدوية الحالية والمواعيد', 'control'); ?></label>
            </div>

            <!-- Section 3: Assessment & Initial Observation -->
            <h4 class="form-section-title"><?php _e('3. التشخيص والملاحظة الأولية', 'control'); ?></h4>
            <div class="control-form-group">
                <textarea name="initial_diagnosis" id="p-init-diag" rows="2" placeholder=" "></textarea>
                <label><?php _e('التشخيص المبدئي عند الدخول', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="external_diagnosis_source" id="p-ext-source" placeholder=" ">
                <label><?php _e('مصدر التشخيص الخارجي (اسم الطبيب)', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <textarea name="initial_behavioral_observation" id="p-behavior" rows="3" placeholder=" "></textarea>
                <label><?php _e('الملاحظة السلوكية الأولى (تواصل بصري، تنفيذ أوامر، إلخ)', 'control'); ?></label>
            </div>

            <!-- Section 4: Admin & Assignment -->
            <h4 class="form-section-title"><?php _e('4. الحالة الإدارية والتكليف', 'control'); ?></h4>
            <div class="control-grid" style="grid-template-columns: 1fr 1.5fr; gap:20px;">
                <div class="control-form-group">
                    <select name="case_status" id="p-status">
                        <option value="waiting_list"><?php _e('قائمة الانتظار', 'control'); ?></option>
                        <option value="active"><?php _e('نشط / مستمر', 'control'); ?></option>
                        <option value="dropped_out"><?php _e('منقطع', 'control'); ?></option>
                        <option value="completed"><?php _e('تم التأهيل', 'control'); ?></option>
                    </select>
                    <label><?php _e('حالة الحالة', 'control'); ?></label>
                </div>
                <div class="control-form-group">
                    <input type="text" name="assigned_specialists" id="p-assigned" placeholder=" ">
                    <label><?php _e('الفريق المعالج (أسماء الأخصائيين)', 'control'); ?></label>
                </div>
            </div>

            <div style="margin-top:30px; display:flex; gap:15px;">
                <button type="submit" class="control-btn" style="flex:2; background:var(--control-primary); border:none; font-weight:800;"><?php _e('حفظ ملف الطفل', 'control'); ?></button>
                <button type="button" onclick="jQuery('#patient-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Assessment Add/Edit Modal -->
<div id="assessment-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:30px;">
        <h3><?php _e('إضافة نتيجة اختبار مقنن', 'control'); ?></h3>
        <form id="assessment-form">
            <input type="hidden" name="id" id="assessment-id">
            <input type="hidden" name="patient_id" id="assessment-patient-id">
            <div class="control-form-group">
                <input type="text" name="test_name" required placeholder=" (مثل: ستانفورد بينيه، كارز، اختبار لغة)">
                <label><?php _e('اسم الاختبار', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <textarea name="test_result" rows="4" required placeholder=" "></textarea>
                <label><?php _e('النتيجة والدرجات', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="date" name="test_date" required value="<?php echo date('Y-m-d'); ?>">
                <label><?php _e('تاريخ الاختبار', 'control'); ?></label>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ النتيجة', 'control'); ?></button>
                <button type="button" onclick="jQuery('#assessment-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Document Upload Modal -->
<div id="document-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:30px;">
        <h3><?php _e('رفع وثيقة أو تقرير طبي', 'control'); ?></h3>
        <form id="document-form">
            <input type="hidden" name="patient_id" id="doc-patient-id">
            <input type="hidden" name="doc_url" id="doc-url">
            <input type="hidden" name="doc_name" id="doc-name">

            <div class="control-form-group">
                <select name="doc_type" required>
                    <option value="medical_report"><?php _e('تقرير طبي', 'control'); ?></option>
                    <option value="eeg"><?php _e('رسم مخ (EEG)', 'control'); ?></option>
                    <option value="scan"><?php _e('أشعة مقطعية/رنين', 'control'); ?></option>
                    <option value="gene_test"><?php _e('تحليل جينات', 'control'); ?></option>
                    <option value="birth_certificate"><?php _e('شهادة ميلاد', 'control'); ?></option>
                    <option value="guardian_id"><?php _e('هوية ولي الأمر', 'control'); ?></option>
                    <option value="agreement"><?php _e('اتفاقية المركز', 'control'); ?></option>
                </select>
                <label><?php _e('نوع الوثيقة', 'control'); ?></label>
            </div>

            <div style="margin:20px 0;">
                <button type="button" id="upload-doc-btn" class="control-btn" style="width:100%; height:60px; border:2px dashed var(--control-border); background:#f8fafc; color:var(--control-muted) !important;">
                    <span class="dashicons dashicons-upload"></span> <?php _e('اختر الملف للرفع', 'control'); ?>
                </button>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('تأكيد الحفظ', 'control'); ?></button>
                <button type="button" onclick="jQuery('#document-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Referral Modal -->
<div id="referral-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10006; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div class="control-card" style="width:100%; max-width:500px; padding:30px;">
        <h3><?php _e('تسجيل تحويل داخلي', 'control'); ?></h3>
        <form id="referral-form">
            <input type="hidden" name="patient_id" id="referral-patient-id">
            <div class="control-form-group">
                <input type="text" name="from_department" required placeholder=" ">
                <label><?php _e('من قسم', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="text" name="to_department" required placeholder=" ">
                <label><?php _e('إلى قسم', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <input type="date" name="referral_date" required value="<?php echo date('Y-m-d'); ?>">
                <label><?php _e('تاريخ التحويل', 'control'); ?></label>
            </div>
            <div class="control-form-group">
                <textarea name="notes" rows="3" placeholder=" "></textarea>
                <label><?php _e('ملاحظات التحويل', 'control'); ?></label>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ التحويل', 'control'); ?></button>
                <button type="button" onclick="jQuery('#referral-modal').hide()" class="control-btn" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openPatientModal(data = null) {
    const $ = jQuery;
    const form = $('#patient-form');
    form[0].reset();
    $('#patient-id').val('');
    $('#patient-modal-title').text('<?php _e('تسجيل حالة طفل جديد', 'control'); ?>');

    if (data) {
        $('#patient-modal-title').text('<?php _e('تعديل بيانات الطفل', 'control'); ?>');
        $('#patient-id').val(data.id);
        $('#p-full-name').val(data.full_name);
        $('#p-dob').val(data.dob);

        if (data.profile_photo) {
            $('#patient-photo-preview img').attr('src', data.profile_photo).show();
            $('#patient-photo-preview span').hide();
            $('#p-profile-photo').val(data.profile_photo);
        } else {
            $('#patient-photo-preview img').hide();
            $('#patient-photo-preview span').show();
        }
        $('#p-gender').val(data.gender);
        $('#p-blood-type').val(data.blood_type);
        $('#p-father-phone').val(data.father_phone);
        $('#p-mother-phone').val(data.mother_phone);
        $('#p-email').val(data.email);
        $('#p-address').val(data.address);
        $('#p-emergency').val(data.emergency_contact);
        $('#p-allergies').val(data.drug_allergies);
        $('#p-pregnancy').val(data.pregnancy_history);
        $('#p-birth').val(data.birth_history);
        $('#p-walk').val(data.milestones_walking);
        $('#p-speak').val(data.milestones_speaking);
        $('#p-sit').val(data.milestones_sitting);
        $('#p-chronic').val(data.chronic_conditions);
        $('#p-meds').val(data.current_medications);
        $('#p-init-diag').val(data.initial_diagnosis);
        $('#p-ext-source').val(data.external_diagnosis_source);
        $('#p-behavior').val(data.initial_behavioral_observation);
        $('#p-status').val(data.case_status);
        $('#p-assigned').val(data.assigned_specialists);
    }

    $('#patient-modal').css('display', 'flex');
    if (typeof updateFloatingLabels === 'function') updateFloatingLabels();
}

jQuery(document).ready(function($) {
    $('#upload-patient-photo, #patient-photo-preview').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'اختر صورة الطفل', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#p-profile-photo').val(attachment.url);
            $('#patient-photo-preview img').attr('src', attachment.url).show();
            $('#patient-photo-preview span').hide();
        });
    });

    $('#patient-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('<?php _e("جاري الحفظ...", "control"); ?>');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) {
                location.reload();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('<?php _e("حفظ ملف الطفل", "control"); ?>');
            }
        });
    });
});
</script>

<style>
.form-section-title { color: var(--control-primary); border-right: 4px solid var(--control-accent); padding-right: 10px; margin: 30px 0 20px; font-size: 1rem; }
.control-form-group label { transition: 0.2s; }
</style>
