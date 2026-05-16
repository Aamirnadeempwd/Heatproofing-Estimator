// Keep raw values for WhatsApp messaging
let currentEstimates = {
    area: 0,
    dynamic: {}
};

document.addEventListener('DOMContentLoaded', function () {
    // Theme Toggle Logic
    const container = document.getElementById('printableArea');
    const themeToggleBtn = document.getElementById('hpThemeToggle');
    const themeToggleText = document.getElementById('hpThemeToggleText');

    // Check localStorage for saved preference, overriding PHP default if exists
    const savedTheme = localStorage.getItem('hp_theme_mode');
    if (savedTheme === 'dark') {
        container.classList.add('hp-dark-theme');
    } else if (savedTheme === 'light') {
        container.classList.remove('hp-dark-theme');
    }

    // Update button text based on current state
    if (container && themeToggleText) {
        themeToggleText.innerText = container.classList.contains('hp-dark-theme') ? 'وائٹ تھیم' : 'ڈارک تھیم';
    }

    if (themeToggleBtn && container) {
        themeToggleBtn.addEventListener('click', function () {
            container.classList.toggle('hp-dark-theme');
            const isDark = container.classList.contains('hp-dark-theme');
            localStorage.setItem('hp_theme_mode', isDark ? 'dark' : 'light');
            if (themeToggleText) {
                themeToggleText.innerText = isDark ? 'وائٹ تھیم' : 'ڈارک تھیم';
            }
        });
    }

    const calculateBtn = document.getElementById('hpCalculateBtn');

    if (calculateBtn) {
        calculateBtn.addEventListener('click', function () {
            calculateEstimates();
        });
    }

    const areaInput = document.getElementById('roofArea');
    if (areaInput) {
        areaInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                calculateEstimates();
            }
        });
    }
});

