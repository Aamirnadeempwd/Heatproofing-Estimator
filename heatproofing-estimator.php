<?php
/**
 * Plugin Name: Heatproofing Estimator
 * Plugin URI:  
 * Description: A tool to calculate cost estimates for different heatproofing products (Coating, Chips, Tiles).
 * Version:     1.0.0
 * Author:      Antigravity
 * Author URI:  
 * Text Domain: heatproofing-estimator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Create custom database table on plugin activation
function hp_estimator_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hp_estimates';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        area float NOT NULL,
        coating_cost float NOT NULL,
        chips_special_cost float NOT NULL,
        chips_super_cost float NOT NULL,
        chips_economy_cost float NOT NULL,
        tile_mat_cost float NOT NULL,
        tile_summer_cost float NOT NULL,
        dynamic_costs text DEFAULT '' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'hp_estimator_activate' );

function hp_get_dynamic_products() {
    $existing = get_option('hp_dynamic_products', false);
    if ($existing !== false) {
        $decoded = json_decode($existing, true);
        if (is_array($decoded)) {
            $updated = false;
            foreach ($decoded as &$p) {
                // Fix broken images for original products
                if ($p['id'] === 'coating' && (empty($p['image_url']) || strpos($p['image_url'], 'localhost') !== false || strpos($p['image_url'], 'plugin_dir_url') !== false || !filter_var($p['image_url'], FILTER_VALIDATE_URL))) {
                    $p['image_url'] = plugin_dir_url(__FILE__) . 'assets/img/solar_coating.png';
                    $updated = true;
                }
                if ($p['id'] === 'chips' && (empty($p['image_url']) || strpos($p['image_url'], 'localhost') !== false || strpos($p['image_url'], 'plugin_dir_url') !== false || !filter_var($p['image_url'], FILTER_VALIDATE_URL))) {
                    $p['image_url'] = plugin_dir_url(__FILE__) . 'assets/img/solar_chips.png';
                    $updated = true;
                }
                if ($p['id'] === 'tiles' && (empty($p['image_url']) || strpos($p['image_url'], 'localhost') !== false || strpos($p['image_url'], 'plugin_dir_url') !== false || !filter_var($p['image_url'], FILTER_VALIDATE_URL))) {
                    $p['image_url'] = plugin_dir_url(__FILE__) . 'assets/img/solar_tiles.png';
                    $updated = true;
                }
                
                // Ensure new fields exist for new products
                if (!isset($p['badge'])) $p['badge'] = '';
                if (!isset($p['quote'])) $p['quote'] = '';
                if (!isset($p['benefits'])) $p['benefits'] = array();
                if (!isset($p['video_url'])) $p['video_url'] = '';
                if (!isset($p['feedback_url'])) $p['feedback_url'] = '';
            }
            // Auto-heal the database
            if ($updated) {
                update_option('hp_dynamic_products', wp_json_encode($decoded, JSON_UNESCAPED_UNICODE));
            }
            return $decoded;
        }
    }
    
    // Seed default products
    $default_products = array(
        array(
            'id' => 'coating',
            'name' => '1. آپشن 1: سولر روف کوٹنگ (معیاری تحفظ)',
            'image_url' => get_option('hp_img_coating', plugin_dir_url(__FILE__) . 'assets/img/solar_coating.png'),
            'badge' => 'لائف: 3 سے 5 سال کی بہترین لائف',
            'quote' => '"کم خرچ اور فوری اثر کے لیے بہترین انتخاب"',
            'benefits' => array(
                'اے سی کے استعمال میں 50 فیصد تک کمی',
                'سورج کی شعاعوں کو 90% تک واپس منعکس (Reflect) کرتا ہے۔',
                'درجہ حرارت میں 8°C سے 12°C تک نمایاں کمی۔',
                'لگوانے کے بعد آپ تپتی دوپہر میں بھی چھت پر ننگے پاؤں چل سکتے ہیں۔'
            ),
            'unit_name' => 'بالٹیاں',
            'unit_desc' => '(27 کلوگرام فی بالٹی • ڈبل کوٹ)',
            'coverage_divisor' => 400,
            'video_url' => 'https://youtu.be/3g146_LddLo?si=Yp8QUq8paM0ipttl',
            'feedback_url' => 'https://youtube.com/playlist?list=PLrpsO5O4fToxscszum7wozp-UVZRqUBHn&si=CjgNWqXgtIaKwJ5d',
            'whatsapp_type' => 'coating',
            'variants' => array(
                array('id' => 'default', 'name' => 'سٹینڈرڈ', 'price' => get_option('hp_coating_price', 7500))
            )
        ),
        array(
            'id' => 'chips',
            'name' => '2. آپشن 2: سولر چپس (پریمیم تحفظ)',
            'image_url' => get_option('hp_img_chips', plugin_dir_url(__FILE__) . 'assets/img/solar_chips.png'),
            'badge_type' => 'lifetime',
            'badge' => 'وارنٹی: لائف ٹائم پائیداری اور گارنٹی',
            'quote' => '"مضبوطی اور ٹھنڈک کا بہترین امتزاج"',
            'benefits' => array(
                '100% واٹر پروفنگ اور ہیٹ پروفنگ کا دوہرا تحفظ۔',
                'چھت سے آنے والی تپش کو سو فیصد روکے۔',
                'گھر کی چھتوں کو سیم اور نمی سے ہمیشہ کے لیے محفوظ رکھے۔',
                'بجلی کے بھاری بلوں (AC کے استعمال) میں زبردست کمی۔'
            ),
            'unit_name' => 'بیگز',
            'unit_desc' => '(40 کلوگرام فی بیگ)',
            'coverage_divisor' => 12,
            'video_url' => 'https://youtu.be/FInGb1ymo4E?si=9hlX9FK3jZxKpInk',
            'feedback_url' => 'https://youtu.be/dyVnjFvLzVw?si=npyOXQViDHKfZb10',
            'whatsapp_type' => 'chips',
            'variants' => array(
                array('id' => 'special', 'name' => 'سپیشل کوالٹی', 'price' => get_option('hp_chips_special_price', 2800)),
                array('id' => 'super', 'name' => 'سپر فیس', 'price' => get_option('hp_chips_super_price', 2300)),
                array('id' => 'economy', 'name' => 'اکانومی کلاس', 'price' => get_option('hp_chips_economy_price', 1800))
            )
        ),
        array(
            'id' => 'tiles',
            'name' => '3. آپشن 3: سولر ٹائل (لگژری تحفظ)',
            'image_url' => get_option('hp_img_tile', plugin_dir_url(__FILE__) . 'assets/img/solar_tiles.png'),
            'badge_type' => 'lifetime',
            'badge' => 'وارنٹی: لائف ٹائم گارنٹی (Premium Quality)',
            'quote' => '"خوبصورتی اور جدید ٹیکنالوجی سے آراستہ"',
            'benefits' => array(
                'ماربل جیسی خوبصورت فنشنگ جو گھر کی ویلیو بڑھائے۔',
                'شدید ترین گرمی میں بھی کمروں کو قدرتی طور پر ٹھنڈا رکھے۔',
                'اے سی کے استعمال میں 50 فیصد تک کمی۔',
                'صاف کرنے میں آسان اور انتہائی پائیدار میٹریل۔'
            ),
            'unit_name' => '',
            'unit_desc' => '',
            'coverage_divisor' => 1,
            'video_url' => 'https://youtu.be/qxibDJzYfCE?si=G4rlKj-qjYQXEdw9',
            'feedback_url' => 'https://youtu.be/YbE2qZqG6Y8?si=IsZ6LLtuokDNHHBd',
            'whatsapp_type' => 'tiles',
            'variants' => array(
                array('id' => 'mat', 'name' => 'منور ائرکنڈیشننگ ٹائل (MAT)', 'badge' => 'گرمی + سردی', 'badge_color' => '#1565c0', 'badge_bg' => '#bbdefb', 'price' => get_option('hp_tile_mat_price', 300)),
                array('id' => 'summer', 'name' => 'سولر ٹائل', 'badge' => 'صرف گرمی لیے', 'badge_color' => '#f57f17', 'badge_bg' => '#fff9c4', 'price' => get_option('hp_tile_summer_price', 250))
            )
        )
    );
    update_option('hp_dynamic_products', json_encode($default_products, JSON_UNESCAPED_UNICODE));
    return $default_products;
}

function hp_estimator_enqueue_scripts($hook) {
    // Only load on the admin page or front-end shortcode
    $is_admin_page = is_admin() && 'toplevel_page_heatproofing-estimator' === $hook;
    $is_frontend = !is_admin();

    if ($is_admin_page || $is_frontend) {
        wp_enqueue_style( 'hp-estimator-style', plugins_url( 'assets/css/style.css', __FILE__ ) );
        wp_enqueue_script( 'hp-estimator-script', plugins_url( 'assets/js/script.js', __FILE__ ), array('jquery'), '1.0', true );
        
        // Pass dynamic pricing from Settings to JavaScript
        $dynamic_products = hp_get_dynamic_products();
        
        $hp_settings = array(
            'customProducts'=> $dynamic_products,
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'whatsappNumber'=> get_option('hp_whatsapp_number', '923006786890')
        );
        wp_localize_script( 'hp-estimator-script', 'hpSettings', $hp_settings );
    }
}
add_action( 'wp_enqueue_scripts', 'hp_estimator_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'hp_estimator_enqueue_scripts' );

// AJAX Handler for saving estimates
function hp_save_estimate_ajax() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hp_estimates';
    
    // Sanitize incoming float for area
    $area = isset($_POST['area']) ? floatval($_POST['area']) : 0;
    
    // Fallbacks for old static reporting
    $coating_cost = isset($_POST['coating_cost']) ? floatval($_POST['coating_cost']) : 0;
    $chips_special_cost = isset($_POST['chips_special_cost']) ? floatval($_POST['chips_special_cost']) : 0;
    $chips_super_cost = isset($_POST['chips_super_cost']) ? floatval($_POST['chips_super_cost']) : 0;
    $chips_economy_cost = isset($_POST['chips_economy_cost']) ? floatval($_POST['chips_economy_cost']) : 0;
    $tile_mat_cost = isset($_POST['tile_mat_cost']) ? floatval($_POST['tile_mat_cost']) : 0;
    $tile_summer_cost = isset($_POST['tile_summer_cost']) ? floatval($_POST['tile_summer_cost']) : 0;
    
    $dynamic_costs_str = isset($_POST['dynamic_costs']) ? wp_unslash($_POST['dynamic_costs']) : '';

    if ($area <= 0) {
        wp_send_json_error('Invalid area');
    }

    $wpdb->insert(
        $table_name,
        array(
            'area' => $area,
            'coating_cost' => $coating_cost,
            'chips_special_cost' => $chips_special_cost,
            'chips_super_cost' => $chips_super_cost,
            'chips_economy_cost' => $chips_economy_cost,
            'tile_mat_cost' => $tile_mat_cost,
            'tile_summer_cost' => $tile_summer_cost,
            'dynamic_costs' => $dynamic_costs_str,
            'created_at' => current_time('mysql', 0)
        ),
        array('%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%s')
    );

    wp_send_json_success('Estimate saved');
}
add_action( 'wp_ajax_hp_save_estimate', 'hp_save_estimate_ajax' );
add_action( 'wp_ajax_nopriv_hp_save_estimate', 'hp_save_estimate_ajax' );


function hp_estimator_html() {
    $theme_mode = get_option('hp_theme_mode', 'light');
    $img_coating = get_option('hp_img_coating', plugin_dir_url(__FILE__) . 'assets/img/solar_coating.png');
    $img_chips   = get_option('hp_img_chips', plugin_dir_url(__FILE__) . 'assets/img/solar_chips.png');
    $img_tile    = get_option('hp_img_tile', plugin_dir_url(__FILE__) . 'assets/img/solar_tiles.png'); // using solar_tiles.png as default based on current code
    
    $container_class = 'hp-estimator-container';
    if ($theme_mode === 'dark') {
        $container_class .= ' hp-dark-theme';
    }

    ob_start(); ?>
    <div class="<?php echo esc_attr($container_class); ?>" id="printableArea" dir="rtl">
        <!-- Theme Toggle Button -->
        <div class="hp-theme-toggle-wrapper no-print" style="text-align: left; margin-bottom: -30px; position: relative; z-index: 10;">
            <button id="hpThemeToggle" class="hp-btn" style="background: transparent; border: 1px solid #ccc; border-radius: 20px; padding: 5px 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: inherit;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 21.054A9.043 9.043 0 0 1 2.946 12 9.043 9.043 0 0 1 12 2.946v18.108z"/></svg> 
                <span id="hpThemeToggleText">تھیم تبدیل کریں</span>
            </button>
        </div>
        <div class="hp-header">
            <div class="hp-header-left">
                <a href="https://sober.pk" target="_blank">
                    <img src="https://sober.pk/wp-content/uploads/2023/05/sober-web-logo.png" alt="Sober Logo" class="hp-logo">
                </a>
                <div class="hp-brand-text">SOBER Technologies intl.</div>
            </div>
            <div class="hp-header-center">
                <h1 class="hp-title eng-title" dir="ltr">
                    <span class="hp-hover-word">SOBER</span> <span class="hp-hover-word">Heatproofing</span><br>
                    <span class="hp-hover-word">Estimator</span>
                </h1>
                <p class="hp-desc">
                    <strong class="hp-desc-highlight">
                        <span class="hp-hover-word">گھر</span> <span class="hp-hover-word">ٹھنڈا،</span> <span class="hp-hover-word">بجلی</span> <span class="hp-hover-word">کا</span> <span class="hp-hover-word">بل</span> <span class="hp-hover-word">کم!</span>
                    </strong>
                    <br>
                    <span class="hp-desc-subtext">
                        <strong>
                            <span class="hp-hover-word">چھت</span> <span class="hp-hover-word">کا</span> <span class="hp-hover-word">رقبہ</span> <span class="hp-hover-word">درج</span> <span class="hp-hover-word">کریں</span> <span class="hp-hover-word">اور</span> <span class="hp-hover-word">اپنے</span> <span class="hp-hover-word">بجٹ</span> <span class="hp-hover-word">کے</span> <span class="hp-hover-word">مطابق</span> <span class="hp-hover-word">فوری</span> <span class="hp-hover-word">کوٹیشن</span> <span class="hp-hover-word">حاصل</span> <span class="hp-hover-word">کریں</span>
                        </strong>
                    </span>
                </p>
            </div>
            <div class="hp-header-right">
                <!-- User can replace 'header-left.jpg' in assets/img folder with their own image -->
                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/header-left.jpg'; ?>" alt="Left Image" class="hp-header-side-img">
                <div class="hp-brand-text">Pro. Munawar Ahmad Malik</div>
            </div>
        </div>

        <div class="hp-input-group no-print">
            <label for="roofArea">کل رقبہ (مربع فٹ / Sq Ft) درج کریں:</label>
            <input type="number" id="roofArea" placeholder="مثلاً: 1000" min="1">
            <button id="hpCalculateBtn" class="hp-btn hp-btn-primary">ایسٹیمیٹ لگائیں</button>
        </div>

        <div id="hpResults" class="hp-results hidden">

            <!-- Dynamic Products Start -->
<?php 
$dynamic_products = hp_get_dynamic_products(); 
foreach($dynamic_products as $product): 
?>
            <div class="hp-card" data-product-id="<?php echo esc_attr($product['id']); ?>">
                <h3><?php echo esc_html($product['name']); ?></h3>
                <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($product['name'])); ?>" class="hp-product-img">
                <div class="hp-product-meta">
                    <?php if (!empty($product['badge'])): ?>
                        <?php if (isset($product['badge_type']) && $product['badge_type'] === 'lifetime'): ?>
                            <span class="hp-badge-lifetime"><?php echo esc_html($product['badge']); ?></span>
                        <?php else: ?>
                            <span class="hp-badge"><?php echo esc_html($product['badge']); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($product['quote'])): ?>
                        <span class="hp-quote"><?php echo esc_html($product['quote']); ?></span>
                    <?php endif; ?>
                </div>

                <?php if (count($product['variants']) === 1): ?>
                    <div class="hp-estimate-row">
                        <?php if (!empty($product['unit_name'])): ?>
                            <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="<?php echo esc_attr($product['id']); ?>_quantity" class="eng-num">0</span> <?php echo esc_html($product['unit_name']); ?> <span class="small-tag"><?php echo esc_html($product['unit_desc']); ?></span></p>
                        <?php endif; ?>
                        <p class="hp-price-inline">قیمت: <span id="<?php echo esc_attr($product['id']); ?>_cost_<?php echo esc_attr($product['variants'][0]['id']); ?>" class="eng-num">0</span> روپے</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['benefits'])): ?>
                <div class="hp-details">
                    <p class="hp-ben-title"><strong>فائدے:</strong></p>
                    <ul class="hp-ben-list">
                        <?php foreach($product['benefits'] as $benefit): ?>
                            <li><?php echo esc_html($benefit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (count($product['variants']) > 1): ?>
                    <?php foreach($product['variants'] as $variant): ?>
                        <div class="hp-variant">
                            <h4><?php echo esc_html($variant['name']); ?> 
                                <?php if (!empty($variant['badge'])): ?>
                                    <span class="hp-badge" style="background: <?php echo esc_attr($variant['badge_bg']); ?>; color: <?php echo esc_attr($variant['badge_color']); ?>;"><?php echo esc_html($variant['badge']); ?></span>
                                <?php endif; ?>
                            </h4>
                            <?php if (!empty($product['unit_name'])): ?>
                                <div class="hp-estimate-row">
                                    <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="<?php echo esc_attr($product['id']); ?>_quantity_<?php echo esc_attr($variant['id']); ?>" class="eng-num">0</span> <?php echo esc_html($product['unit_name']); ?> <span class="small-tag"><?php echo esc_html($product['unit_desc']); ?></span></p>
                                    <p class="hp-price-inline">قیمت: <span id="<?php echo esc_attr($product['id']); ?>_cost_<?php echo esc_attr($variant['id']); ?>" class="eng-num">0</span> روپے</p>
                                </div>
                            <?php else: ?>
                                <p class="hp-price">قیمت: <span id="<?php echo esc_attr($product['id']); ?>_cost_<?php echo esc_attr($variant['id']); ?>" class="eng-num">0</span> روپے</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="hp-video-group no-print">
                    <?php if (!empty($product['video_url'])): ?>
                    <a href="<?php echo esc_url($product['video_url']); ?>" target="_blank" class="hp-btn hp-btn-video">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        طریقہ کار دیکھیں
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($product['feedback_url'])): ?>
                    <a href="<?php echo esc_url($product['feedback_url']); ?>" target="_blank" class="hp-btn hp-btn-feedback">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.858 1.4-8.168-5.934-5.787 8.2-1.192z"/></svg>
                        کسٹمر فیڈ بیک
                    </a>
                    <?php endif; ?>
                </div>

                <button class="hp-btn hp-btn-whatsapp no-print" onclick="sendWhatsApp('<?php echo esc_js($product['id']); ?>')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                    WhatsApp پر معلومات بھیجیں
                </button>
            </div>
<?php endforeach; ?>
            <!-- Dynamic Products End -->

            <div class="pdf-actions no-print">
                <button class="hp-btn hp-btn-print" onclick="printEstimate()">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                    ایسٹیمیٹ پرنٹ کریں
                </button>
                <button class="hp-btn hp-btn-pdf" onclick="generatePDF()">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M12 16L7 11l1.4-1.4 2.6 2.6V4h2v8.2l2.6-2.6L17 11l-5 5zm-8 4v-2h16v2H4z"/></svg>
                    پی ڈی ایف ڈاؤنلوڈ کریں
                </button>
            </div>
            <div class="hp-social-section">
                <h3>ہیٹ پروفنگ کے بہترین حل اور اپڈیٹس کے لئے ہم سے جڑے رہیں</h3>
                <div class="hp-social-grid">
                    <!-- Row 1: WhatsApp and YouTube -->
                    <a href="https://wa.me/923006786890" target="_blank" class="hp-btn hp-btn-whatsapp-grid">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                        WhatsApp
                    </a>
                    <a href="https://youtube.com/@coolyourhome7682?si=hq0Qlom74QKE7RwW" target="_blank" class="hp-btn hp-btn-youtube-grid">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        YouTube
                    </a>
                    <!-- Row 2: Facebook and Website -->
                    <a href="https://www.facebook.com/share/183cz1o861/" target="_blank" class="hp-btn hp-btn-facebook-grid">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>
                    <a href="https://sober.pk" target="_blank" class="hp-btn hp-btn-website-grid">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 8px;"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.514 0-10-4.486-10-10S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10zm-1-15h2v6h-2V7zm0 8h2v2h-2v-2z"/></svg>
                        Website
                    </a>
                </div>
                
                <div class="hp-powered-by">
                    <p><strong>powered by :</strong> SOBER Technologies intl</p>
                    <p dir="ltr">0515421151 - 03006786890</p>
                    <p>islamabad</p>
                </div>
            </div>
        </div>
        
        <div id="hpError" class="hidden error-text hp-error no-print">براہ کرم کوئی درست رقبہ درج کریں۔</div>
    </div>
    
    <!-- Load PDF Generation scripts directly in output for safety -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <?php
    return ob_get_clean();
}

// Add Shortcode
function hp_estimator_shortcode() {
    return hp_estimator_html();
}
add_shortcode( 'heatproofing_estimator', 'hp_estimator_shortcode' );


// Add Admin Menu Page
function hp_estimator_admin_menu() {
    add_menu_page(
        'HP Estimator', // Page title
        'HP Estimator', // Menu title
        'manage_options', // Capability
        'heatproofing-estimator', // Menu slug
        'hp_estimator_admin_page', // Callback function
        'dashicons-calculator', // Icon
        81 // Position
    );
}
add_action( 'admin_menu', 'hp_estimator_admin_menu' );

function hp_estimator_admin_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'estimator';
    
    // Base URL for tabs
    $base_url = admin_url('admin.php?page=heatproofing-estimator');
    
    // Inject CSS to hide the default WordPress sidebar and expand content area
    echo '<style>
        #adminmenumain { display: none !important; }
        #wpcontent, #wpfooter { margin-left: 0 !important; margin-right: 0 !important; }
        .update-nag, .notice { display: none !important; } /* Optional: Hide random WP notices on this app page */
    </style>';

    echo '<div class="wrap hp-admin-dashboard-wrap" style="display: flex; gap: 20px; align-items: flex-start; margin-top: 20px; direction: rtl;">';
    
    // --- Custom Sidebar Section ---
    echo '  <div class="hp-admin-sidebar" style="flex: 0 0 250px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">';
    echo '      <h2 style="margin-top: 0; font-family: \'Noto Nastaliq Urdu\', serif; text-align: right; color: #1a73e8; font-size: 22px; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">مینیو</h2>';
    echo '      <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">';
    
    // Tab Links
    $tabs = [
        'estimator'    => ['title' => 'ایسٹیمیٹ لگائیں', 'icon' => 'dashicons-calculator'],
        'faqs'         => ['title' => 'عمومی سوالات', 'icon' => 'dashicons-editor-help'],
        'share'        => ['title' => 'شیئر کریں', 'icon' => 'dashicons-share'],
        'introduction' => ['title' => 'تعارف', 'icon' => 'dashicons-info'],
        'whatsapp'     => ['title' => 'تبدیلی وٹس ایپ', 'icon' => 'dashicons-phone'],
        'images'       => ['title' => 'تبدیلی تصویر', 'icon' => 'dashicons-images-alt2'],
        'theme'        => ['title' => 'تھیم', 'icon' => 'dashicons-art'],
        'settings'     => ['title' => 'سیٹنگز', 'icon' => 'dashicons-admin-generic'],
        'reports'      => ['title' => 'رپورٹس', 'icon' => 'dashicons-chart-bar'],
        'help'         => ['title' => 'مدد', 'icon' => 'dashicons-editor-help'],
    ];

    foreach ($tabs as $key => $tab) {
        $active_class = ($active_tab === $key) ? 'background-color: #f0f7ff; color: #1a73e8; border-right: 3px solid #1a73e8; font-weight: bold;' : 'color: #555; border-right: 3px solid transparent;';
        $hover_style = "onmouseover=\"this.style.backgroundColor='#f8f9fa'\" onmouseout=\"if(!this.classList.contains('active-tab')){this.style.backgroundColor='transparent'}\"";
        
        echo "          <li>";
        echo "              <a href='{$base_url}&tab={$key}' class='" . ($active_tab === $key ? 'active-tab' : '') . "' style='text-decoration: none; padding: 12px 15px; display: flex; align-items: center; justify-content: flex-start; gap: 10px; border-radius: 4px; transition: all 0.3s; {$active_class}' {$hover_style}>";
        echo "                  <span class='dashicons {$tab['icon']}'></span>";
        echo "                  <span style='font-family: \"Noto Nastaliq Urdu\", serif; font-size: 16px; margin-top: 4px;'>{$tab['title']}</span>";
        echo "              </a>";
        echo "          </li>";
    }
    
    echo '      </ul>';
    echo '      <div style="margin-top: 30px; text-align: center; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #888;">Powered by SOBER</div>';
    echo '  </div>';

    // --- Content Area Section ---
    echo '  <div class="hp-admin-content" style="flex: 1; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden;">';
    
    if ($active_tab === 'estimator') {
        echo hp_estimator_html();
    } elseif ($active_tab === 'faqs') {
        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl; max-width: 800px; margin: 0 auto;">';
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">اکثر پوچھے گئے سوالات (FAQs)</h2>';
        
        echo '<div class="hp-faq-section" style="box-shadow: none; padding: 0;">';
        echo '  <div class="hp-faq-category">کمپنی کا تعارف اور ساکھ</div>';
        
        echo '  <div class="hp-faq-item">';
        echo '      <button class="hp-faq-question">سوبر ٹیکنالوجیز (SOBER Technologies) کا اس فیلڈ میں کتنا تجربہ ہے؟</button>';
        echo '      <div class="hp-faq-answer"><p>سوبر ٹیکنالوجیز 1996 سے خدمات فراہم کر رہی ہے اور اس فیلڈ میں پاکستان کی پہلی کمپنی ہے۔ کمپنی کے بانی پروفیسر منور احمد ملک (M.Sc Physics) ہیں جن کا 27 سالہ تجربہ اور 50 سے زائد ایجادات ہیں۔</p></div>';
        echo '  </div>';

        echo '  <div class="hp-faq-item">';
        echo '      <button class="hp-faq-question">کیا ان مصنوعات کی تصدیق کسی مستند ادارے نے کی ہے؟</button>';
        echo '      <div class="hp-faq-answer"><p>جی ہاں، حکومتِ پاکستان کے ادارے ENERCON، CDA، وزارتِ ماحولیات اور عالمی ادارے جیسے UN-HABITAT نے ان کے دعووں کی عملی تصدیق کی ہے۔</p></div>';
        echo '  </div>';

        echo '  <div class="hp-faq-category">عمومی فوائد اور نتائج</div>';
        
        echo '  <div class="hp-faq-item">';
        echo '      <button class="hp-faq-question">ان مصنوعات کے استعمال سے بجلی کے بل میں کتنی بچت ممکن ہے؟</button>';
        echo '      <div class="hp-faq-answer"><p>یہ مصنوعات 90% تک سورج کی حرارت کو گھر میں داخل ہونے سے روکتی ہیں، جس سے A/C کے استعمال میں نمایاں کمی آتی ہے اور بجلی کے بلوں میں 40% سے 50% تک ماہانہ بچت ہو سکتی ہے۔</p></div>';
        echo '  </div>';

        echo '  <div class="hp-faq-item">';
        echo '      <button class="hp-faq-question">کیا اس سے گرمیوں میں چھت پر ننگے پاؤں چلا جا سکتا ہے؟</button>';
        echo '      <div class="hp-faq-answer"><p>بالکل۔ ہماری تمام مصنوعات (ٹائلز، چپس، اور کوٹنگ) سورج کی روشنی کو واپس منعکس (reflect) کرتی ہیں۔ شدید گرمی اور تپتی دوپہر میں بھی ان پر ننگے پاؤں چلنا ممکن ہے۔</p></div>';
        echo '  </div>';
        
        echo '  <div class="hp-faq-item">';
        echo '      <button class="hp-faq-question">لگانے کے بعد چھت کی لائف/مدت کتنی ہوتی ہے؟</button>';
        echo '      <div class="hp-faq-answer"><p>سولر ٹائل اور سولر چپس کی لائف ٹائم گارنٹی ہے (ساری زندگی کے لئے)۔ جبکہ سولر روف کوٹنگ 3 سے 5 سال تک کارآمد رہتی ہے۔</p></div>';
        echo '  </div>';
        echo '</div>';
        echo '</div>'; // End container
    } elseif ($active_tab === 'share') {
        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl; max-width: 800px; margin: 0 auto; text-align: center;">';
        echo '<h2 style="color: #f57f17; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">ایسٹیمیٹر دوستوں کے ساتھ شیئر کریں</h2>';
        echo '<p style="font-size: 18px; color: #555; margin-bottom: 25px;">یہ مفید ٹول اپنے پیاروں کے ساتھ واٹس ایپ، فیس بک اور ٹویٹر پر شیئر کریں تاکہ وہ بھی گرمی سے نجات پا سکیں!</p>';
        
        // Re-use the existing JS functions since they are loaded globally on this admin page
        echo '<div class="hp-share-grid" style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">';
        echo '  <button class="hp-btn hp-btn-whatsapp" onclick="shareEstimator(\'whatsapp\')" style="min-width: 120px; font-family: inherit;">';
        echo '      <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 5px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>';
        echo '      WhatsApp';
        echo '  </button>';
        echo '  <button class="hp-btn" onclick="shareEstimator(\'facebook\')" style="background: #1877f2; color: white; min-width: 120px; font-family: inherit;">';
        echo '      <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 5px;"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
        echo '      Facebook';
        echo '  </button>';
        echo '  <button class="hp-btn" onclick="shareEstimator(\'twitter\')" style="background: #1da1f2; color: white; min-width: 120px; font-family: inherit;">';
        echo '      <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 5px;"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>';
        echo '      X (Twitter)';
        echo '  </button>';
        echo '  <button class="hp-btn" onclick="copyEstimatorLink()" style="background: #333; color: white; min-width: 120px; font-family: inherit;">';
        echo '      <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="vertical-align: middle; margin-left: 5px;"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
        echo '      کاپی لنک';
        echo '  </button>';
        echo '</div>';
        echo '</div>'; // End container
    } elseif ($active_tab === 'introduction') {
        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl; max-width: 800px; margin: 0 auto;">';
        
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">تعارف</h2>';
        
        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0; font-size: 20px;">گرمیوں میں چھت کی تپش اور لیکیج سے یقینی نجات</h3>';
        echo '<p style="line-height: 1.8; color: #555; text-align: justify; font-size: 16px;">ہماری کمپنی <strong style="color: #1a73e8;">سوبر ٹیکنالوجیز</strong> 1996 سے واٹر پروفنگ اور ہیٹ پروفنگ کی سروسز فراہم کررہی ہے اور اس فیلڈ میں پاکستان کی پہلی کمپنی ہے۔ ہماری چار قسم کی پروڈکٹس ہیں جنہیں استعمال کرنے سے کمرے میں صبح کا درجہ حرارت سارا دن قائم رہتا ہے سیلنگ فین دوپہر 2 بجے بھی ٹھنڈی ہوا دیتاہے اے سی کے بل میں 60 فیصد تک کمی آتی ہے چھت کی لیکج اور نمی سے نجات مل جاتی ہے اور چھت کی لائف میں اضافہ ہوتا ہے۔ ان کا مختصر تعارف درج ذیل ہے۔</p>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0; font-size: 18px;">1. سولر چپس (ٹھنڈی چپس)</h3>';
        echo '<p style="line-height: 1.8; color: #555; text-align: justify; font-size: 16px;">سولر چپس گرمیوں میں کمروں کو ٹھنڈا رکھتی ہے اور 100% واٹر پروفنگ بھی کرتی ہے۔ یہ سفید رنگ کا پہلے سے تیار شدہ مٹیریل ہوتا ہے جس میں سولر پاؤڈر، کم سے کم گرم ہونے والا چپس کا دانہ اور سفید سیمنٹ شامل ہوتا ہے۔ اس میں آپ کو صرف پانی شامل کرنا ہے۔ اسے عام چپس کی طرح لگایا جاتا ہے جسے حسب ضرورت گرائینڈ پالش کر کے مزید خوبصورت بنایا جا سکتا ہے۔ اس پروڈکٹ کی ہیٹ پروفنگ اور واٹر پروفننگ کی گارنٹی لائف ٹائم ہے۔</p>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0; font-size: 18px;">2. منور ایئر کنڈیشننگ ٹائل</h3>';
        echo '<p style="line-height: 1.8; color: #555; text-align: justify; font-size: 16px;">ہماری کمپنی 2 قسم کی ٹھنڈی ٹائلز تیار کرتی ہے۔ <strong>منور ائرکنڈیشننگ ٹائل MAT</strong> گرمیوں میں کمروں کو ٹھنڈا اور سردیوں میں گرم رکھتی ہے اور واٹر پروفنگ بھی کرتی ہے، جبکہ دوسری قسم صرف گرمیوں کے لئے ہے اور یہ گرمیوں میں کمروں کو ٹھنڈا رکھنے کے لئے استعمال کی جاتی ہے۔ سفید رنگ کی اس ٹائل کو عام ماربل ٹائل کی طرح لگایا جاتا ہے جو گرائینڈ پالش شدہ خوبصورت و دلکش نظر آتی ہے۔ ہماری اس پروڈکٹ کی ہیٹ پروفنگ واٹر پروفننگ کی گارنٹی لائف ٹائم ہے۔</p>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0; font-size: 18px;">3. سولر پلستر / واٹر پروف پلستر</h3>';
        echo '<p style="line-height: 1.8; color: #555; text-align: justify; font-size: 16px;">سولر پلستر نئی کوری دیواروں پر لگایا جاتا ہے جس سے لائف ٹائم کے لئے ہیٹ پروفنگ واٹر پروفننگ کے ساتھ دیواروں پر رنگ پینٹ کی ضرورت بھی نہیں رہتی۔</p>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0; font-size: 18px;">4. سولر روف کوٹنگ</h3>';
        echo '<p style="line-height: 1.8; color: #555; text-align: justify; font-size: 16px;">سولر روف کوٹنگ چھتوں اور دیواروں کو سورج کی تپش سے بچانے اور واٹر پروف کرنے والی سستی ترین ٹریٹمنٹ ہے۔ یہ سفید رنگ کا کیمیکل ہے جو چھت، دیوارں اور پانی کی ٹینکی پر برش کی مدد سے بڑی آسانی سے لگایا جاسکتا ہے یہ پائیدار ہونے کی وجہ سے تیز بارش میں بھی نہیں دھلتا اور تقریباً 3 سے 5 سال تک کار آمد رہتا ہے۔ ایک بالٹی تقریباً 400/500 مربع فٹ پر ڈبل کوٹ کوریج کے لئے کافی ہے۔</p>';
        echo '</div>';

        echo '<div style="background: #eaf1fa; border-right: 4px solid #1a73e8; padding: 20px; border-radius: 4px; text-align: center;">';
        echo '<p style="line-height: 1.8; color: #333; font-size: 18px; font-weight: bold; margin-bottom: 10px;">مزید معلومات اور ملک بھر میں حاصل کرنے کے لئے کال یا وٹس ایپ کریں</p>';
        echo '<p style="direction: ltr; font-size: 24px; color: #1a73e8; font-family: sans-serif; font-weight: bold; margin: 0;">0300-6786890</p>';
        echo '</div>';

        echo '</div>';
    } elseif ($active_tab === 'whatsapp') {
        // Change WhatsApp Number Tab
        if (isset($_POST['hp_save_whatsapp']) && check_admin_referer('hp_settings_save', 'hp_settings_nonce')) {
            update_option('hp_whatsapp_number', sanitize_text_field($_POST['hp_whatsapp_number']));
            echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 15px; margin: 20px; border-radius: 5px; font-family: \'Noto Nastaliq Urdu\', serif;">وٹس ایپ نمبر کامیابی سے تبدیل ہو گیا!</div>';
        }
        $wp_num = get_option('hp_whatsapp_number', '923006786890');

        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl;">';
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">تبدیلی وٹس ایپ (WhatsApp Settings)</h2>';
        echo '<form method="post" action="">';
        wp_nonce_field('hp_settings_save', 'hp_settings_nonce');
        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
        echo '<label style="display: block; margin-bottom: 10px;">نیا وٹس ایپ نمبر درج کریں (ملکی کوڈ کے ساتھ، مثلاً 923001234567):</label>';
        echo '<input type="text" name="hp_whatsapp_number" value="' . esc_attr($wp_num) . '" style="width: 100%; max-width: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; direction: ltr; font-family: sans-serif;">';
        echo '</div>';
        echo '<button type="submit" name="hp_save_whatsapp" style="background: #1a73e8; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-family: \'Noto Nastaliq Urdu\', serif; border-radius: 5px; cursor: pointer;">محفوظ کریں (Save)</button>';
        echo '</form>';
        echo '</div>';
    } elseif ($active_tab === 'images') {
        // Change Images Tab
        if (isset($_POST['hp_save_images']) && check_admin_referer('hp_settings_save', 'hp_settings_nonce')) {
            update_option('hp_img_coating', esc_url_raw($_POST['hp_img_coating']));
            update_option('hp_img_chips', esc_url_raw($_POST['hp_img_chips']));
            update_option('hp_img_tile', esc_url_raw($_POST['hp_img_tile']));
            echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 15px; margin: 20px; border-radius: 5px; font-family: \'Noto Nastaliq Urdu\', serif;">تصاویر کامیابی سے اپڈیٹ ہو گئیں!</div>';
        }
        $img_coating = get_option('hp_img_coating', plugin_dir_url(__FILE__) . 'assets/img/solar_coating.png');
        $img_chips   = get_option('hp_img_chips', plugin_dir_url(__FILE__) . 'assets/img/solar_chips.png');
        $img_tile    = get_option('hp_img_tile', plugin_dir_url(__FILE__) . 'assets/img/solar_tiles_marble.png');

        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl;">';
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">تبدیلی تصویر (Product Images)</h2>';
        echo '<p style="color: #555; margin-bottom: 20px;">تصویر کا لنک (URL) یہاں پیسٹ کریں۔ آپ اپنی نئی تصویریں ورڈپریس کی میڈیا لائبریری (Media Library) میں اَپلوڈ کر کے ان کا لنک یہاں لگا سکتے ہیں۔</p>';
        echo '<form method="post" action="">';
        wp_nonce_field('hp_settings_save', 'hp_settings_nonce');
        
        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
        echo '<label style="display: block; margin-bottom: 10px;">1. سولر روف کوٹنگ تصویر کا لنک (URL):</label>';
        echo '<input type="url" name="hp_img_coating" value="' . esc_url($img_coating) . '" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; direction: ltr; font-family: sans-serif;"><br><br>';
        echo '<img src="'. esc_url($img_coating) .'" style="max-height: 100px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '</div>';

        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
        echo '<label style="display: block; margin-bottom: 10px;">2. سولر چپس تصویر کا لنک (URL):</label>';
        echo '<input type="url" name="hp_img_chips" value="' . esc_url($img_chips) . '" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; direction: ltr; font-family: sans-serif;"><br><br>';
        echo '<img src="'. esc_url($img_chips) .'" style="max-height: 100px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '</div>';

        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
        echo '<label style="display: block; margin-bottom: 10px;">3. منور ایئر کنڈیشننگ ٹائل تصویر کا لنک (URL):</label>';
        echo '<input type="url" name="hp_img_tile" value="' . esc_url($img_tile) . '" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; direction: ltr; font-family: sans-serif;"><br><br>';
        echo '<img src="'. esc_url($img_tile) .'" style="max-height: 100px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '</div>';

        echo '<button type="submit" name="hp_save_images" style="background: #1a73e8; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-family: \'Noto Nastaliq Urdu\', serif; border-radius: 5px; cursor: pointer;">محفوظ کریں (Save)</button>';
        echo '</form>';
        echo '</div>';
    } elseif ($active_tab === 'theme') {
        // Change Theme Tab
        if (isset($_POST['hp_save_theme']) && check_admin_referer('hp_settings_save', 'hp_settings_nonce')) {
            update_option('hp_theme_mode', sanitize_text_field($_POST['hp_theme_mode']));
            echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 15px; margin: 20px; border-radius: 5px; font-family: \'Noto Nastaliq Urdu\', serif;">تھیم کامیابی سے اپڈیٹ ہو گئی!</div>';
        }
        $theme_mode = get_option('hp_theme_mode', 'light');

        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl;">';
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">ایسٹیمیٹر کی تھیم (Theme Settings)</h2>';
        echo '<form method="post" action="">';
        wp_nonce_field('hp_settings_save', 'hp_settings_nonce');
        
        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
        echo '<label style="display: block; margin-bottom: 15px; font-size: 18px;">کہاں کیسی تھیم لگانی ہے؟</label>';
        
        $light_checked = ($theme_mode === 'light' || empty($theme_mode)) ? 'checked' : '';
        $dark_checked  = ($theme_mode === 'dark') ? 'checked' : '';
        
        echo '<label style="display: block; margin-bottom: 10px; cursor: pointer;">';
        echo '<input type="radio" name="hp_theme_mode" value="light" ' . $light_checked . '> وائٹ تھیم (Light Mode - Default)';
        echo '</label>';

        echo '<label style="display: block; margin-bottom: 10px; cursor: pointer;">';
        echo '<input type="radio" name="hp_theme_mode" value="dark" ' . $dark_checked . '> ڈارک تھیم (Dark Mode)';
        echo '</label>';
        
        echo '</div>';

        echo '<button type="submit" name="hp_save_theme" style="background: #1a73e8; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-family: \'Noto Nastaliq Urdu\', serif; border-radius: 5px; cursor: pointer;">محفوظ کریں (Save)</button>';
        echo '</form>';
        echo '</div>';
    } elseif ($active_tab === 'settings') {
        // Handle Save
        if (isset($_POST['hp_save_settings']) && check_admin_referer('hp_settings_save', 'hp_settings_nonce')) {
            if (!empty($_POST['hp_dynamic_products_json'])) {
                $json_data = wp_unslash($_POST['hp_dynamic_products_json']);
                $decoded = json_decode($json_data, true);
                if (is_array($decoded)) {
                    update_option('hp_dynamic_products', wp_json_encode($decoded, JSON_UNESCAPED_UNICODE));
                    echo '<div style="background: #e6f4ea; color: #1e8e3e; padding: 15px; margin: 20px; border-radius: 5px; font-family: \'Noto Nastaliq Urdu\', serif;">پروڈکٹس کامیابی سے محفوظ ہو گئیں!</div>';
                }
            }
        }

        $dynamic_products = hp_get_dynamic_products();
        $products_json = wp_json_encode($dynamic_products, JSON_UNESCAPED_UNICODE);

        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl;">';
        echo '<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">';
        echo '<h2 style="color: #1a73e8; margin: 0;">پروڈکٹس اور قیمتیں (Products & Prices)</h2>';
        echo '<button type="button" onclick="hpAddNewProduct()" style="background: #28a745; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-family: inherit;">+ نیا پروڈکٹ شامل کریں</button>';
        echo '</div>';

        echo '<form method="post" action="" id="hpProductsForm">';
        wp_nonce_field('hp_settings_save', 'hp_settings_nonce');
        echo '<input type="hidden" name="hp_dynamic_products_json" id="hp_dynamic_products_json" value="' . esc_attr($products_json) . '">';

        echo '<div id="hp-products-container"></div>';

        echo '<button type="submit" name="hp_save_settings" onclick="return hpPrepSave();" style="background: #1a73e8; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-family: \'Noto Nastaliq Urdu\', serif; border-radius: 5px; cursor: pointer; margin-top:20px;">محفوظ کریں (Save All)</button>';
        echo '</form>';
        echo '</div>';
        ?>
        <script>
            let hpProductsDB = <?php echo $products_json; ?>;
            
            function hpRenderEditor() {
                const container = document.getElementById('hp-products-container');
                container.innerHTML = '';
                
                hpProductsDB.forEach((prod, pIndex) => {
                    let html = `
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; position: relative;">
                            <button type="button" onclick="hpDeleteProduct(${pIndex})" style="position: absolute; left: 20px; top: 20px; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">پروڈکٹ ڈیلیٹ کریں</button>
                            
                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <div style="flex:1;">
                                    <label>پروڈکٹ کا نام:</label>
                                    <input type="text" value="${prod.name || ''}" oninput="hpUpdateField(${pIndex}, null, 'name', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                                <div style="flex:1;">
                                    <label>تصویر URL:</label>
                                    <input type="text" value="${prod.image_url || ''}" oninput="hpUpdateField(${pIndex}, null, 'image_url', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <div style="flex:1;">
                                    <label>یونٹ کا نام (مثال: بالٹیاں, بیگز):</label>
                                    <input type="text" value="${prod.unit_name || ''}" oninput="hpUpdateField(${pIndex}, null, 'unit_name', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                                <div style="flex:1;">
                                    <label>کوریج فیکٹر (مثال: 400، 12، 1):</label>
                                    <input type="number" value="${prod.coverage_divisor || 1}" oninput="hpUpdateField(${pIndex}, null, 'coverage_divisor', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <div style="flex:1;">
                                    <label>بیج (Badge - مثال: لائف 3 سے 5 سال):</label>
                                    <input type="text" value="${prod.badge || ''}" oninput="hpUpdateField(${pIndex}, null, 'badge', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                                <div style="flex:1;">
                                    <label>مختصر تفصیل / کوٹ (Quote - مثال: کم خرچ اور فوری اثر):</label>
                                    <input type="text" value="${prod.quote || ''}" oninput="hpUpdateField(${pIndex}, null, 'quote', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label>فائدے (ہر لائن میں ایک نیا فائدہ لکھیں):</label>
                                <textarea oninput="hpUpdateBenefits(${pIndex}, this.value)" style="width: 100%; padding: 8px; height: 80px;">${(prod.benefits || []).join('\n')}</textarea>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <div style="flex:1;">
                                    <label>ویڈیو لنک (YouTube URL):</label>
                                    <input type="text" value="${prod.video_url || ''}" oninput="hpUpdateField(${pIndex}, null, 'video_url', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                                <div style="flex:1;">
                                    <label>فیڈبیک ویڈیو (Feedback URL):</label>
                                    <input type="text" value="${prod.feedback_url || ''}" oninput="hpUpdateField(${pIndex}, null, 'feedback_url', this.value)" style="width: 100%; padding: 8px;">
                                </div>
                            </div>

                            <div style="margin-bottom: 15px; border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #fff;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="margin: 0; color: #333;">کوالٹی / ٹائپ (Variants)</h4>
                                    <button type="button" onclick="hpAddVariant(${pIndex})" style="background: #17a2b8; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">+ نئی کوالٹی بنائیں</button>
                                </div>
                                
                                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                                    ${prod.variants.map((variant, vIndex) => `
                                        <div style="flex: 1; min-width: 200px; border: 1px solid #eee; padding: 10px; border-radius: 4px; position:relative; background: #fafafa;">
                                            ${prod.variants.length > 1 ? `<button type="button" onclick="hpDeleteVariant(${pIndex}, ${vIndex})" style="position: absolute; left: 5px; top: 5px; background: transparent; border: none; color: #dc3545; cursor: pointer; font-size: 16px;">&times;</button>` : ''}
                                            <label>نام:</label>
                                            <input type="text" value="${variant.name || ''}" oninput="hpUpdateField(${pIndex}, ${vIndex}, 'name', this.value)" style="width: 100%; padding: 5px; margin-bottom: 8px;">
                                            <label>قیمت:</label>
                                            <input type="number" value="${variant.price || 0}" oninput="hpUpdateField(${pIndex}, ${vIndex}, 'price', this.value)" style="width: 100%; padding: 5px;">
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            }

            function hpUpdateField(p, v, field, val) {
                if (v === null) {
                    hpProductsDB[p][field] = val;
                } else {
                    hpProductsDB[p].variants[v][field] = field === 'price' ? parseFloat(val) || 0 : val;
                }
            }

            function hpUpdateBenefits(p, val) {
                hpProductsDB[p].benefits = val.split('\n').filter(l => l.trim() !== '');
            }

            function hpDeleteProduct(p) {
                if(confirm("کیا آپ واقعی یہ پروڈکٹ ڈیلیٹ کرنا چاہتے ہیں؟")) {
                    hpProductsDB.splice(p, 1);
                    hpRenderEditor();
                }
            }

            function hpAddNewProduct() {
                let newId = 'prod_' + Date.now();
                hpProductsDB.push({
                    id: newId,
                    name: "نیا پروڈکٹ",
                    image_url: "",
                    badge: "",
                    quote: "",
                    benefits: [],
                    unit_name: "یونٹ",
                    unit_desc: "",
                    coverage_divisor: 1,
                    video_url: "",
                    feedback_url: "",
                    whatsapp_type: newId,
                    variants: [
                        {id: 'var_' + Date.now(), name: "سٹینڈرڈ", price: 1000}
                    ]
                });
                hpRenderEditor();
            }

            function hpAddVariant(p) {
                hpProductsDB[p].variants.push({
                    id: 'var_' + Date.now(),
                    name: "نئی کوالٹی",
                    price: 0
                });
                hpRenderEditor();
            }

            function hpDeleteVariant(p, v) {
                if(confirm("کیا آپ واقعی یہ کوالٹی ڈیلیٹ کرنا چاہتے ہیں؟")) {
                    hpProductsDB[p].variants.splice(v, 1);
                    hpRenderEditor();
                }
            }

            function hpPrepSave() {
                document.getElementById('hp_dynamic_products_json').value = JSON.stringify(hpProductsDB);
                return true;
            }

            document.addEventListener('DOMContentLoaded', hpRenderEditor);
        </script>
        <?php
    } elseif ($active_tab === 'reports') {
        // Guarantee table exists for immediate testing without forcing the user to Deactivate/Reactivate
        hp_estimator_activate();

        global $wpdb;
        $table_name = $wpdb->prefix . 'hp_estimates';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 500", ARRAY_A);

        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl;">';
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">ایسٹیمیٹس کی ہسٹری (Reports)</h2>';
        
        if (empty($results)) {
            echo '<div style="background: #fff; padding: 30px; border-radius: 8px; text-align: center; color: #666; border: 1px solid #eee;">ابھی تک کوئی ایسٹیمیٹ محفوظ نہیں ہوا۔</div>';
        } else {
            echo '<div style="overflow-x: auto;">';
            echo '<table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; text-align: right;">';
            echo '<thead>';
            echo '<tr style="background: #f8f9fa; color: #333; text-align: right; border-bottom: 2px solid #dee2e6;">';
            echo '<th style="padding: 15px; font-weight: bold;">آئی ڈی</th>';
            echo '<th style="padding: 15px; font-weight: bold;">تاریخ اور وقت</th>';
            echo '<th style="padding: 15px; font-weight: bold;">رقبہ (Sq Ft)</th>';
            echo '<th style="padding: 15px; font-weight: bold;">ایسٹیمیٹ تفصیلات (لاگت)</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($results as $row) {
                echo '<tr style="border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background=\'#fdfdfd\'" onmouseout="this.style.background=\'transparent\'">';
                echo '<td style="padding: 15px; color: #666; vertical-align: top;">#' . esc_html($row['id']) . '</td>';
                echo '<td style="padding: 15px; font-family: sans-serif; direction: ltr; text-align: right; vertical-align: top;">' . esc_html(wp_date('M j, Y g:i A', strtotime($row['created_at']))) . '</td>';
                echo '<td style="padding: 15px; font-family: sans-serif; font-weight: bold; color: #1a73e8; vertical-align: top;">' . esc_html($row['area']) . '</td>';
                
                echo '<td style="padding: 15px; font-family: sans-serif; vertical-align: top;">';
                if (!empty($row['dynamic_costs'])) {
                    $costs = json_decode($row['dynamic_costs'], true);
                    if (is_array($costs)) {
                        foreach ($costs as $prod) {
                            echo '<div style="margin-bottom: 8px;"><strong>' . esc_html($prod['name'] ?? '') . ':</strong> ';
                            $variants_out = array();
                            if (!empty($prod['variants'])) {
                                foreach ($prod['variants'] as $v) {
                                    $variants_out[] = esc_html($v['name'] ?? '') . ' (Rs ' . number_format($v['cost'] ?? 0) . ')';
                                }
                            }
                            echo implode(', ', $variants_out);
                            echo '</div>';
                        }
                    }
                } else {
                    // Fallback for old static records
                    echo '<div style="font-size: 13px; color: #555;">';
                    echo 'Coating: Rs ' . number_format($row['coating_cost']) . '<br>';
                    echo 'Chips Special: Rs ' . number_format($row['chips_special_cost']) . '<br>';
                    echo 'Chips Super: Rs ' . number_format($row['chips_super_cost']) . '<br>';
                    echo 'Chips Economy: Rs ' . number_format($row['chips_economy_cost']) . '<br>';
                    echo 'Tile MAT: Rs ' . number_format($row['tile_mat_cost']) . '<br>';
                    echo 'Tile Summer: Rs ' . number_format($row['tile_summer_cost']);
                    echo '</div>';
                }
                echo '</td>';
                
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div style="padding: 30px; font-family: \'Noto Nastaliq Urdu\', serif; direction: rtl; max-width: 800px; margin: 0 auto;">';
        
        echo '<h2 style="color: #1a73e8; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">ہیٹ پروفنگ ایسٹیمیٹر - استعمال کا طریقہ (Help Guide)</h2>';
        
        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0;">1. مین کیلکولیٹر (ایسٹیمیٹ لگائیں)</h3>';
        echo '<p style="line-height: 1.8; color: #555; font-size: 16px;">یہ پلگ ان کا مرکزی حصہ ہے۔ یہاں آپ یا آپ کا کسٹمر صرف اپنی چھت کا رقبہ (مربع فٹ میں) لکھتا ہے اور یہ پلگ ان خود بخود تینوں پروڈکٹس (کوٹنگ، چپس، ٹائلز) کا مکمل ایسٹیمیٹ نکال کر دے دیتا ہے۔</p>';
        echo '<ul style="line-height: 1.8; color: #555; font-size: 16px; padding-right: 20px;">';
        echo '<li><b>پرنٹ اور پی ڈی ایف:</b> کسٹمر ایک بٹن دبا کر اس ایسٹیمیٹ کا پرنٹ لے سکتا ہے یا اپنے موبائل میں PDF کے طور پر محفوظ کر سکتا ہے۔</li>';
        echo '<li><b>واٹس ایپ پر شئیر:</b> کسٹمر "WhatsApp پر معلومات بھیجیں" پر کلک کر کے آپ کو سیدھا واٹس ایپ پر آرڈر یا تفصیل بھیج سکتا ہے۔</li>';
        echo '</ul>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0;">2. قیمتیں تبدیل کرنا (Settings)</h3>';
        echo '<p style="line-height: 1.8; color: #555; font-size: 16px;">اب آپ کو قیمتیں بدلنے کے لیے کسی پروگرامر کی ضرورت نہیں ہے۔ بائیں جانب موجود <b>\'سیٹنگز\'</b> کے بٹن پر کلک کریں۔ یہاں آپ کو تمام پروڈکٹس کی موجودہ قیمتیں نظر آئیں گی:</p>';
        echo '<ul style="line-height: 1.8; color: #555; font-size: 16px; padding-right: 20px;">';
        echo '<li>کوٹنگ کی فی بالٹی قیمت</li>';
        echo '<li>چپس کے بیگز کی 3 مختلف کوالٹیز کے حساب سے قیمت</li>';
        echo '<li>ٹائلز کی فی مربع فٹ قیمت</li>';
        echo '</ul>';
        echo '<p style="line-height: 1.8; color: #555; font-size: 16px;">نئی قیمت لکھ کر <b>\'محفوظ کریں\'</b> کا بٹن دبائیں اور کیلکولیٹر فوری طور پر نئی قیمتوں کے مطابق ایسٹیمیٹ بنانا شروع کر دے گا۔</p>';
        echo '</div>';

        echo '<div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">';
        echo '<h3 style="color: #333; margin-top: 0;">3. پرانے ایسٹیمیٹس کا ریکارڈ (Reports)</h3>';
        echo '<p style="line-height: 1.8; color: #555; font-size: 16px;">جیسے ہی کوئی کسٹمر اپنی چھت کا رقبہ بتا کر نیا ایسٹیمیٹ نکالتا ہے، اس کا سارا ریکارڈ بیک گراؤنڈ میں محفوظ ہو جاتا ہے۔ یہ ریکارڈ دیکھنے کے لیے بائیں جانب موجود <b>\'رپورٹس\'</b> کے ٹیب پر کلک کریں۔ یہاں آپ کو تمام پچھلے ایسٹیمیٹس بمعہ تاریخ، وقت اور لاگت کے نظر آئیں گے جس سے آپ اپنے بزنس کا اندازہ لگا سکتے ہیں۔</p>';
        echo '</div>';

        echo '<div style="background: #eaf1fa; border-right: 4px solid #1a73e8; padding: 20px; border-radius: 4px;">';
        echo '<h3 style="color: #1a73e8; margin-top: 0; font-size: 18px;">شارٹ کوڈ (ویب سائٹ پر لگانے کا طریقہ)</h3>';
        echo '<p style="line-height: 1.8; color: #333; font-size: 16px;">اگر آپ اپنی ورڈپریس ویب سائٹ کے کسی بھی پیج یا پوسٹ پر یہ کیلکولیٹر دکھانا چاہتے ہیں، تو بس یہ شارٹ کوڈ کاپی کر کے وہاں پیسٹ کر دیں:</p>';
        echo '<div style="background: #fff; padding: 10px 15px; border: 1px solid #cddc39; display: inline-block; font-family: monospace; font-size: 18px; border-radius: 4px; direction: ltr;">[heatproofing_estimator]</div>';
        echo '</div>';

        echo '</div>';
    }

    echo '  </div>';
    echo '</div>';
}
