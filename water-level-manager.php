<?php
/**
 * Plugin Name: Управление уровнем воды
 * Plugin URI: 
 * Description: Плагин для управления показателями уровня воды через админ-панель WordPress
 * Version: 1.0
 * Author: Your Name
 */

// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

class WaterLevelManager {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_update_water_level', array($this, 'update_water_level'));
        add_action('wp_ajax_get_water_level', array($this, 'get_water_level'));
        add_action('wp_ajax_nopriv_get_water_level', array($this, 'get_water_level'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('water_level_display', array($this, 'display_water_level'));
    }
    
    // Добавляем пункт меню в админку
    public function add_admin_menu() {
        add_menu_page(
            'Управление уровнем воды',
            'Уровень воды',
            'manage_options',
            'water-level-manager',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            30
        );
    }
    
    // Инициализация настроек
    public function init_settings() {
        register_setting('water_level_settings', 'water_level_current');
        register_setting('water_level_settings', 'water_level_post_name');
        register_setting('water_level_settings', 'water_level_water_body');
        register_setting('water_level_settings', 'water_level_normal');
        register_setting('water_level_settings', 'water_level_coordinates');
        register_setting('water_level_settings', 'water_level_ice_mode');

        // Устанавливаем значения по умолчанию при первой активации
        if (get_option('water_level_current') === false) {
            update_option('water_level_current', '92.90');
        }
        if (get_option('water_level_post_name') === false) {
            update_option('water_level_post_name', 'КП "ПЛЕСО"');
        }
        if (get_option('water_level_water_body') === false) {
            update_option('water_level_water_body', 'Русанівська протока');
        }
        if (get_option('water_level_normal') === false) {
            update_option('water_level_normal', '91.50');
        }
        if (get_option('water_level_coordinates') === false) {
            update_option('water_level_coordinates', '50.454078,30.583940');
        }
        if (get_option('water_level_ice_mode') === false) {
            update_option('water_level_ice_mode', '0');
        }
    }
    
    // Подключаем скрипты для админки
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_water-level-manager') {
            wp_enqueue_script('jquery');
            wp_localize_script('jquery', 'water_level_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('water_level_nonce')
            ));
        }
    }
    
    // Страница админки
    public function admin_page() {
        $current_level = get_option('water_level_current', '92.90');
        $post_name = get_option('water_level_post_name', 'КП "ПЛЕСО"');
        $water_body = get_option('water_level_water_body', 'Русанівська протока');
        $normal_level = get_option('water_level_normal', '91.50');
        $coordinates = get_option('water_level_coordinates', '50.454078,30.583940');
        $ice_mode = get_option('water_level_ice_mode', '0');

        ?>
        <div class="wrap">
            <h1>🌊 Управление уровнем воды</h1>

            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2>Текущие показатели</h2>
                <?php if ($ice_mode === '1'): ?>
                    <p><strong>Текущий уровень:</strong> <span id="current-display" style="color: #3498db; font-weight: bold;">❄️ ЛЬОДОСТАВ</span></p>
                    <p><strong>Статус:</strong>
                        <span id="status-display" style="padding: 5px 10px; border-radius: 5px; color: white;">
                            <span style="background: #3498db;">❄️ ЛЬОДОСТАВ</span>
                        </span>
                    </p>
                <?php else: ?>
                    <p><strong>Текущий уровень:</strong> <span id="current-display"><?php echo $current_level; ?></span> м</p>
                    <p><strong>Отклонение от нормы:</strong>
                        <span id="deviation-display" style="font-weight: bold;">
                            <?php
                            $deviation = floatval($current_level) - floatval($normal_level);
                            echo ($deviation >= 0 ? '+' : '') . number_format($deviation, 2) . ' м';
                            ?>
                        </span>
                    </p>
                    <p><strong>Статус:</strong>
                        <span id="status-display" style="padding: 5px 10px; border-radius: 5px; color: white;">
                            <?php
                            if ($current_level >= 92.70) {
                                echo '<span style="background: #e74c3c;">КРИТИЧНИЙ</span>';
                            } elseif ($current_level >= 91.50) {
                                echo '<span style="background: #f39c12;">ПІДВИЩЕНИЙ</span>';
                            } else {
                                echo '<span style="background: #27ae60;">НОРМАЛЬНИЙ</span>';
                            }
                            ?>
                        </span>
                    </p>
                <?php endif; ?>
            </div>
            
            <form method="post" id="water-level-form">
                <?php wp_nonce_field('water_level_nonce', 'water_level_nonce_field'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="water_level_ice_mode">❄️ Льодостав</label>
                        </th>
                        <td>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox"
                                       id="water_level_ice_mode"
                                       name="water_level_ice_mode"
                                       value="1"
                                       <?php checked($ice_mode, '1'); ?>
                                       style="width: 20px; height: 20px;" />
                                <span>Увімкнути режим льодоставу</span>
                            </label>
                            <p class="description">При увімкненому режимі замість рівня води буде відображатися "Льодостав"</p>
                        </td>
                    </tr>
                    <tr id="water-level-row">
                        <th scope="row">
                            <label for="water_level_current">Текущий уровень воды (м)</label>
                        </th>
                        <td>
                            <input type="number"
                                   step="0.01"
                                   min="85.00"
                                   max="100.00"
                                   id="water_level_current"
                                   name="water_level_current"
                                   value="<?php echo esc_attr($current_level); ?>"
                                   style="width: 200px;" />
                            <p class="description">Введите текущий уровень воды в метрах (например: 92.90)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="water_level_post_name">Название поста</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="water_level_post_name" 
                                   name="water_level_post_name" 
                                   value="<?php echo esc_attr($post_name); ?>" 
                                   style="width: 300px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="water_level_water_body">Водоём</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="water_level_water_body" 
                                   name="water_level_water_body" 
                                   value="<?php echo esc_attr($water_body); ?>" 
                                   style="width: 300px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="water_level_normal">Нормальный уровень (м)</label>
                        </th>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   id="water_level_normal" 
                                   name="water_level_normal" 
                                   value="<?php echo esc_attr($normal_level); ?>" 
                                   style="width: 200px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="water_level_coordinates">Координаты (широта,долгота)</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="water_level_coordinates" 
                                   name="water_level_coordinates" 
                                   value="<?php echo esc_attr($coordinates); ?>" 
                                   style="width: 300px;" />
                            <p class="description">Формат: 50.454078,30.583940</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" 
                           name="submit" 
                           id="submit" 
                           class="button-primary" 
                           value="Обновить данные" />
                    <span id="save-status" style="margin-left: 10px;"></span>
                </p>
            </form>
            
            <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <h3>📋 Инструкция по использованию:</h3>
                <p><strong>1.</strong> Введите текущий уровень воды в поле "Текущий уровень воды"</p>
                <p><strong>2.</strong> Нажмите "Обновить данные"</p>
                <p><strong>3.</strong> Данные автоматически обновятся на сайте</p>
                <p><strong>4.</strong> Для отображения виджета на сайте используйте шорткод: <code>[water_level_display]</code></p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Функция для переключения видимости поля уровня воды
            function toggleWaterLevelField() {
                if ($('#water_level_ice_mode').is(':checked')) {
                    $('#water-level-row').hide();
                } else {
                    $('#water-level-row').show();
                }
            }

            // Инициализация при загрузке
            toggleWaterLevelField();

            // Переключение при изменении чекбокса
            $('#water_level_ice_mode').on('change', toggleWaterLevelField);

            $('#water-level-form').on('submit', function(e) {
                e.preventDefault();

                var formData = {
                    action: 'update_water_level',
                    water_level_current: $('#water_level_current').val(),
                    water_level_post_name: $('#water_level_post_name').val(),
                    water_level_water_body: $('#water_level_water_body').val(),
                    water_level_normal: $('#water_level_normal').val(),
                    water_level_coordinates: $('#water_level_coordinates').val(),
                    water_level_ice_mode: $('#water_level_ice_mode').is(':checked') ? '1' : '0',
                    nonce: $('#water_level_nonce_field').val()
                };

                $('#save-status').html('<span style="color: #0073aa;">Сохранение...</span>');

                $.post(water_level_ajax.ajax_url, formData, function(response) {
                    if (response.success) {
                        $('#save-status').html('<span style="color: #00a32a;">✓ Данные успешно сохранены!</span>');

                        // Перезагружаем страницу для обновления отображения статуса
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        $('#save-status').html('<span style="color: #d63638;">Ошибка сохранения!</span>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    // AJAX обновление уровня воды
    public function update_water_level() {
        if (!wp_verify_nonce($_POST['nonce'], 'water_level_nonce')) {
            wp_die('Ошибка безопасности');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $water_level = sanitize_text_field(wp_unslash($_POST['water_level_current']));
        $post_name = sanitize_text_field(wp_unslash($_POST['water_level_post_name']));
        $water_body = sanitize_text_field(wp_unslash($_POST['water_level_water_body']));
        $normal_level = sanitize_text_field(wp_unslash($_POST['water_level_normal']));
        $coordinates = sanitize_text_field(wp_unslash($_POST['water_level_coordinates']));
        $ice_mode = (!empty($_POST['water_level_ice_mode']) && $_POST['water_level_ice_mode'] === '1') ? '1' : '0';

        update_option('water_level_current', $water_level);
        update_option('water_level_post_name', $post_name);
        update_option('water_level_water_body', $water_body);
        update_option('water_level_normal', $normal_level);
        update_option('water_level_coordinates', $coordinates);
        update_option('water_level_ice_mode', $ice_mode);
        update_option('water_level_last_update', current_time('mysql'));

        wp_send_json_success(array(
            'level' => $water_level,
            'ice_mode' => $ice_mode,
            'message' => 'Данные успешно обновлены'
        ));
    }
    
    // AJAX получение уровня воды
    public function get_water_level() {
        $data = array(
            'level' => get_option('water_level_current', '92.90'),
            'post_name' => get_option('water_level_post_name', 'КП "ПЛЕСО"'),
            'water_body' => get_option('water_level_water_body', 'Русанівська протока'),
            'normal_level' => get_option('water_level_normal', '91.50'),
            'coordinates' => get_option('water_level_coordinates', '50.454078,30.583940'),
            'ice_mode' => get_option('water_level_ice_mode', '0'),
            'last_update' => get_option('water_level_last_update', current_time('mysql'))
        );

        wp_send_json_success($data);
    }
    
    // Шорткод для отображения виджета
    public function display_water_level($atts) {
        // Подключаем необходимые скрипты
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'water_level_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        ob_start();
        include plugin_dir_path(__FILE__) . 'water-level-widget.php';
        return ob_get_clean();
    }
}

// Инициализация плагина
new WaterLevelManager();

// Активация плагина
register_activation_hook(__FILE__, function() {
    // Создаем таблицу для истории (опционально)
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'water_level_history';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        water_level decimal(5,2) NOT NULL,
        recorded_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});
?>