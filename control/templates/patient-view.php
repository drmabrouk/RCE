<?php
global $wpdb;
$patient_id = intval( $_GET['id'] ?? 0 );
$patient = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patients WHERE id = %d", $patient_id ) );

if ( ! $patient ) {
    echo '<div class="control-card">' . __('المريض غير موجود.', 'control') . '</div>';
    return;
}

$can_view_clinical = Control_Auth::has_permission('pediatric_view_clinical');
$can_manage = Control_Auth::has_permission('pediatric_manage');

$assessments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patient_assessments WHERE patient_id = %d ORDER BY test_date DESC", $patient_id ) );
$documents = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patient_documents WHERE patient_id = %d ORDER BY uploaded_at DESC", $patient_id ) );
$referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}control_patient_referrals WHERE patient_id = %d ORDER BY referral_date DESC", $patient_id ) );

$status_labels = array(
    'active'       => __('نشط', 'control'),
    'waiting_list' => __('قائمة الانتظار', 'control'),
    'dropped_out'  => __('منقطع', 'control'),
    'completed'    => __('تم التأهيل', 'control'),
);
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
    <div style="display:flex; gap:20px; align-items:center;">
        <div style="width:80px; height:80px; background:var(--control-bg); border-radius:20px; overflow:hidden; border:2px solid #fff; box-shadow:0 10px 25px rgba(0,0,0,0.05); flex-shrink:0;">
            <?php if($patient->profile_photo): ?>
                <img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#94a3b8;">
                    <span class="dashicons dashicons-admin-users" style="font-size:40px; width:40px; height:40px;"></span>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <h2 style="font-weight:800; font-size:1.5rem; margin:0; color:var(--control-text-dark);"><?php echo esc_html($patient->full_name); ?></h2>
            <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
                <span class="patient-status-badge status-<?php echo esc_attr($patient->case_status); ?>" style="font-size:0.75rem; padding:3px 12px; border-radius:12px; font-weight:700;">
                    <?php echo $status_labels[$patient->case_status] ?? $patient->case_status; ?>
                </span>
                <span style="color:var(--control-muted); font-size:0.85rem;">#<?php echo $patient->id; ?></span>
            </div>
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <button onclick="window.print()" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);">
            <span class="dashicons dashicons-printer" style="margin-left:5px;"></span><?php _e('طباعة الملف', 'control'); ?>
        </button>
        <a href="<?php echo add_query_arg('control_view', 'pediatric_records'); ?>" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);">
            <span class="dashicons dashicons-arrow-right-alt" style="margin-left:5px;"></span><?php _e('العودة للقائمة', 'control'); ?>
        </a>
        <?php if($can_manage): ?>
            <button class="control-btn edit-patient-btn" data-id="<?php echo $patient->id; ?>" style="background:var(--control-primary); border:none;">
                <span class="dashicons dashicons-edit" style="margin-left:5px;"></span><?php _e('تعديل الملف', 'control'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="control-card" style="padding:0; border:none; overflow:hidden;">
    <div class="control-tabs" style="display:flex; background:#fff; border-bottom:1px solid #e2e8f0; padding:0 20px; gap:20px; overflow-x:auto;">
        <button class="tab-btn active" data-tab="tab-overview"><?php _e('نظرة عامة', 'control'); ?></button>
        <button class="tab-btn" data-tab="tab-medical"><?php _e('التاريخ الطبي', 'control'); ?></button>
        <?php if($can_view_clinical): ?>
            <button class="tab-btn" data-tab="tab-assessments"><?php _e('التقييمات والتشخيص', 'control'); ?></button>
            <button class="tab-btn" data-tab="tab-behavioral"><?php _e('الملاحظة السلوكية', 'control'); ?></button>
        <?php endif; ?>
        <button class="tab-btn" data-tab="tab-documents"><?php _e('الوثائق والملفات', 'control'); ?></button>
        <?php if($can_view_clinical): ?>
            <button class="tab-btn" data-tab="tab-referrals"><?php _e('التحويلات الداخلية', 'control'); ?></button>
        <?php endif; ?>
    </div>

    <div style="padding:30px; background:#fff;">
        <!-- Overview Tab -->
        <div id="tab-overview" class="tab-content">
            <div class="control-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:30px;">
                <div>
                    <h4 style="border-bottom:2px solid var(--control-bg); padding-bottom:10px; margin-bottom:20px;"><?php _e('البيانات الديموغرافية', 'control'); ?></h4>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('الاسم الكامل', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->full_name); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('تاريخ الميلاد', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->dob); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('الجنس', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo $patient->gender === 'male' ? __('ذكر', 'control') : __('أنثى', 'control'); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 style="border-bottom:2px solid var(--control-bg); padding-bottom:10px; margin-bottom:20px;"><?php _e('معلومات التواصل', 'control'); ?></h4>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('هاتف الأب', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->father_phone); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('هاتف الأم', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->mother_phone); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('العنوان السكني', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->address); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 style="border-bottom:2px solid var(--control-bg); padding-bottom:10px; margin-bottom:20px;"><?php _e('معلومات الطوارئ', 'control'); ?></h4>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('جهة اتصال بديلة', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->emergency_contact); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('فصيلة الدم', 'control'); ?></label>
                            <span style="font-weight:700;"><?php echo esc_html($patient->blood_type); ?></span>
                        </div>
                        <div>
                            <label style="color:var(--control-muted); font-size:0.8rem; display:block;"><?php _e('حساسية الأدوية', 'control'); ?></label>
                            <span style="font-weight:700; color:#ef4444;"><?php echo esc_html($patient->drug_allergies) ?: __('لا يوجد', 'control'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="id-card-print-section" style="margin-bottom:30px;">
                <div class="patient-id-card" style="width:350px; background:linear-gradient(135deg, #1e293b, #334155); color:#fff; border-radius:15px; padding:20px; display:flex; gap:15px; align-items:center; position:relative; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">
                    <div style="position:absolute; top:-20px; left:-20px; width:100px; height:100px; background:var(--control-accent); opacity:0.1; border-radius:50%;"></div>
                    <div style="width:80px; height:80px; background:#fff; border-radius:10px; overflow:hidden; border:2px solid var(--control-accent); flex-shrink:0;">
                        <?php if($patient->profile_photo): ?>
                            <img src="<?php echo esc_url($patient->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:40px;"></span></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:800; font-size:1.1rem; margin-bottom:5px;"><?php echo esc_html($patient->full_name); ?></div>
                        <div style="font-size:0.7rem; color:rgba(255,255,255,0.7); display:grid; grid-template-columns: 1fr 1fr; gap:5px;">
                            <span><?php _e('العمر:', 'control'); ?> <strong style="color:#fff;"><?php
                                $dob = new DateTime($patient->dob);
                                $now = new DateTime();
                                $age = $now->diff($dob);
                                echo $age->y . ' ' . __('سنة', 'control');
                            ?></strong></span>
                            <span><?php _e('رقم الملف:', 'control'); ?> <strong style="color:#fff;">#<?php echo $patient->id; ?></strong></span>
                            <span><?php _e('الطول:', 'control'); ?> <strong style="color:#fff;"><?php echo $patient->height ?: '---'; ?></strong></span>
                            <span><?php _e('الوزن:', 'control'); ?> <strong style="color:#fff;"><?php echo $patient->weight ?: '---'; ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:40px;">
                <h4 style="border-bottom:2px solid var(--control-bg); padding-bottom:10px; margin-bottom:20px;"><?php _e('الفريق المعين', 'control'); ?></h4>
                <div style="background:#f8fafc; padding:20px; border-radius:15px; border:1px solid #e2e8f0;">
                    <p style="margin:0; font-weight:600; color:var(--control-primary);"><?php echo esc_html($patient->assigned_specialists) ?: __('لم يتم تعيين فريق عمل بعد.', 'control'); ?></p>
                </div>
            </div>
        </div>

        <!-- Medical Tab -->
        <div id="tab-medical" class="tab-content" style="display:none;">
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:30px;">
                <div>
                    <h4 style="color:var(--control-primary); margin-bottom:15px;"><?php _e('تاريخ الحمل والولادة', 'control'); ?></h4>
                    <div class="info-box" style="background:#f1f5f9; padding:15px; border-radius:10px; min-height:100px;">
                        <label style="color:var(--control-muted); font-size:0.75rem; display:block; margin-bottom:5px;"><?php _e('مضاعفات الحمل', 'control'); ?></label>
                        <p><?php echo nl2br(esc_html($patient->pregnancy_history)); ?></p>
                        <label style="color:var(--control-muted); font-size:0.75rem; display:block; margin-top:15px; margin-bottom:5px;"><?php _e('تاريخ الولادة', 'control'); ?></label>
                        <p><?php echo nl2br(esc_html($patient->birth_history)); ?></p>
                    </div>
                </div>
                <div>
                    <h4 style="color:var(--control-primary); margin-bottom:15px;"><?php _e('مراحل التطور النموذجي', 'control'); ?></h4>
                    <table class="control-table" style="background:#f8fafc;">
                        <tr>
                            <td><strong><?php _e('المشي', 'control'); ?></strong></td>
                            <td><?php echo esc_html($patient->milestones_walking); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('الكلام', 'control'); ?></strong></td>
                            <td><?php echo esc_html($patient->milestones_speaking); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('الجلوس', 'control'); ?></strong></td>
                            <td><?php echo esc_html($patient->milestones_sitting); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:30px; margin-top:30px;">
                <div>
                    <h4 style="color:var(--control-primary); margin-bottom:15px;"><?php _e('الأمراض المزمنة', 'control'); ?></h4>
                    <div style="background:#fff1f2; color:#9f1239; padding:15px; border-radius:10px; border:1px solid #fecaca;">
                        <?php echo nl2br(esc_html($patient->chronic_conditions)) ?: __('لا توجد أمراض مزمنة مسجلة.', 'control'); ?>
                    </div>
                </div>
                <div>
                    <h4 style="color:var(--control-primary); margin-bottom:15px;"><?php _e('الأدوية الحالية', 'control'); ?></h4>
                    <div style="background:#f0fdf4; color:#166534; padding:15px; border-radius:10px; border:1px solid #bbf7d0;">
                        <?php echo nl2br(esc_html($patient->current_medications)) ?: __('لا توجد أدوية مسجلة.', 'control'); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if($can_view_clinical): ?>
        <!-- Assessments Tab -->
        <div id="tab-assessments" class="tab-content" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h4 style="color:var(--control-primary);"><?php _e('نتائج الاختبارات والتشخيصات', 'control'); ?></h4>
                <button class="control-btn add-assessment-btn" data-patient-id="<?php echo $patient->id; ?>" style="padding:5px 15px; font-size:0.8rem; background:var(--control-accent); color:var(--control-primary-soft) !important; border:none;">
                    <?php _e('إضافة اختبار جديد', 'control'); ?>
                </button>
            </div>

            <div style="background:#f8fafc; padding:20px; border-radius:15px; border:1px dashed #cbd5e1; margin-bottom:30px;">
                <label style="color:var(--control-muted); font-size:0.8rem;"><?php _e('التشخيص الأولي:', 'control'); ?></label>
                <p style="font-weight:700; font-size:1.1rem; color:var(--control-text-dark); margin-bottom:15px;"><?php echo esc_html($patient->initial_diagnosis); ?></p>
                <label style="color:var(--control-muted); font-size:0.8rem;"><?php _e('مصدر التشخيص الخارجي:', 'control'); ?></label>
                <p><?php echo esc_html($patient->external_diagnosis_source) ?: __('غير محدد', 'control'); ?></p>
            </div>

            <table class="control-table">
                <thead>
                    <tr>
                        <th><?php _e('اسم الاختبار', 'control'); ?></th>
                        <th><?php _e('النتيجة', 'control'); ?></th>
                        <th><?php _e('التاريخ', 'control'); ?></th>
                        <th><?php _e('الفاحص', 'control'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($assessments): foreach($assessments as $a): ?>
                        <tr>
                            <td><strong><?php echo esc_html($a->test_name); ?></strong></td>
                            <td><?php echo nl2br(esc_html($a->test_result)); ?></td>
                            <td><?php echo esc_html($a->test_date); ?></td>
                            <td><?php echo esc_html($a->assessor_id); ?></td>
                            <td style="text-align:left;">
                                <button class="delete-assessment-btn" data-id="<?php echo $a->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد نتائج اختبارات مسجلة.', 'control'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Behavioral Tab -->
        <div id="tab-behavioral" class="tab-content" style="display:none;">
            <h4 style="color:var(--control-primary); margin-bottom:20px;"><?php _e('الملاحظة السلوكية الأولية', 'control'); ?></h4>
            <div style="background:#fff; border:1px solid #e2e8f0; padding:30px; border-radius:20px; line-height:1.8; color:#334155; white-space: pre-line;">
                <?php echo esc_html($patient->initial_behavioral_observation) ?: __('لا توجد ملاحظات مسجلة للجلسة الأولى.', 'control'); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documents Tab -->
        <div id="tab-documents" class="tab-content" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h4 style="color:var(--control-primary);"><?php _e('التقارير الطبية والوثائق الإدارية', 'control'); ?></h4>
                <button class="control-btn add-document-btn" data-patient-id="<?php echo $patient->id; ?>" style="padding:5px 15px; font-size:0.8rem; background:var(--control-accent); color:var(--control-primary-soft) !important; border:none;">
                    <?php _e('رفع ملف جديد', 'control'); ?>
                </button>
            </div>

            <div class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:20px;">
                <?php if($documents): foreach($documents as $d): ?>
                    <div class="control-card" style="padding:15px; text-align:center; position:relative;">
                        <span class="dashicons dashicons-media-document" style="font-size:40px; width:40px; height:40px; color:var(--control-primary); margin-bottom:10px;"></span>
                        <h5 style="margin:5px 0; font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($d->doc_name); ?></h5>
                        <small style="color:var(--control-muted); display:block; margin-bottom:10px;"><?php echo esc_html($d->doc_type); ?></small>
                        <div style="display:flex; gap:10px; justify-content:center;">
                            <a href="<?php echo esc_url($d->doc_url); ?>" target="_blank" class="control-btn" style="padding:4px 10px; font-size:0.7rem; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('عرض', 'control'); ?></a>
                            <button class="delete-document-btn" data-id="<?php echo $d->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash" style="font-size:16px;"></span></button>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div style="grid-column:1/-1; text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد وثائق مرفوعة.', 'control'); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($can_view_clinical): ?>
        <!-- Referrals Tab -->
        <div id="tab-referrals" class="tab-content" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h4 style="color:var(--control-primary);"><?php _e('سجل التحويلات بين الأقسام', 'control'); ?></h4>
                <button class="control-btn add-referral-btn" data-patient-id="<?php echo $patient->id; ?>" style="padding:5px 15px; font-size:0.8rem; background:var(--control-accent); color:var(--control-primary-soft) !important; border:none;">
                    <?php _e('إضافة تحويل جديد', 'control'); ?>
                </button>
            </div>

            <table class="control-table">
                <thead>
                    <tr>
                        <th><?php _e('من قسم', 'control'); ?></th>
                        <th><?php _e('إلى قسم', 'control'); ?></th>
                        <th><?php _e('تاريخ التحويل', 'control'); ?></th>
                        <th><?php _e('ملاحظات', 'control'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($referrals): foreach($referrals as $r): ?>
                        <tr>
                            <td><?php echo esc_html($r->from_department); ?></td>
                            <td><strong><?php echo esc_html($r->to_department); ?></strong></td>
                            <td><?php echo esc_html($r->referral_date); ?></td>
                            <td><?php echo esc_html($r->notes); ?></td>
                            <td style="text-align:left;">
                                <button class="delete-referral-btn" data-id="<?php echo $r->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;"><span class="dashicons dashicons-trash"></span></button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--control-muted);"><?php _e('لا توجد سجلات تحويل.', 'control'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include CONTROL_PATH . 'templates/patient-forms.php'; ?>

<script>
jQuery(document).ready(function($) {
    $('.tab-btn').on('click', function() {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        const tab = $(this).data('tab');
        $('.tab-content').hide();
        $('#' + tab).fadeIn();
    });

    $(document).on('click', '.edit-patient-btn', function() {
        const patientData = <?php echo json_encode($patient); ?>;
        openPatientModal(patientData);
    });

    // Assessment Logic
    $('.add-assessment-btn').on('click', function() {
        const patientId = $(this).data('patient-id');
        $('#assessment-patient-id').val(patientId);
        $('#assessment-id').val('0');
        $('#assessment-form')[0].reset();
        $('#assessment-modal').css('display', 'flex');
    });

    $('#assessment-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('<?php _e("جاري الحفظ...", "control"); ?>');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_assessment&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $(document).on('click', '.delete-assessment-btn', function() {
        if(!confirm('<?php _e("حذف نتيجة الاختبار؟", "control"); ?>')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_patient_assessment', id: id, nonce: control_ajax.nonce }, () => location.reload());
    });

    // Document Logic
    $('.add-document-btn').on('click', function() {
        const patientId = $(this).data('patient-id');
        $('#doc-patient-id').val(patientId);
        $('#document-modal').css('display', 'flex');
    });

    $('#upload-doc-btn').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'اختر مستند', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#doc-url').val(attachment.url);
            $('#doc-name').val(attachment.filename);
            $('#upload-doc-btn').text('تم اختيار: ' + attachment.filename).css('background', '#10b981').css('color', '#fff');
        });
    });

    $('#document-form').on('submit', function(e) {
        e.preventDefault();
        if(!$('#doc-url').val()) return alert('<?php _e("يرجى اختيار ملف أولاً", "control"); ?>');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_document&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $(document).on('click', '.delete-document-btn', function() {
        if(!confirm('<?php _e("حذف المستند؟", "control"); ?>')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_patient_document', id: id, nonce: control_ajax.nonce }, () => location.reload());
    });

    // Referral Logic
    $('.add-referral-btn').on('click', function() {
        const patientId = $(this).data('patient-id');
        $('#referral-patient-id').val(patientId);
        $('#referral-modal').css('display', 'flex');
    });

    $('#referral-form').on('submit', function(e) {
        e.preventDefault();
        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_patient_referral&nonce=' + control_ajax.nonce, function(res) {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $(document).on('click', '.delete-referral-btn', function() {
        if(!confirm('<?php _e("حذف سجل التحويل؟", "control"); ?>')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_patient_referral', id: id, nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.tab-btn { background:none; border:none; padding:15px 25px; cursor:pointer; font-weight:700; color:var(--control-muted); border-bottom:3px solid transparent; transition:0.2s; white-space:nowrap; }
.tab-btn.active { color:var(--control-primary); border-bottom-color:var(--control-accent); }
.patient-status-badge.status-active { background: #ecfdf5; color: #059669; }
.patient-status-badge.status-waiting_list { background: #fff7ed; color: #d97706; }
.patient-status-badge.status-dropped_out { background: #fef2f2; color: #ef4444; }
.patient-status-badge.status-completed { background: #eff6ff; color: #2563eb; }
.info-box p { margin:0; line-height:1.6; color:var(--control-text-dark); font-weight:500; }
</style>
