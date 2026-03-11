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

function hp_estimator_enqueue_scripts($hook) {
    // Only load on the admin page or front-end shortcode
    $is_admin_page = is_admin() && 'toplevel_page_heatproofing-estimator' === $hook;
    $is_frontend = !is_admin();

    if ($is_admin_page || $is_frontend) {
        wp_enqueue_style( 'hp-estimator-style', plugins_url( 'assets/css/style.css', __FILE__ ) );
        wp_enqueue_script( 'hp-estimator-script', plugins_url( 'assets/js/script.js', __FILE__ ), array(), '1.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'hp_estimator_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'hp_estimator_enqueue_scripts' );


function hp_estimator_html() {
    ob_start(); ?>
    <div class="hp-estimator-container" id="printableArea" dir="rtl">
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

            <!-- Product 1: Solar Roof Coating -->
            <div class="hp-card">
                <h3>1. آپشن 1: سولر روف کوٹنگ (معیاری تحفظ)</h3>
                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/solar_coating.png'; ?>" alt="Solar Roof Coating" class="hp-product-img">
                <div class="hp-product-meta">
                    <span class="hp-badge">لائف: 3 سے 5 سال کی بہترین لائف</span>
                    <span class="hp-quote">"کم خرچ اور فوری اثر کے لیے بہترین انتخاب"</span>
                </div>
                <div class="hp-estimate-row">
                    <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="coatingBuckets" class="eng-num">0</span> بالٹیاں <span class="small-tag">(27 کلوگرام فی بالٹی • ڈبل کوٹ)</span></p>
                    <p class="hp-price-inline">قیمت: <span id="coatingCost" class="eng-num">0</span> روپے</p>
                </div>
                <div class="hp-details">
                    <p class="hp-ben-title"><strong>فائدے:</strong></p>
                    <ul class="hp-ben-list">
                        <li>اے سی کے استعمال میں 50 فیصد تک کمی</li>
                        <li>سورج کی شعاعوں کو 90% تک واپس منعکس (Reflect) کرتا ہے۔</li>
                        <li>درجہ حرارت میں 8°C سے 12°C تک نمایاں کمی۔</li>
                        <li>لگوانے کے بعد آپ تپتی دوپہر میں بھی چھت پر ننگے پاؤں چل سکتے ہیں۔</li>
                    </ul>
                </div>

                <div class="hp-video-group no-print">
                    <a href="https://youtu.be/3g146_LddLo?si=Yp8QUq8paM0ipttl" target="_blank" class="hp-btn hp-btn-video">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        طریقہ کار دیکھیں
                    </a>
                    <a href="https://youtube.com/playlist?list=PLrpsO5O4fToxscszum7wozp-UVZRqUBHn&si=CjgNWqXgtIaKwJ5d" target="_blank" class="hp-btn hp-btn-feedback">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.858 1.4-8.168-5.934-5.787 8.2-1.192z"/></svg>
                        کسٹمرز کی رائے
                    </a>
                </div>

                <button class="hp-btn hp-btn-whatsapp no-print" onclick="sendWhatsApp('coating')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                    WhatsApp پر معلومات بھیجیں
                </button>
            </div>

            <!-- Product 2: Solar Chips -->
            <div class="hp-card">
                <h3>2. آپشن 2: سولر چپس (پریمیم تحفظ)</h3>
                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/solar_chips.png'; ?>" alt="Solar Chips" class="hp-product-img">
                <div class="hp-product-meta">
                    <span class="hp-badge-lifetime">وارنٹی: لائف ٹائم پائیداری اور گارنٹی</span>
                    <span class="hp-quote">"مضبوطی اور ٹھنڈک کا بہترین امتزاج"</span>
                </div>
                <div class="hp-details">
                    <p class="hp-ben-title"><strong>فائدے:</strong></p>
                    <ul class="hp-ben-list">
                        <li>100% واٹر پروفنگ اور ہیٹ پروفنگ کا دوہرا تحفظ۔</li>
                        <li>چھت سے آنے والی تپش کو سو فیصد روکے۔</li>
                        <li>گھر کی چھتوں کو سیم اور نمی سے ہمیشہ کے لیے محفوظ رکھے۔</li>
                        <li>بجلی کے بھاری بلوں (AC کے استعمال) میں زبردست کمی۔</li>
                    </ul>
                </div>

                <div class="hp-variant">
                    <h4>سپیشل کوالٹی</h4>
                    <div class="hp-estimate-row">
                        <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="chipsSpecialBags" class="eng-num">0</span> بیگز <span class="small-tag">(40 کلوگرام فی بیگ)</span></p>
                        <p class="hp-price-inline">قیمت: <span id="chipsSpecialCost" class="eng-num">0</span> روپے</p>
                    </div>
                </div>
                <div class="hp-variant">
                    <h4>سپر فیس</h4>
                    <div class="hp-estimate-row">
                        <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="chipsSuperBags" class="eng-num">0</span> بیگز <span class="small-tag">(40 کلوگرام فی بیگ)</span></p>
                        <p class="hp-price-inline">قیمت: <span id="chipsSuperCost" class="eng-num">0</span> روپے</p>
                    </div>
                </div>
                <div class="hp-variant">
                    <h4>اکانومی کلاس</h4>
                    <div class="hp-estimate-row">
                        <p class="hp-quantity-inline"><strong>درکار مقدار:</strong> <span id="chipsEconomyBags" class="eng-num">0</span> بیگز <span class="small-tag">(40 کلوگرام فی بیگ)</span></p>
                        <p class="hp-price-inline">قیمت: <span id="chipsEconomyCost" class="eng-num">0</span> روپے</p>
                    </div>
                </div>

                <div class="hp-video-group no-print">
                    <a href="https://youtu.be/FInGb1ymo4E?si=9hlX9FK3jZxKpInk" target="_blank" class="hp-btn hp-btn-video">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        طریقہ کار دیکھیں
                    </a>
                    <a href="https://youtu.be/dyVnjFvLzVw?si=npyOXQViDHKfZb10" target="_blank" class="hp-btn hp-btn-feedback">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.858 1.4-8.168-5.934-5.787 8.2-1.192z"/></svg>
                        کسٹمر فیڈ بیک
                    </a>
                </div>

                <button class="hp-btn hp-btn-whatsapp no-print" onclick="sendWhatsApp('chips')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                    WhatsApp پر معلومات بھیجیں
                </button>
            </div>

            <!-- Product 3: Solar Tiles -->
            <div class="hp-card">
                <h3>3. آپشن 3: سولر ٹائل (لگژری تحفظ)</h3>
                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/solar_tiles.png'; ?>" alt="Solar Tiles" class="hp-product-img">
                <div class="hp-product-meta">
                    <span class="hp-badge-lifetime">وارنٹی: لائف ٹائم گارنٹی (Premium Quality)</span>
                    <span class="hp-quote">"خوبصورتی اور جدید ٹیکنالوجی سے آراستہ"</span>
                </div>
                <div class="hp-details">
                    <p class="hp-ben-title"><strong>فائدے:</strong></p>
                    <ul class="hp-ben-list">
                        <li>ماربل جیسی خوبصورت فنشنگ جو گھر کی ویلیو بڑھائے۔</li>
                        <li>شدید ترین گرمی میں بھی کمروں کو قدرتی طور پر ٹھنڈا رکھے۔</li>
                        <li>اے سی کے استعمال میں 50 فیصد تک کمی۔</li>
                        <li>صاف کرنے میں آسان اور انتہائی پائیدار میٹریل۔</li>
                    </ul>
                </div>

                <div class="hp-variant">
                    <h4>منور ائرکنڈیشننگ ٹائل (MAT) <span class="hp-badge" style="background: #bbdefb; color: #1565c0;">گرمی + سردی</span></h4>
                    <p class="hp-price">قیمت: <span id="tileMatCost" class="eng-num">0</span> روپے</p>
                </div>
                <div class="hp-variant">
                    <h4>سولر ٹائل <span class="hp-badge" style="background: #fff9c4; color: #f57f17;">صرف گرمی لیے</span></h4>
                    <p class="hp-price">قیمت: <span id="tileSummerCost" class="eng-num">0</span> روپے</p>
                </div>

                <div class="hp-video-group no-print">
                    <a href="https://youtu.be/qxibDJzYfCE?si=G4rlKj-qjYQXEdw9" target="_blank" class="hp-btn hp-btn-video">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        طریقہ کار دیکھیں
                    </a>
                    <a href="https://youtu.be/YbE2qZqG6Y8?si=IsZ6LLtuokDNHHBd" target="_blank" class="hp-btn hp-btn-feedback">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.858 1.4-8.168-5.934-5.787 8.2-1.192z"/></svg>
                        کسٹمر فیڈ بیک
                    </a>
                </div>

                <button class="hp-btn hp-btn-whatsapp no-print" onclick="sendWhatsApp('tiles')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                    WhatsApp پر معلومات بھیجیں
                </button>
            </div>

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
    echo '<div class="wrap">';
    echo hp_estimator_html();
    echo '</div>';
}
