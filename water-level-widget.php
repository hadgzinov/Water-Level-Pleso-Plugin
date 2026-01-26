<?php
// Получаем данные из настроек WordPress
$current_level = get_option('water_level_current', '92.90');
$post_name = get_option('water_level_post_name', 'КП "ПЛЕСО"');
$water_body = get_option('water_level_water_body', 'Русанівська протока');
$normal_level = get_option('water_level_normal', '91.50');
$coordinates = get_option('water_level_coordinates', '50.454078,30.583940');
$last_update = get_option('water_level_last_update', current_time('mysql'));

// Вычисляем отклонение
$deviation = floatval($current_level) - floatval($normal_level);

// Определяем статус
$status = 'normal';
$status_text = 'НОРМАЛЬНИЙ';
if ($current_level >= 92.70) {
    $status = 'danger';
    $status_text = 'КРИТИЧНИЙ';
} elseif ($current_level >= 91.50) {
    $status = 'warning';
    $status_text = 'ПІДВИЩЕНИЙ';
}
?>

<div class="water-level-dashboard" id="water-level-dashboard">
    <div class="header">
        <div class="status-indicator status-<?php echo $status; ?>" id="statusIndicator"><?php echo $status_text; ?></div>
    </div>

    <div class="current-level <?php echo $status; ?>" id="currentLevelPanel">
        <div>Поточний рівень води</div>
        <div class="value" id="currentLevelDisplay"><?php echo $current_level; ?></div>
        <div>метри</div>
    </div>

    <div class="main-content">
        <div class="gauge-section">
            <div class="gauge-labels">
                <div class="level-label">94.00</div>
                <div class="level-label">92.70</div>
                <div class="level-label normal">91.50</div>
                <div class="level-label">91.00</div>
            </div>
            
            <div class="gauge-wrapper">
                <div class="gauge">
                    <div class="water-fill <?php echo $status; ?>" id="waterFill"></div>
                    <div class="gauge-markings">
                        <div class="gauge-mark" style="bottom: 75%;"></div>
                        <div class="gauge-mark" style="bottom: 50%;"></div>
                        <div class="gauge-mark normal" style="bottom: 25%;"></div>
                        <div class="gauge-mark" style="bottom: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-panel">
            <div class="info-item">
                <span class="info-label">📍 Пост</span>
                <span class="info-value" id="postName"><?php echo $post_name; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">📅 Дата</span>
                <span class="info-value" id="currentDate"><?php echo date('d.m.Y'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">⏰ Час</span>
                <span class="info-value" id="currentTime"><?php echo date('H:i'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">🏞️ Водойма</span>
                <span class="info-value" id="waterBody"><?php echo $water_body; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">📊 Норма</span>
                <span class="info-value" id="normalLevel"><?php echo $normal_level; ?> м</span>
            </div>
            <div class="info-item">
                <span class="info-label">📈 Відхилення</span>
                <span class="info-value deviation-<?php echo ($deviation >= 0) ? 'positive' : 'negative'; ?>" id="deviation">
                    <?php echo ($deviation >= 0 ? '+' : '') . number_format($deviation, 2); ?> м
                </span>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>Оновлено: <span id="lastUpdate"><?php echo date('d.m H:i', strtotime($last_update)); ?></span></div>
    </div>
</div>

<style>
.water-level-dashboard {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    width: 370px;
    height: 400px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
}

.water-level-dashboard * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ЗАГОЛОВОК */
.water-level-dashboard .header {
    text-align: center;
    margin-bottom: 6px;
    flex-shrink: 0;
}

.water-level-dashboard .header h1 {
    color: #2c3e50;
    margin: 0 0 4px 0;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.2;
}

.water-level-dashboard .status-indicator {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    font-size: 13px;
}

.water-level-dashboard .status-normal { 
    background: linear-gradient(45deg, #4CAF50, #66BB6A); 
}
.water-level-dashboard .status-warning { 
    background: linear-gradient(45deg, #FF9800, #FFB74D);
    animation: dangerPulse 3s infinite;
}
.water-level-dashboard .status-danger { 
    background: linear-gradient(45deg, #F44336, #EF5350); 
    animation: dangerPulse 0.5s infinite;
}

@keyframes dangerPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.9; }
}

/* ТЕКУЩИЙ УРОВЕНЬ */
.water-level-dashboard .current-level {
    text-align: center;
    color: white;
    padding: 6px;
    border-radius: 8px;
    margin-bottom: 6px;
    flex-shrink: 0;
}

.water-level-dashboard .current-level.normal {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
}

.water-level-dashboard .current-level.warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    box-shadow: 0 2px 8px rgba(243, 156, 18, 0.3);
}

.water-level-dashboard .current-level.danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.water-level-dashboard .current-level .value {
    font-size: 22px;
    font-weight: 600;
    margin: 2px 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.water-level-dashboard .current-level div {
    font-size: 11px;
    line-height: 1.1;
}

/* ОСНОВНОЙ КОНТЕНТ */
.water-level-dashboard .main-content {
    display: flex;
    gap: 30px;
    flex: 1;
    min-height: 0;
    flex-direction: row-reverse; /* Меняем местами: текстовый блок слева, шкала справа */
}

/* СЕКЦИЯ ДАТЧИКА */
.water-level-dashboard .gauge-section {
    display: flex;
    align-items: center;
    margin-right: 10px;
    gap: 3px;
    flex-shrink: 0;
}

.water-level-dashboard .gauge-labels {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    height: 220px;
    width: 40px;
    gap: 23px;
}

.water-level-dashboard .level-label {
    background: rgba(255, 255, 255, 0.9);
    color: #2c3e50;
    padding: 1px 2px;
    border-radius: 3px;
    border: 1px solid #bdc3c7;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    transform: translateY(-2px);
}

.water-level-dashboard .level-label.normal {
    background: #27ae60;
    color: white;
    border-color: #27ae60;
}

.water-level-dashboard .gauge-wrapper {
    position: relative;
    display: flex;
    justify-content: center;
}

.water-level-dashboard .gauge {
    width: 45px;
    height: 220px;
    background: linear-gradient(to bottom, #ecf0f1, #bdc3c7);
    border-radius: 4px;
    position: relative;
    overflow: hidden;
    box-shadow: 
        inset 0 0 6px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.15);
    border: 2px solid #34495e;
}

.water-level-dashboard .gauge-markings {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 2;
}

.water-level-dashboard .gauge-mark {
    position: absolute;
    left: 2px;
    right: 2px;
    height: 1px;
    background: #2c3e50;
}

.water-level-dashboard .gauge-mark.normal {
    background: #27ae60;
    height: 2px;
}

.water-level-dashboard .water-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    min-height: 10px;
    transition: height 1s ease-out;
    z-index: 1;
}

.water-level-dashboard .water-fill.normal {
    background: linear-gradient(0deg, #2ecc71 0%, #27ae60 100%);
    box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.1);
}

.water-level-dashboard .water-fill.warning {
    background: linear-gradient(0deg, #e67e22 0%, #f39c12 100%);
    box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.1);
}

.water-level-dashboard .water-fill.danger {
    background: linear-gradient(0deg, #c0392b 0%, #e74c3c 100%);
    box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.1);
}

/* ИНФОРМАЦИОННАЯ ПАНЕЛЬ */
.water-level-dashboard .info-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    min-width: 0;
    margin-left: 10px;
    padding: 0 2px;
    margin-top: 25px;
}

.water-level-dashboard .info-item {
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.4);
    padding: 2px 4px;
    border-radius: 3px;
    margin-bottom: 10px !important;
}

