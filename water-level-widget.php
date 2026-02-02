<?php
// Получаем данные из настроек WordPress
$current_level = get_option('water_level_current', '92.90');
$post_name = get_option('water_level_post_name', 'КП "ПЛЕСО"');
$water_body = get_option('water_level_water_body', 'Русанівська протока');
$normal_level = get_option('water_level_normal', '91.50');
$coordinates = get_option('water_level_coordinates', '50.454078,30.583940');
$ice_mode = get_option('water_level_ice_mode', '0');
$last_update = get_option('water_level_last_update', current_time('mysql'));

// Вычисляем отклонение
$deviation = floatval($current_level) - floatval($normal_level);

// Определяем статус
$status = 'normal';
$status_text = 'НОРМАЛЬНИЙ';

if ($ice_mode === '1') {
    $status = 'ice';
    $status_text = 'ЛЬОДОСТАВ';
} elseif ($current_level >= 92.70) {
    $status = 'danger';
    $status_text = 'КРИТИЧНИЙ';
} elseif ($current_level >= 91.50) {
    $status = 'warning';
    $status_text = 'ПІДВИЩЕНИЙ';
}
?>

<div class="water-level-dashboard" id="water-level-dashboard" data-ice-mode="<?php echo $ice_mode; ?>">
    <div class="wld-header">
        <div class="wld-status-indicator wld-status-<?php echo $status; ?>" id="statusIndicator">
            <?php echo ($ice_mode === '1') ? '❄️ ' : ''; ?><?php echo $status_text; ?>
        </div>
    </div>

    <div class="wld-current-level <?php echo $status; ?>" id="currentLevelPanel">
        <div class="wld-current-label">Поточний рівень води</div>
        <?php if ($ice_mode === '1'): ?>
            <div class="wld-value wld-ice-text" id="currentLevelDisplay">❄️ Льодостав</div>
        <?php else: ?>
            <div class="wld-value" id="currentLevelDisplay"><?php echo $current_level; ?></div>
            <div class="wld-unit">метри</div>
        <?php endif; ?>
    </div>

    <div class="wld-main-content">
        <div class="wld-gauge-section">
            <div class="wld-gauge-labels">
                <div class="wld-level-label">94.00</div>
                <div class="wld-level-label">92.70</div>
                <div class="wld-level-label wld-normal">91.50</div>
                <div class="wld-level-label">91.00</div>
            </div>

            <div class="wld-gauge-wrapper">
                <div class="wld-gauge">
                    <?php if ($ice_mode === '1'): ?>
                        <div class="wld-ice-fill" id="waterFill">
                            <div class="wld-ice-pattern"></div>
                        </div>
                    <?php else: ?>
                        <div class="wld-water-fill <?php echo $status; ?>" id="waterFill"></div>
                    <?php endif; ?>
                    <div class="wld-gauge-markings">
                        <div class="wld-gauge-mark" style="bottom: 75%;"></div>
                        <div class="wld-gauge-mark" style="bottom: 50%;"></div>
                        <div class="wld-gauge-mark wld-normal" style="bottom: 25%;"></div>
                        <div class="wld-gauge-mark" style="bottom: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wld-info-panel">
            <div class="wld-info-item">
                <span class="wld-info-label">📍 Пост</span>
                <span class="wld-info-value" id="postName"><?php echo esc_html($post_name); ?></span>
            </div>
            <div class="wld-info-item">
                <span class="wld-info-label">📅 Дата</span>
                <span class="wld-info-value" id="currentDate"><?php echo date('d.m.Y'); ?></span>
            </div>
            <div class="wld-info-item">
                <span class="wld-info-label">⏰ Час</span>
                <span class="wld-info-value" id="currentTime"><?php echo date('H:i'); ?></span>
            </div>
            <div class="wld-info-item">
                <span class="wld-info-label">🏞️ Водойма</span>
                <span class="wld-info-value" id="waterBody"><?php echo esc_html($water_body); ?></span>
            </div>
            <?php if ($ice_mode !== '1'): ?>
                <div class="wld-info-item">
                    <span class="wld-info-label">📊 Норма</span>
                    <span class="wld-info-value" id="normalLevel"><?php echo $normal_level; ?> м</span>
                </div>
                <div class="wld-info-item">
                    <span class="wld-info-label">📈 Відхилення</span>
                    <span class="wld-info-value wld-deviation-<?php echo ($deviation >= 0) ? 'positive' : 'negative'; ?>" id="deviation">
                        <?php echo ($deviation >= 0 ? '+' : '') . number_format($deviation, 2); ?> м
                    </span>
                </div>
            <?php else: ?>
                <div class="wld-info-item wld-ice-info">
                    <span class="wld-info-label">❄️ Стан</span>
                    <span class="wld-info-value wld-ice-status">Льодовий покрив</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="wld-footer">
        <div>Оновлено: <span id="lastUpdate"><?php echo date('d.m H:i', strtotime($last_update)); ?></span></div>
    </div>
