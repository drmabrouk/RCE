<div class="view-section-container">
<?php
global $wpdb;
$patients = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}control_patients ORDER BY created_at DESC" );
$can_manage = Control_Auth::has_permission('pediatric_manage');

$status_labels = array(
    'active'          => __('نشط', 'control'),
    'evaluation_only' => __('تقييم فقط', 'control'),
    'waiting_list'    => __('قائمة الانتظار', 'control'),
    'dropped_out'     => __('منقطع', 'control'),
    'completed'       => __('تم التأهيل', 'control'),
    'closed'          => __('ملف مغلق', 'control'),
);

$pending_requests = array_filter($patients, function($p) { return $p->intake_status === 'pending'; });

// Strict Tab Segmentation
$active_records   = array_filter($patients, function($p) { return $p->case_status === 'active'; });
$evaluation_cases = array_filter($patients, function($p) { return $p->case_status === 'evaluation_only'; });
$waiting_cases    = array_filter($patients, function($p) { return $p->case_status === 'waiting_list' && $p->intake_status !== 'pending'; });
$closed_cases     = array_filter($patients, function($p) { return in_array($p->case_status, ['closed', 'completed', 'dropped_out']); });
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 style="font-weight:800; font-size:1.6rem; margin:0; color:var(--control-primary);"><?php _e('إدارة سجلات الأطفال', 'control'); ?></h2>
        <p style="color:var(--control-muted); margin:5px 0 0 0;"><?php echo sprintf(__('إجمالي الحالات: %d | طلبات جديدة: %d', 'control'), count($patients), count($pending_requests)); ?></p>
    </div>
    <div style="display:flex; gap:12px;">
        <?php if($can_manage): ?>
            <button class="control-btn" onclick="openPatientModal()" style="background:var(--control-accent); color: var(--control-primary) !important; border:none; padding:12px 25px; font-weight:800;">
                <span class="dashicons dashicons-plus-alt" style="margin-left:8px;"></span><?php _e('تسجيل طفل جديد', 'control'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Pending Intake Queue -->
<?php if($pending_requests): ?>
<div style="margin-bottom:40px;">
    <h3 style="font-weight:800; color:#d97706; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <span class="dashicons dashicons-clock" style="font-size:24px; width:24px; height:24px;"></span>
        <?php _e('طلبات الالتحاق الجديدة (قيد المراجعة)', 'control'); ?>
    </h3>
    <div class="control-grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach($pending_requests as $p): ?>
            <div class="control-card" style="border-right:5px solid #fbbf24; padding:0; overflow:hidden;">
                <div style="padding:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                        <div>
                            <h4 style="margin:0; font-weight:800;"><?php echo esc_html($p->full_name); ?></h4>
                            <small style="color:var(--control-muted);">ID: <?php echo esc_html($p->temp_id ?: '#'.$p->id); ?></small>
                        </div>
                        <span style="background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:10px; font-size:0.7rem; font-weight:800;"><?php _e('انتظار', 'control'); ?></span>
                    </div>
                    <p style="font-size:0.85rem; color:#475569; margin:0 0 15px 0; line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        <strong><?php _e('سبب الطلب:', 'control'); ?></strong> <?php echo esc_html($p->intake_reason); ?>
                    </p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:0.8rem;">
                        <div><span style="color:var(--control-muted);"><?php _e('تاريخ الطلب:', 'control'); ?></span><br><strong><?php echo date('Y-m-d', strtotime($p->created_at)); ?></strong></div>
                        <div><span style="color:var(--control-muted);"><?php _e('هاتف التواصل:', 'control'); ?></span><br><strong><?php echo esc_html($p->father_phone); ?></strong></div>
                    </div>
                </div>
                <div style="background:#f8fafc; padding:12px 20px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                    <a href="<?php echo add_query_arg(array('resume_id' => $p->id), get_permalink(get_page_by_path('kiosk-registration'))); ?>" class="control-btn" style="background:var(--control-primary); border:none; padding:6px 20px; font-size:0.85rem;">
                        <?php _e('إكمال ملف الطفل', 'control'); ?>
                    </a>
                    <div style="display:flex; gap:10px;">
                        <button class="reject-intake-btn" data-id="<?php echo $p->id; ?>" style="color:#ef4444; background:none; border:none; cursor:pointer;" title="رفض الطلب"><span class="dashicons dashicons-no"></span></button>
                        <button class="delete-patient-btn" data-id="<?php echo $p->id; ?>" style="color:#64748b; background:none; border:none; cursor:pointer;" title="حذف نهائي"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div style="display:flex; gap:30px; align-items:flex-start;" class="pediatric-records-layout">
    <!-- Left Column: Patient Grids -->
    <div style="flex:1;">
        <div id="patient-tabs-content">
            <!-- Active Tab Content -->
            <div id="tab-active" class="p-tab-pane">
                <div id="active-grid" class="control-grid grid-layout">
                    <?php render_patient_cards($active_records, $status_labels, $can_manage); ?>
                </div>
            </div>

            <!-- Evaluation Tab Content -->
            <div id="tab-evaluation" class="p-tab-pane" style="display:none;">
                <div id="evaluation-grid" class="control-grid grid-layout">
                    <?php render_patient_cards($evaluation_cases, $status_labels, $can_manage); ?>
                </div>
            </div>

            <!-- Waiting List Tab Content -->
            <div id="tab-waiting" class="p-tab-pane" style="display:none;">
                <div id="waiting-grid" class="control-grid grid-layout">
                    <?php render_patient_cards($waiting_cases, $status_labels, $can_manage); ?>
                </div>
            </div>

            <!-- Closed Tab Content -->
            <div id="tab-closed" class="p-tab-pane" style="display:none;">
                <div id="closed-grid" class="control-grid grid-layout">
                    <?php render_patient_cards($closed_cases, $status_labels, $can_manage); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Navigation & Search Sidebar -->
    <div class="p-internal-sidebar" style="width:300px; flex-shrink:0; position:sticky; top:100px;">
        <!-- 1. Real-time Search Box (Top) -->
        <div class="control-card" style="padding:15px; border-radius:20px; background:#fff; border:2px solid var(--control-primary-soft); margin-bottom:20px; box-shadow:0 12px 30px rgba(0,0,0,0.04);">
            <div style="position:relative;">
                <span class="dashicons dashicons-search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--control-primary); font-size: 18px;"></span>
                <input type="text" id="patient-search-input" placeholder="<?php _e('بحث شامل في السجلات...', 'control'); ?>" style="padding:12px 40px 12px 12px; border-radius:12px; border:1.5px solid #f1f5f9; width:100%; font-size: 0.9rem; background:#f8fafc; border-color:#e2e8f0; font-weight:700;">
            </div>
        </div>

        <div class="control-card" style="padding:10px; border-radius:20px; background:#fff; border:1px solid #f1f5f9; box-shadow:0 10px 30px rgba(0,0,0,0.02); overflow:hidden;">
            <!-- 2. Category Tabs (Vertical Navigation) -->
            <div style="padding:10px 15px 5px; color:var(--control-muted); font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">
                <?php _e('تصنيفات الملفات', 'control'); ?>
            </div>
            <div class="p-nav-item active" data-tab="tab-active" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                <div class="nav-icon-box" style="width:35px; height:35px; border-radius:10px; background:#f0fdf4; display:flex; align-items:center; justify-content:center; color:#166534;">
                    <span class="dashicons dashicons-id"></span>
                </div>
                <span style="font-weight:800; flex:1; font-size:0.9rem;"><?php _e('السجلات النشطة', 'control'); ?></span>
                <span class="nav-count-badge" style="background:#166534; color:#fff; font-size:0.65rem; padding:2px 8px; border-radius:10px;"><?php echo count($active_records); ?></span>
            </div>
            <div class="p-nav-item" data-tab="tab-evaluation" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                <div class="nav-icon-box" style="width:35px; height:35px; border-radius:10px; background:#fff7ed; display:flex; align-items:center; justify-content:center; color:#c2410c;">
                    <span class="dashicons dashicons-clipboard"></span>
                </div>
                <span style="font-weight:800; flex:1; font-size:0.9rem;"><?php _e('تقييم فقط', 'control'); ?></span>
                <span class="nav-count-badge" style="background:#c2410c; color:#fff; font-size:0.65rem; padding:2px 8px; border-radius:10px;"><?php echo count($evaluation_cases); ?></span>
            </div>
            <div class="p-nav-item" data-tab="tab-waiting" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:2px;">
                <div class="nav-icon-box" style="width:35px; height:35px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; color:#1d4ed8;">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <span style="font-weight:800; flex:1; font-size:0.9rem;"><?php _e('قائمة الانتظار', 'control'); ?></span>
                <span class="nav-count-badge" style="background:#1d4ed8; color:#fff; font-size:0.65rem; padding:2px 8px; border-radius:10px;"><?php echo count($waiting_cases); ?></span>
            </div>
            <div class="p-nav-item" data-tab="tab-closed" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-radius:15px; cursor:pointer; transition:0.3s; margin-bottom:10px;">
                <div class="nav-icon-box" style="width:35px; height:35px; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:#475569;">
                    <span class="dashicons dashicons-archive"></span>
                </div>
                <span style="font-weight:800; flex:1; font-size:0.9rem;"><?php _e('ملفات مغلقة', 'control'); ?></span>
                <span class="nav-count-badge" style="background:#475569; color:#fff; font-size:0.65rem; padding:2px 8px; border-radius:10px;"><?php echo count($closed_cases); ?></span>
            </div>

            <div style="height:1px; background:#f1f5f9; margin:5px 15px 15px;"></div>

            <!-- 3. Clinical Filters -->
            <div style="padding:0 15px 20px;" class="filters-wrapper">
                <div style="margin-bottom:15px;" class="filter-group">
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:var(--control-muted); margin-bottom:8px;"><?php _e('تصفية حسب الحالة', 'control'); ?></label>
                    <select id="patient-status-filter" class="p-sidebar-select" style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #f1f5f9; font-size: 0.85rem; font-weight: 600; background:#f8fafc; transition:0.3s;">
                        <option value=""><?php _e('كل الحالات التشغيلية', 'control'); ?></option>
                        <?php foreach($status_labels as $val => $label): ?>
                            <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:15px;" class="filter-group">
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:var(--control-muted); margin-bottom:8px;"><?php _e('التشخيص', 'control'); ?></label>
                    <select id="patient-diag-filter" class="p-sidebar-select" style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #f1f5f9; font-size: 0.85rem; font-weight: 600; background:#f8fafc; transition:0.3s;">
                        <option value=""><?php _e('كل التشخيصات', 'control'); ?></option>
                        <option value="autism">ASD</option>
                        <option value="adhd">ADHD</option>
                        <option value="speech">Speech Delay</option>
                        <option value="cp">Cerebral Palsy</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:var(--control-muted); margin-bottom:8px;"><?php _e('الأولوية', 'control'); ?></label>
                    <select id="patient-priority-filter" class="p-sidebar-select" style="width:100%; padding:12px; border-radius:12px; border:1.5px solid #f1f5f9; font-size: 0.85rem; font-weight: 600; background:#f8fafc; transition:0.3s;">
                        <option value=""><?php _e('كل الأولويات', 'control'); ?></option>
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 4. Summary Statistics (Bottom) -->
        <div class="p-sidebar-stats" style="margin-top:20px; padding:25px 20px; background:var(--control-primary); border-radius:24px; text-align:center; color:#fff; box-shadow:0 15px 35px var(--control-primary-soft);">
            <p style="margin:0; font-size:0.85rem; color:rgba(255,255,255,0.7); font-weight:700;"><?php _e('إجمالي الحالات بالمركز', 'control'); ?></p>
            <div style="font-size:2.8rem; font-weight:900; color:#fff; margin:5px 0;"><?php echo count($patients); ?></div>
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-top:15px; border-top:1px solid rgba(255,255,255,0.1); padding-top:15px;">
                <div>
                    <div style="font-size:0.9rem; font-weight:800;"><?php echo count($active_records); ?></div>
                    <div style="font-size:0.6rem; color:rgba(255,255,255,0.6);"><?php _e('نشطة', 'control'); ?></div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,0.1); border-right:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:0.9rem; font-weight:800;"><?php echo count($evaluation_cases); ?></div>
                    <div style="font-size:0.6rem; color:rgba(255,255,255,0.6);"><?php _e('تقييم', 'control'); ?></div>
                </div>
                <div>
                    <div style="font-size:0.9rem; font-weight:800;"><?php echo count($closed_cases); ?></div>
                    <div style="font-size:0.6rem; color:rgba(255,255,255,0.6);"><?php _e('مغلقة', 'control'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ( ! function_exists( 'render_patient_cards' ) ) {
function render_patient_cards($records, $status_labels, $can_manage) {
    if($records): foreach($records as $p):
        $dob = new DateTime($p->dob);
        $age_diff = $dob->diff(new DateTime());
        $age_str = sprintf(__('%d سنة، %d شهر', 'control'), $age_diff->y, $age_diff->m);
        $nat_label = Control_I18n::get_country_name($p->nationality ?: 'SA');
        $lang_label = ($p->preferred_lang === 'en') ? 'English' : 'Arabic';
    ?>
        <div class="control-card patient-card"
             data-status="<?php echo esc_attr($p->case_status); ?>"
             data-diag="<?php echo esc_attr(strtolower($p->initial_diagnosis)); ?>"
             data-priority="<?php echo esc_attr($p->priority_level ?: 'normal'); ?>"
             data-search="<?php echo esc_attr(strtolower($p->full_name . ' ' . $p->permanent_id . ' ' . $p->father_phone . ' ' . $p->initial_diagnosis)); ?>"
             style="padding:0; overflow:hidden; transition:0.3s; border:1px solid #f1f5f9; border-radius: 20px; position:relative;">

            <div style="padding:24px;">
                <div style="display:flex; gap:18px; align-items:flex-start; margin-bottom:20px;">
                    <div style="width:75px; height:75px; background:#f8fafc; border-radius:20px; overflow:hidden; border:2px solid #fff; box-shadow:0 10px 25px rgba(0,0,0,0.06); flex-shrink:0;">
                        <?php if($p->profile_photo): ?>
                            <img src="<?php echo esc_url($p->profile_photo); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f8fafc; color:#cbd5e1;">
                                <span class="dashicons dashicons-admin-users" style="font-size:45px; width:45px; height:45px;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <h3 style="margin:0 0 6px 0; font-size:1.15rem; font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color: var(--control-primary); letter-spacing:-0.2px;"><?php echo esc_html($p->full_name); ?></h3>
                        <div style="display:flex; gap:6px; align-items:center; flex-wrap: wrap; margin-bottom:8px;">
                            <span class="patient-status-badge status-<?php echo esc_attr($p->case_status); ?>" style="font-size:0.65rem; padding:3px 10px; border-radius:8px; font-weight:800; text-transform:uppercase;">
                                <?php echo $status_labels[$p->case_status] ?? $p->case_status; ?>
                            </span>
                            <span class="badge-pastel badge-age" style="padding:3px 10px; border-radius:8px; font-size:0.65rem; font-weight:800;">
                                <?php echo $age_str; ?>
                            </span>
                            <span class="badge-pastel badge-gender" style="padding:3px 10px; border-radius:8px; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                <?php echo $p->gender === 'male' ? __('ذكر', 'control') : __('أنثى', 'control'); ?>
                            </span>
                            <?php if(!empty($p->priority_level)): ?>
                                <span class="badge-pastel badge-priority priority-<?php echo esc_attr($p->priority_level); ?>" style="padding:3px 10px; border-radius:8px; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                    <?php
                                        $priorities = ['normal' => __('عادي', 'control'), 'urgent' => __('عاجل', 'control'), 'critical' => __('حرج', 'control')];
                                        echo $priorities[$p->priority_level] ?? $p->priority_level;
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span class="badge-pastel badge-nat" style="padding:2px 12px; border-radius:20px; font-size:0.65rem; font-weight:700;">
                                <?php echo esc_html($nat_label); ?>
                            </span>
                            <span style="color:var(--control-muted); font-size:0.65rem; font-weight:700; border:1px solid #e2e8f0; padding:1px 12px; border-radius:20px; background:#fff;">
                                <?php echo esc_html($lang_label); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="background:#f8fafc; padding:15px; border-radius:15px; margin-bottom: 5px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="flex:1;">
                            <span style="color:var(--control-muted); font-size:0.7rem; font-weight:700; display:block; margin-bottom:2px;"><?php _e('تشخيص الحالة', 'control'); ?></span>
                            <span class="badge-pastel badge-diagnosis" style="padding:2px 8px; border-radius:6px; font-size:0.75rem; font-weight:800; display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo esc_html($p->initial_diagnosis) ?: __('غير محدد', 'control'); ?>
                            </span>
                        </div>
                        <div style="width:1px; height:25px; background:#e2e8f0; margin:0 15px;"></div>
                        <div style="flex:1; text-align:left;">
                            <span style="color:var(--control-muted); font-size:0.7rem; font-weight:700; display:block; margin-bottom:2px;"><?php _e('رقم الملف', 'control'); ?></span>
                            <strong style="font-size: 0.8rem; font-family:monospace; letter-spacing:1px; display:block;">#<?php echo esc_html($p->permanent_id ?: $p->id); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:#fff; padding:15px 24px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <a href="<?php echo add_query_arg(array('control_view' => 'patient_view', 'id' => $p->id)); ?>" class="control-btn" style="flex: 1; padding:10px; font-size:0.8rem; background:#f8fafc; color:var(--control-primary) !important; border:1px solid #e2e8f0; font-weight:800; border-radius:12px; text-align:center;">
                    <?php echo Control_I18n::t('view_file'); ?>
                </a>

                <?php if($can_manage): ?>
                    <?php if($p->case_status === 'evaluation_only' || $p->case_status === 'closed' || $p->case_status === 'completed'): ?>
                        <button class="restore-patient-btn" data-id="<?php echo $p->id; ?>" style="background:var(--control-accent); color:var(--control-primary); border:none; padding:10px 15px; font-size:0.8rem; border-radius:12px; font-weight:800; cursor:pointer;" title="<?php _e('إعادة تنشيط الملف', 'control'); ?>">
                            <span class="dashicons dashicons-undo"></span>
                        </button>
                    <?php elseif(empty($p->permanent_id) || $p->case_status === 'waiting_list'): ?>
                        <a href="<?php echo add_query_arg(array('resume_id' => $p->id), get_permalink(get_page_by_path('kiosk-registration'))); ?>" class="control-btn" style="flex: 0.8; padding:10px; font-size:0.8rem; background:var(--control-primary); color:#fff !important; border:none; font-weight:800; border-radius:12px; text-align:center;">
                            <?php _e('إكمال الملف', 'control'); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($can_manage): ?>
                    <div style="display:flex; gap:5px;">
                        <?php if($p->case_status === 'active'): ?>
                            <button class="close-patient-btn" data-id="<?php echo $p->id; ?>" style="background:none; border:none; color:#94a3b8; cursor:pointer; transition:0.2s;" onmouseover="this.style.color='#ef4444'" title="<?php _e('إغلاق الملف', 'control'); ?>">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        <?php endif; ?>
                        <button class="delete-patient-btn" data-id="<?php echo $p->id; ?>" style="background:none; border:none; color:#cbd5e1; cursor:pointer; transition:0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div style="grid-column: 1 / -1; text-align:center; padding:80px 40px; background:#fff; border-radius:20px; border:2px dashed #e2e8f0;">
            <div style="width:100px; height:100px; background:#f8fafc; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; color:#cbd5e1;">
                <span class="dashicons dashicons-groups" style="font-size:50px; width:50px; height:50px;"></span>
            </div>
            <p style="color:var(--control-muted); font-size:1.2rem; font-weight:600;"><?php _e('لا توجد سجلات في هذا القسم حالياً.', 'control'); ?></p>
        </div>
    <?php endif;
}
}
?>

<div id="pediatric-toast" style="display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#1e293b; color:#fff; padding:12px 30px; border-radius:50px; z-index:100000; box-shadow:0 10px 30px rgba(0,0,0,0.2); font-weight:700;"></div>

<!-- Add Patient Modal Placeholder -->
<?php include CONTROL_PATH . 'templates/patient-forms.php'; ?>

<!-- System Confirmation Modal -->
<div id="system-confirm-modal" class="control-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100000; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="control-card" style="width:100%; max-width:400px; padding:30px; border-radius:24px; text-align:center;">
        <div style="width:70px; height:70px; background:#fef2f2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <span class="dashicons dashicons-warning" style="font-size:35px; width:35px; height:35px;"></span>
        </div>
        <h3 id="modal-confirm-title" style="margin:0 0 10px 0; font-weight:800; color:var(--control-primary);">---</h3>
        <p id="modal-confirm-msg" style="color:var(--control-muted); font-size:0.95rem; line-height:1.6; margin-bottom:25px;">---</p>
        <div style="display:flex; gap:12px;">
            <button id="btn-confirm-action" class="control-btn" style="flex:1; background:#1e293b; border:none; padding:12px; font-weight:800;"><?php _e('تأكيد', 'control'); ?></button>
            <button onclick="jQuery('#system-confirm-modal').fadeOut(200)" class="control-btn" style="flex:1; background:#f1f5f9; color:#475569 !important; border:none; padding:12px; font-weight:800;"><?php _e('إلغاء', 'control'); ?></button>
        </div>
    </div>
</div>

<style>
.p-nav-item.active { background: #1e293b; color: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
.p-nav-item.active .nav-icon-box { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
.p-nav-item.active .nav-count-badge { background: var(--control-accent) !important; color: #1e293b !important; }
.p-nav-item:not(.active):hover { background: #f8fafc; transform: translateX(-5px); }
.p-nav-item:active { transform: scale(0.98); }
.p-sidebar-select:focus { border-color: var(--control-primary); background: #fff; box-shadow: 0 0 0 4px var(--control-primary-soft); }
.p-sidebar-select option:checked { background: #1e293b; color: #fff; }

.patient-status-badge.status-active { background: #dcfce7; color: #15803d; }
.patient-status-badge.status-waiting_list { background: #dbeafe; color: #1e40af; }
.patient-status-badge.status-dropped_out { background: #fee2e2; color: #b91c1c; }
.patient-status-badge.status-completed { background: #f0fdf4; color: #166534; }
.patient-status-badge.status-closed { background: #f1f5f9; color: #475569; }
.patient-status-badge.status-evaluation_only { background: #fef3c7; color: #92400e; }

.badge-pastel { border: 1px solid rgba(0,0,0,0.03); }
.badge-age { background: #f3e8ff; color: #7e22ce; }
.badge-gender { background: #e0f2fe; color: #0369a1; }
.badge-nat { background: #fdf2f8; color: #be185d; }
.badge-diagnosis { background: #fff1f2; color: #e11d48; }
.badge-priority.priority-normal { background: #f0fdf4; color: #166534; }
.badge-priority.priority-urgent { background: #fff7ed; color: #9a3412; }
.badge-priority.priority-critical { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }

.patient-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.08); border-color: var(--control-primary); }

@media (max-width: 1024px) {
    .pediatric-records-layout { flex-direction: column-reverse; }
    .p-internal-sidebar { width: 100% !important; position: static !important; margin-bottom: 30px; }
    .p-internal-sidebar > .control-card { display: block; }
    .p-nav-item { margin-bottom: 5px !important; }
    .p-internal-sidebar .filters-wrapper { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
}

@media (max-width: 768px) {
    .p-internal-sidebar .filters-wrapper { grid-template-columns: 1fr; }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.p-nav-item').on('click', function() {
        const targetTab = $(this).data('tab');
        $('.p-nav-item').removeClass('active');
        $(this).addClass('active');
        $('.p-tab-pane').hide();
        $('#' + targetTab).fadeIn(300, function() {
            applyFilters(); // Context-aware search: only filters active tab
        });
    });

    $(document).on('click', '.restore-patient-btn', function() {
        const id = $(this).data('id');
        if(!confirm('<?php _e("هل أنت متأكد من إعادة تنشيط ملف هذا الطفل ونقله للسجلات النشطة؟", "control"); ?>')) return;

        $.post(control_ajax.ajax_url, {
            action: 'control_restore_patient',
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                showToast('<?php _e("تمت استعادة الملف بنجاح.", "control"); ?>');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(res.data);
            }
        });
    });

    function applyFilters() {
        const query = $('#patient-search-input').val().toLowerCase();
        const status = $('#patient-status-filter').val();
        const diag = $('#patient-diag-filter').val().toLowerCase();
        const priority = $('#patient-priority-filter').val();
        const activeTabId = $('.p-nav-item.active').data('tab');

        $(`#${activeTabId} .patient-card`).each(function() {
            const card = $(this);
            const searchVal = card.data('search');
            const cardStatus = card.data('status');
            const cardDiag = card.data('diag');
            const cardPriority = card.data('priority');

            const matchesSearch = !query || searchVal.includes(query);
            const matchesStatus = !status || cardStatus === status;
            const matchesDiag = !diag || cardDiag.includes(diag);
            const matchesPriority = !priority || cardPriority === priority;

            if (matchesSearch && matchesStatus && matchesDiag && matchesPriority) {
                card.fadeIn(200);
            } else {
                card.hide();
            }
        });
    }

    function updateSelectStyling() {
        $('.p-sidebar-select').each(function() {
            if ($(this).val()) {
                $(this).addClass('is-active').css({ 'background': '#1e293b', 'color': '#fff', 'border-color': '#1e293b' });
            } else {
                $(this).removeClass('is-active').css({ 'background': '#f8fafc', 'color': 'inherit', 'border-color': '#f1f5f9' });
            }
        });
    }

    $('#patient-search-input, #patient-status-filter, #patient-diag-filter, #patient-priority-filter').on('keyup change', function() {
        applyFilters();
        if ($(this).hasClass('p-sidebar-select')) updateSelectStyling();
    });

    function showToast(message) {
        $('#pediatric-toast').text(message).fadeIn().delay(3000).fadeOut();
    }

    // Internal System Modals for Actions
    let pendingAction = null;
    let pendingId = null;

    window.openSystemModal = function(type, id) {
        pendingId = id;
        pendingAction = type;
        const $modal = $('#system-confirm-modal');
        const $title = $('#modal-confirm-title');
        const $msg = $('#modal-confirm-msg');

        if (type === 'delete') {
            $title.text('<?php _e("تأكيد الحذف النهائي", "control"); ?>');
            $msg.text('<?php _e("هل أنت متأكد من حذف سجل هذا الطفل نهائياً؟ سيتم مسح كافة البيانات السريرية والمالية.", "control"); ?>');
        } else if (type === 'close') {
            $title.text('<?php echo Control_I18n::t("confirm_closure_title"); ?>');
            $msg.text('<?php echo Control_I18n::t("confirm_closure_msg"); ?>');
        }
        $modal.css('display', 'flex').hide().fadeIn(200);
    }

    $('#btn-confirm-action').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        const action = pendingAction === 'delete' ? 'control_delete_patient' : 'control_close_patient';

        $.post(control_ajax.ajax_url, {
            action: action,
            id: pendingId,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                showToast(pendingAction === 'delete' ? '<?php _e("تم الحذف بنجاح.", "control"); ?>' : '<?php _e("تم إغلاق الملف بنجاح.", "control"); ?>');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('<?php _e("تأكيد", "control"); ?>');
            }
        });
    });

    $(document).on('click', '.delete-patient-btn', function() {
        openSystemModal('delete', $(this).data('id'));
    });

    $(document).on('click', '.close-patient-btn', function() {
        openSystemModal('close', $(this).data('id'));
    });

    $('#add-patient-btn').on('click', function() {
        openPatientModal();
    });

    $('.process-intake-btn').on('click', function() {
        const id = $(this).data('id');
        // Fetch full record and open modal at step 5
        $.post(control_ajax.ajax_url, {
            action: 'control_get_patient', // This action needs to be verified/added
            id: id,
            nonce: control_ajax.nonce
        }, function(res) {
            if(res.success) {
                openPatientModal(res.data);
                // Force jump to step 5
                jQuery('.wiz-step').hide();
                jQuery('#wiz-step-5').show();
                currentWizStep = 5;
                window.updateWizUI();
            }
        });
    });

    $('.reject-intake-btn').on('click', function() {
        const id = $(this).data('id');
        if(!confirm('<?php _e('هل أنت متأكد من رفض هذا الطلب؟ سيتم نقله للأرشيف.', 'control'); ?>')) return;
        $.post(control_ajax.ajax_url, { action: 'control_update_intake_status', id: id, status: 'rejected', nonce: control_ajax.nonce }, () => location.reload());
    });
});
</script>

<style>
.patient-status-badge.status-active { background: #ecfdf5; color: #059669; }
.patient-status-badge.status-waiting_list { background: #eff6ff; color: #1d4ed8; }
.patient-status-badge.status-dropped_out { background: #fef2f2; color: #ef4444; }
.patient-status-badge.status-completed { background: #f0fdf4; color: #166534; }
.patient-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); border-color: var(--control-primary); }
</style>
</div>