.water-level-dashboard .info-label {
    font-size: 9px;
    color: #7f8c8d;
    font-weight: 500;
    line-height: 0.9;
    margin-bottom: 1px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.water-level-dashboard .info-value {
    font-size: 12px;
    color: #2c3e50;
    font-weight: 600;
    line-height: 0.9;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.water-level-dashboard .deviation-positive {
    color: #e74c3c;
}

.water-level-dashboard .deviation-negative {
    color: #27ae60;
}

/* ПОДВАЛ */
.water-level-dashboard .footer {
    text-align: center;
    margin-top: 4px;
    flex-shrink: 0;
}

.water-level-dashboard .footer div {
    font-size: 11px;
    color: #7f8c8d;
    line-height: 1;
    margin: 1px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Функция для обновления визуального индикатора уровня воды
    function updateWaterLevel() {
        var currentLevel = parseFloat($('#currentLevelDisplay').text());
        
        // Определяем уровни меток на шкале
        var levels = [91.00, 91.50, 92.70, 94.00];
        var percentage = 0;
        
        if (currentLevel <= levels[0]) {
            // Ниже минимального уровня - 0%
            percentage = 0;
        } else if (currentLevel <= levels[1]) {
            // От 91.00 до 91.50 - заполнение от 0% до 25%
            var progress = (currentLevel - levels[0]) / (levels[1] - levels[0]);
            percentage = progress * 25;
        } else if (currentLevel <= levels[2]) {
            // От 91.50 до 92.70 - заполнение от 25% до 50%
            var progress = (currentLevel - levels[1]) / (levels[2] - levels[1]);
            percentage = 25 + (progress * 25);
        } else if (currentLevel <= levels[3]) {
            // От 92.70 до 94.00 - заполнение от 50% до 75%
            var progress = (currentLevel - levels[2]) / (levels[3] - levels[2]);
            percentage = 50 + (progress * 25);
        } else {
            // Выше 94.00 - заполнение от 75% до 100%
            // Считаем что максимум шкалы это 95.00 (можете изменить по необходимости)
            var maxScaleLevel = 95.00;
            if (currentLevel >= maxScaleLevel) {
                percentage = 100;
            } else {
                var progress = (currentLevel - levels[3]) / (maxScaleLevel - levels[3]);
                percentage = 75 + (progress * 25);
            }
        }
        
        // Ограничиваем значение от 0 до 100
        percentage = Math.max(0, Math.min(100, percentage));
        
        $('#waterFill').css('height', percentage + '%');
        
        updateTime();
    }
    
    // Функция для обновления времени
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
    
    // Функция для обновления данных с сервера
    function refreshData() {
        $.ajax({
            url: water_level_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_water_level'
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    $('#currentLevelDisplay').text(data.level);
                    $('#postName').text(data.post_name);
                    $('#waterBody').text(data.water_body);
                    $('#normalLevel').text(data.normal_level + ' м');
                    
                    var deviation = parseFloat(data.level) - parseFloat(data.normal_level);
                    var deviationText = (deviation >= 0 ? '+' : '') + deviation.toFixed(2) + ' м';
                    $('#deviation').text(deviationText);
                    
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
                        .removeClass('status-normal status-warning status-danger')
                        .addClass('status-' + status)
                        .text(statusText);
                    
                    $('#currentLevelPanel')
                        .removeClass('normal warning danger')
                        .addClass(status);
                    
                    $('#waterFill')
                        .removeClass('normal warning danger')
                        .addClass(status);
                    
                    $('#deviation')
                        .removeClass('deviation-positive deviation-negative')
                        .addClass(deviation >= 0 ? 'deviation-positive' : 'deviation-negative');
                    
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
                    
                    updateWaterLevel();
                }
            }
        });
    }
    
    updateWaterLevel();
    setInterval(updateTime, 60000);
    setInterval(refreshData, 300000);
    refreshData();
});
</script>