</div>

<style>
/* Reset and base */
.water-level-dashboard {
    --wld-primary: #2c3e50;
    --wld-success: #27ae60;
    --wld-warning: #f39c12;
    --wld-danger: #e74c3c;
    --wld-ice: #3498db;
    --wld-text-muted: #7f8c8d;

    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 400px;
    min-width: 280px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
}

.water-level-dashboard *,
.water-level-dashboard *::before,
.water-level-dashboard *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* Header */
.water-level-dashboard .wld-header {
    text-align: center;
    margin-bottom: 10px;
    flex-shrink: 0;
}

.water-level-dashboard .wld-status-indicator {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    color: white;
    font-weight: 600;
    font-size: clamp(11px, 3vw, 14px);
    letter-spacing: 0.5px;
}

.water-level-dashboard .wld-status-normal {
    background: linear-gradient(45deg, #4CAF50, #66BB6A);
}
.water-level-dashboard .wld-status-warning {
    background: linear-gradient(45deg, #FF9800, #FFB74D);
    animation: wldPulse 3s infinite;
}
.water-level-dashboard .wld-status-danger {
    background: linear-gradient(45deg, #F44336, #EF5350);
    animation: wldPulse 0.5s infinite;
}
.water-level-dashboard .wld-status-ice {
    background: linear-gradient(45deg, #2980b9, #3498db);
    animation: wldIcePulse 2s infinite;
}

@keyframes wldPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.9; }
}

@keyframes wldIcePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}

/* Current level panel */
.water-level-dashboard .wld-current-level {
    text-align: center;
    color: white;
    padding: 10px 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    flex-shrink: 0;
}

.water-level-dashboard .wld-current-level.normal {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    box-shadow: 0 3px 10px rgba(46, 204, 113, 0.3);
}

.water-level-dashboard .wld-current-level.warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
}

.water-level-dashboard .wld-current-level.danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    box-shadow: 0 3px 10px rgba(231, 76, 60, 0.3);
}