function calculateEstimates() {
    const areaInput = document.getElementById('roofArea').value;
    const errorDiv = document.getElementById('hpError');
    const resultsDiv = document.getElementById('hpResults');

    if (!areaInput || areaInput <= 0) {
        errorDiv.classList.remove('hidden');
        resultsDiv.classList.add('hidden');
        return;
    }

    const area = parseFloat(areaInput);
    currentEstimates.area = area;
    errorDiv.classList.add('hidden');

    // Fetch dynamic prices from localized WordPress object
    const customProducts = (typeof hpSettings !== 'undefined' && hpSettings.customProducts) ? hpSettings.customProducts : [];
    
    let dynamicCostsObj = {};

    customProducts.forEach(prod => {
        const id = prod.id;
        const div = parseFloat(prod.coverage_divisor) || 1;
        const totalQuantity = (div === 1) ? area : Math.ceil(area / div);
        
        let prodStore = {
            name: prod.name,
            unitName: prod.unit_name,
            totalQuantity: totalQuantity,
            variants: {}
        };

        if (prod.variants.length === 1) {
            const variant = prod.variants[0];
            const cost = totalQuantity * parseFloat(variant.price);
            
            prodStore.variants[variant.id] = { name: variant.name, price: variant.price, cost: cost };
            
            // update UI
            const qtyEl = document.getElementById(id + '_quantity');
            if (qtyEl) qtyEl.innerText = totalQuantity;
            const costEl = document.getElementById(id + '_cost_' + variant.id);
            if (costEl) costEl.innerText = formatCurrency(cost);
        } else {
            prod.variants.forEach(variant => {
                const cost = totalQuantity * parseFloat(variant.price);
                prodStore.variants[variant.id] = { name: variant.name, price: variant.price, cost: cost };
                
                const qtyEl = document.getElementById(id + '_quantity_' + variant.id);
                if (qtyEl) qtyEl.innerText = totalQuantity;
                const costEl = document.getElementById(id + '_cost_' + variant.id);
                if (costEl) costEl.innerText = formatCurrency(cost);
            });
        }
        
        currentEstimates.dynamic[id] = prodStore;
        dynamicCostsObj[id] = prodStore;
    });

    // Show results
    resultsDiv.classList.remove('hidden');

    setTimeout(() => {
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);

    // Save strictly to the backend database via AJAX silently
    if (typeof hpSettings !== 'undefined' && typeof jQuery !== 'undefined' && hpSettings.ajaxUrl) {
        jQuery.post(hpSettings.ajaxUrl, {
            action: 'hp_save_estimate',
            area: area,
            dynamic_costs: JSON.stringify(dynamicCostsObj)
        });
    }
}

function formatCurrency(num) {
    return num.toLocaleString('en-PK');
}

window.sendWhatsApp = function (productType) {
    if (!currentEstimates.area || currentEstimates.area <= 0) {
        alert("براہ کرم پہلے رقبہ درج کریں۔");
        return;
    }

    const prodData = currentEstimates.dynamic[productType];
    if (!prodData) return;

    let productName = prodData.name;
    let variantDetails = [];
    
    for (let vId in prodData.variants) {
        let v = prodData.variants[vId];
        if (Object.keys(prodData.variants).length === 1 && (!v.name || v.name === "سٹینڈرڈ")) {
            variantDetails.push("قیمت: " + formatCurrency(v.cost) + " روپے");
        } else {
            variantDetails.push(v.name + ": " + formatCurrency(v.cost) + " روپے");
        }
    }
    
    let productDetails = "(" + variantDetails.join('، ') + ")";

    const msg = `السلام علیکم، میں نے آپ کی ویب سائٹ پر اپنی چھت کا رقبہ (${currentEstimates.area} مربع فٹ) درج کر کے ہیٹ پروفنگ کا تخمینہ لگایا ہے۔\n\nمیں *${productName}* کے حوالے سے آپ کی سروس حاصل کرنے میں دلچسپی رکھتا/رکھتی ہوں۔\nاس ایسٹیمیٹ کے مطابق بل یہ بن رہا ہے: ${productDetails}\n\nبراہ کرم مزید رہنمائی فرمائیں۔`;

    const whatsappNumber = (typeof hpSettings !== 'undefined' && hpSettings.whatsappNumber) ? hpSettings.whatsappNumber : "923006786890";
    // Strip any non-numeric characters for the wa.me link
    const cleanNumber = whatsappNumber.replace(/\D/g, '');
    const whatsappUrl = `https://wa.me/${cleanNumber}?text=${encodeURIComponent(msg)}`;

    window.open(whatsappUrl, '_blank');
};

// Print Function (Reliable Centering)
window.printEstimate = function () {
    const printArea = document.getElementById('printableArea');
    const originalParent = printArea.parentNode;
    const originalNextSibling = printArea.nextSibling;

    // Move printable area directly to body to escape WordPress theme relative constraints
    document.body.appendChild(printArea);

    // Add a printing class to body to activate our specific CSS rules
    document.body.classList.add('hp-is-printing');

    // Add static area text for print
    const areaInput = document.getElementById('roofArea');
    const printText = document.createElement('div');
    printText.id = 'temp-print-text';
    printText.innerText = `کل رقبہ: ${areaInput.value} مربع فٹ`;
    printText.style.fontWeight = "900";
    printText.style.fontSize = "22px";
    printText.style.color = "#000000";
    printText.style.textAlign = "center";
    printText.style.marginBottom = "10px";
    printText.style.borderBottom = "2px dashed #000";
    printText.style.paddingBottom = "5px";
    printText.dir = "rtl";

    const inputGroup = document.querySelector('#printableArea .hp-input-group');
    if (inputGroup) {
        inputGroup.parentNode.insertBefore(printText, inputGroup);
    }

    // Give browser a split second to render the DOM change before freezing for print dialog
    // Modern browsers allow synchronous print if initiated by user click, 
    // but sometimes need a tick to repaint the newly appended DOM.
    setTimeout(() => {
        window.print();

        // Restore everything immediately after the print dialog closes
        if (printText.parentNode) {
            printText.parentNode.removeChild(printText);
        }
        document.body.classList.remove('hp-is-printing');
        if (originalNextSibling) {
            originalParent.insertBefore(printArea, originalNextSibling);
        } else {
            originalParent.appendChild(printArea);
        }
    }, 50);
};

// PDF Generation Function
window.generatePDF = function () {
    const element = document.getElementById('printableArea');

    // 1. Inject a ruthless CSS block to force dark colors and bold text
    // Nastaleeq fonts are thin and can render light/faded in Canvas.
    const style = document.createElement('style');
    style.id = 'temp-pdf-style';
    style.innerHTML = `
        /* Lock width to a standard size so canvas scaling is consistent */
        #printableArea { background: #ffffff !important; padding: 15px !important; width: 800px !important; margin: 0 auto !important; box-sizing: border-box; }
        #printableArea * { 
            color: #000000 !important; 
            opacity: 1 !important;
            font-weight: 900 !important; /* Force bold to prevent fading */
            text-shadow: none !important;
        }
        #printableArea .hp-title { color: #1a73e8 !important; font-size: 26px !important; }
        #printableArea .hp-price { color: #d32f2f !important; border-bottom: 2px solid #d32f2f !important; display: inline-block; }
        #printableArea .hp-card, #printableArea .hp-variant { border: 2px solid #000000 !important; background: #ffffff !important; }
        #printableArea .hp-badge, #printableArea .hp-badge-lifetime { border: 2px solid #000000 !important; }
        #printableArea .hp-social-section { border: 2px solid #1a73e8 !important; background: #f8f9fa !important; }
        #printableArea .hp-social-grid a { border: 1px solid #000000 !important; color: #000000 !important; }
        #printableArea .hp-powered-by { color: #000000 !important; border-top: 1px solid #000000 !important; }
        
        /* Hide things we don't want */
        #printableArea .no-print, 
        #printableArea .hp-input-group, 
        #printableArea .hp-btn-whatsapp, 
        #printableArea .hp-btn-video,
        #printableArea .pdf-actions {
            display: none !important;
        }
        
        /* Compress spacing for 1 page */
        #printableArea .hp-card { margin-bottom: 8px !important; padding: 10px !important; }
        #printableArea .hp-variant { margin-bottom: 5px !important; padding: 5px !important; }
        #printableArea .hp-header { margin-bottom: 5px !important; padding-bottom: 5px !important; }
        #printableArea h3 { padding-bottom: 2px !important; margin-bottom: 2px !important; }
        #printableArea p { margin: 2px 0 !important; }
    `;
    document.head.appendChild(style);

    // 2. Insert static text for Area, hide original input area completely
    const areaInput = document.getElementById('roofArea');
    const printText = document.createElement('div');
    // Removed English 'Sq Ft' to prevent RTL text overlapping
    printText.innerText = `کل رقبہ: ${areaInput.value} مربع فٹ`;
    printText.style.fontWeight = "900";
    printText.style.fontSize = "22px";
    printText.style.color = "#000000";
    printText.style.textAlign = "center";
    printText.style.marginBottom = "10px";
    printText.style.borderBottom = "2px dashed #000";
    printText.style.paddingBottom = "5px";
    printText.dir = "rtl";

    const inputGroup = document.querySelector('.hp-input-group');
    inputGroup.parentNode.insertBefore(printText, inputGroup);

    // Force a small delay to let browser reflow the injected styles before taking the picture
    setTimeout(() => {
        // 3. Take a high-quality picture of the container
        window.html2canvas(element, {
            scale: 2, // High resolution for sharp text
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            // 4. Force fit the picture rigidly to A4 width, allowing multiple pages for height
            const imgData = canvas.toDataURL('image/jpeg', 1.0);

            // A4 size dimensions in mm
            const pdfWidth = 210;
            const pdfHeight = 297;

            // Margins
            const margin = 10;
            const usableWidth = pdfWidth - (margin * 2);
            const usableHeight = pdfHeight - (margin * 2);

            // Maintain aspect ratio to find the height needed
            const imgRatio = canvas.width / canvas.height;
            const finalWidth = usableWidth;
            const finalHeight = finalWidth / imgRatio;

            // Create PDF
            const pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');

            // Draw the image, paginating it if it exceeds one page height
            let heightLeft = finalHeight;
            let position = margin;

            // First page
            pdf.addImage(imgData, 'JPEG', margin, position, finalWidth, finalHeight);
            heightLeft -= usableHeight;

            // Remaining pages
            while (heightLeft > 0) {
                position = position - usableHeight - margin; // Shift image UP by a full page
                pdf.addPage();
                pdf.addImage(imgData, 'JPEG', margin, position, finalWidth, finalHeight);
                heightLeft -= usableHeight;
            }

            pdf.save('heatproofing-estimate.pdf');

            // 5. Cleanup
            document.head.removeChild(style);
            printText.parentNode.removeChild(printText);
        });
    }, 200);
}

// Intercept Ctrl+P to optionally trigger PDF download or print this section directly
document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'p') {
        const resultsDiv = document.getElementById('hpResults');
        if (resultsDiv && !resultsDiv.classList.contains('hidden')) {
            event.preventDefault();
            window.printEstimate();
        }
    }
});

// FAQ Accordion Logic
document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.hp-faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');

            // Close all answers
            document.querySelectorAll('.hp-faq-answer').forEach(ans => {
                ans.style.maxHeight = null;
            });
            document.querySelectorAll('.hp-faq-question').forEach(q => {
                q.classList.remove('active');
            });

            // If it wasn't active, open it
            if (!isActive) {
                question.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + "px";
            }
        });
    });
});

// Share Functions
window.shareEstimator = function(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent("سوبر ٹیکنالوجیز ہیٹ پروفنگ ایسٹیمیٹر");
    const text = encodeURIComponent("اپنے گھر کی ہیٹ پروفنگ کا تخمینہ آسانی سے لگائیں:");
    
    let shareUrl = "";
    
    if (platform === 'whatsapp') {
        shareUrl = `https://api.whatsapp.com/send?text=${title}%0A${text}%0A${url}`;
    } else if (platform === 'facebook') {
        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    } else if (platform === 'twitter') {
        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
};

window.copyEstimatorLink = function() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert("لنک کاپی ہو گیا!");
    }).catch(err => {
        console.error('Copy failed: ', err);
    });
};