.water-level-dashboard .wld-current-level.ice {
    background: linear-gradient(135deg, #2980b9, #3498db);
    box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
}

.water-level-dashboard .wld-current-label {
    font-size: clamp(10px, 2.5vw, 12px);
    opacity: 0.9;
}

.water-level-dashboard .wld-value {
    font-size: clamp(22px, 6vw, 28px);
    font-weight: 700;
    margin: 4px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.water-level-dashboard .wld-ice-text {
    font-size: clamp(18px, 5vw, 22px);
}

.water-level-dashboard .wld-unit {
    font-size: clamp(10px, 2.5vw, 12px);
    opacity: 0.9;
}

/* Main content */
.water-level-dashboard .wld-main-content {
    display: flex;
    gap: 15px;
    flex: 1;
    min-height: 0;
}

/* Gauge section */
.water-level-dashboard .wld-gauge-section {
    display: flex;
    align-items: stretch;
    gap: 5px;
    flex-shrink: 0;
}

.water-level-dashboard .wld-gauge-labels {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 180px;
    padding: 5px 0;
}

.water-level-dashboard .wld-level-label {
    background: rgba(255, 255, 255, 0.9);
    color: var(--wld-primary);
    padding: 2px 4px;
    border-radius: 4px;
    border: 1px solid #bdc3c7;
    text-align: center;
    font-size: clamp(9px, 2.5vw, 11px);
    font-weight: 600;
    white-space: nowrap;
}

.water-level-dashboard .wld-level-label.wld-normal {
    background: var(--wld-success);
    color: white;
    border-color: var(--wld-success);
}

.water-level-dashboard .wld-gauge-wrapper {
    position: relative;
    display: flex;
    justify-content: center;
}

.water-level-dashboard .wld-gauge {
    width: clamp(35px, 10vw, 50px);
    height: 180px;
    background: linear-gradient(to bottom, #ecf0f1, #bdc3c7);
    border-radius: 6px;
    position: relative;
    overflow: hidden;
    box-shadow:
        inset 0 0 8px rgba(0, 0, 0, 0.1),
        0 3px 10px rgba(0, 0, 0, 0.15);
    border: 2px solid #34495e;
}

.water-level-dashboard .wld-gauge-markings {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 2;
}

.water-level-dashboard .wld-gauge-mark {
    position: absolute;
    left: 3px;
    right: 3px;
    height: 1px;
    background: var(--wld-primary);
}

.water-level-dashboard .wld-gauge-mark.wld-normal {
    background: var(--wld-success);
    height: 2px;
}

.water-level-dashboard .wld-water-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    min-height: 10px;
    transition: height 1s ease-out;
    z-index: 1;
}

.water-level-dashboard .wld-water-fill.normal {
    background: linear-gradient(0deg, #2ecc71 0%, #27ae60 100%);
}

.water-level-dashboard .wld-water-fill.warning {
    background: linear-gradient(0deg, #e67e22 0%, #f39c12 100%);
}

.water-level-dashboard .wld-water-fill.danger {
    background: linear-gradient(0deg, #c0392b 0%, #e74c3c 100%);
}

/* Ice fill */
.water-level-dashboard .wld-ice-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, #a8d5e5 0%, #74b9d1 50%, #5dade2 100%);
    z-index: 1;
}

.water-level-dashboard .wld-ice-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 5px,
            rgba(255, 255, 255, 0.3) 5px,
            rgba(255, 255, 255, 0.3) 10px
        ),
        repeating-linear-gradient(
            -45deg,
            transparent,
            transparent 5px,
            rgba(255, 255, 255, 0.2) 5px,
            rgba(255, 255, 255, 0.2) 10px
        );
}

/* Info panel */
.water-level-dashboard .wld-info-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 8px;
    min-width: 0;
    padding-top: 5px;
}

.water-level-dashboard .wld-info-item {
    display: flex;
    flex-direction: column;
    background: rgba(236, 240, 241, 0.5);
    padding: 6px 8px;
    border-radius: 6px;
}

.water-level-dashboard .wld-info-label {
    font-size: clamp(8px, 2vw, 10px);
    color: var(--wld-text-muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.water-level-dashboard .wld-info-value {
    font-size: clamp(11px, 3vw, 13px);
    color: var(--wld-primary);
    font-weight: 600;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.water-level-dashboard .wld-deviation-positive {
    color: var(--wld-danger);
}

.water-level-dashboard .wld-deviation-negative {
    color: var(--wld-success);
}

.water-level-dashboard .wld-ice-status {
    color: var(--wld-ice);
}

/* Footer */
.water-level-dashboard .wld-footer {
    text-align: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    flex-shrink: 0;
}

.water-level-dashboard .wld-footer div {
    font-size: clamp(9px, 2.5vw, 11px);
    color: var(--wld-text-muted);
}

/* Responsive adjustments */
@media (max-width: 360px) {
    .water-level-dashboard {
        padding: 8px;
    }

    .water-level-dashboard .wld-main-content {
        gap: 10px;
    }

    .water-level-dashboard .wld-gauge-labels {
        height: 150px;
    }

    .water-level-dashboard .wld-gauge {
        height: 150px;
        width: 30px;
    }

    .water-level-dashboard .wld-info-item {
        padding: 4px 6px;
    }
}

@media (min-width: 400px) {
    .water-level-dashboard .wld-gauge-labels {
        height: 200px;
    }

    .water-level-dashboard .wld-gauge {
        height: 200px;
    }
}

@media (min-width: 500px) {
    .water-level-dashboard {
        max-width: 450px;
        padding: 15px;
    }

    .water-level-dashboard .wld-gauge-labels {
        height: 220px;
    }

    .water-level-dashboard .wld-gauge {
        height: 220px;
        width: 55px;
    }

    .water-level-dashboard .wld-main-content {
        gap: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    var $dashboard = $('#water-level-dashboard');
    var isIceMode = $dashboard.data('ice-mode') === 1 || $dashboard.data('ice-mode') === '1';

    function updateWaterLevel() {
        if (isIceMode) {
            return; // Ice mode - gauge is fully filled with ice pattern
        }

        var currentLevel = parseFloat($('#currentLevelDisplay').text());
        if (isNaN(currentLevel)) return;

        var levels = [91.00, 91.50, 92.70, 94.00];
        var percentage = 0;

        if (currentLevel <= levels[0]) {
            percentage = 0;
        } else if (currentLevel <= levels[1]) {
            percentage = ((currentLevel - levels[0]) / (levels[1] - levels[0])) * 25;
        } else if (currentLevel <= levels[2]) {
            percentage = 25 + ((currentLevel - levels[1]) / (levels[2] - levels[1])) * 25;
        } else if (currentLevel <= levels[3]) {
            percentage = 50 + ((currentLevel - levels[2]) / (levels[3] - levels[2])) * 25;
        } else {
            var maxScaleLevel = 95.00;
            if (currentLevel >= maxScaleLevel) {
                percentage = 100;
            } else {
                percentage = 75 + ((currentLevel - levels[3]) / (maxScaleLevel - levels[3])) * 25;
            }
        }

        percentage = Math.max(0, Math.min(100, percentage));
        $('#waterFill').css('height', percentage + '%');
    }

    function updateTime() {
        var now = new Date();
        var date = now.toLocaleDateString('uk-UA', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        var time = now.toLocaleTimeString('uk-UA', {
            hour: '2-digit',
            minute: '2-digit'
        });

        $('#currentDate').text(date);
        $('#currentTime').text(time);
    }

    function refreshData() {
        if (typeof water_level_ajax === 'undefined') return;

        $.ajax({
            url: water_level_ajax.ajax_url,
            type: 'POST',
            data: { action: 'get_water_level' },
            success: function(response) {
                if (!response.success) return;

                var data = response.data;
                var newIceMode = data.ice_mode === '1' || data.ice_mode === 1;

                // If ice mode changed, reload the page for proper display
                if (newIceMode !== isIceMode) {
                    location.reload();
                    return;
                }

                if (isIceMode) {
                    // Ice mode - just update basic info
                    $('#postName').text(data.post_name);
                    $('#waterBody').text(data.water_body);
                } else {
                    // Normal mode - update all values
                    $('#currentLevelDisplay').text(data.level);
                    $('#postName').text(data.post_name);
                    $('#waterBody').text(data.water_body);
                    $('#normalLevel').text(data.normal_level + ' м');

                    var deviation = parseFloat(data.level) - parseFloat(data.normal_level);
                    var deviationText = (deviation >= 0 ? '+' : '') + deviation.toFixed(2) + ' м';
                    $('#deviation').text(deviationText)
                        .removeClass('wld-deviation-positive wld-deviation-negative')
                        .addClass(deviation >= 0 ? 'wld-deviation-positive' : 'wld-deviation-negative');

                    var status = 'normal';
                    var statusText = 'НОРМАЛЬНИЙ';

                    if (data.level >= 92.70) {
                        status = 'danger';
                        statusText = 'КРИТИЧНИЙ';
                    } else if (data.level >= 91.50) {
                        status = 'warning';
                        statusText = 'ПІДВИЩЕНИЙ';
                    }

                    $('#statusIndicator')
                        .removeClass('wld-status-normal wld-status-warning wld-status-danger wld-status-ice')
                        .addClass('wld-status-' + status)
                        .text(statusText);

                    $('#currentLevelPanel')
                        .removeClass('normal warning danger ice')
                        .addClass(status);

                    $('#waterFill')
                        .removeClass('normal warning danger')
                        .addClass(status);

                    updateWaterLevel();
                }

                if (data.last_update) {
                    var updateDate = new Date(data.last_update);
                    var shortDate = updateDate.toLocaleDateString('uk-UA', {
                        day: '2-digit',
                        month: '2-digit'
                    }) + ' ' + updateDate.toLocaleTimeString('uk-UA', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    $('#lastUpdate').text(shortDate);
                }
            }
        });
    }

    // Initialize
    updateWaterLevel();
    updateTime();

    // Update time every minute
    setInterval(updateTime, 60000);

    // Refresh data every 5 minutes
    setInterval(refreshData, 300000);

    // Initial data refresh
    refreshData();
});
</script>
