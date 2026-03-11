// Keep raw values for WhatsApp messaging
let currentEstimates = {
    area: 0,
    coating: 0,
    chipsSpecial: 0,
    chipsSuper: 0,
    chipsEconomy: 0,
    tileMat: 0,
    tileSummer: 0
};

document.addEventListener('DOMContentLoaded', function () {
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

    // --- Product 1: Solar Roof Coating ---
    const coatingBuckets = Math.ceil(area / 400);
    const coatingCost = coatingBuckets * 7500;
    currentEstimates.coating = coatingCost;

    document.getElementById('coatingBuckets').innerText = coatingBuckets;
    document.getElementById('coatingCost').innerText = formatCurrency(coatingCost);

    // --- Product 2: Solar Chips ---
    const chipsBags = Math.ceil(area / 12);

    const chipsSpecialCost = chipsBags * 2800;
    const chipsSuperCost = chipsBags * 2300;
    const chipsEconomyCost = chipsBags * 1800;

    currentEstimates.chipsSpecial = chipsSpecialCost;
    currentEstimates.chipsSuper = chipsSuperCost;
    currentEstimates.chipsEconomy = chipsEconomyCost;

    document.getElementById('chipsSpecialBags').innerText = chipsBags;
    document.getElementById('chipsSpecialCost').innerText = formatCurrency(chipsSpecialCost);

    document.getElementById('chipsSuperBags').innerText = chipsBags;
    document.getElementById('chipsSuperCost').innerText = formatCurrency(chipsSuperCost);

    document.getElementById('chipsEconomyBags').innerText = chipsBags;
    document.getElementById('chipsEconomyCost').innerText = formatCurrency(chipsEconomyCost);

    // --- Product 3: Solar Tiles ---
    const tileMatCost = area * 300;
    const tileSummerCost = area * 250;

    currentEstimates.tileMat = tileMatCost;
    currentEstimates.tileSummer = tileSummerCost;

    document.getElementById('tileMatCost').innerText = formatCurrency(tileMatCost);
    document.getElementById('tileSummerCost').innerText = formatCurrency(tileSummerCost);

    // Show results
    resultsDiv.classList.remove('hidden');

    setTimeout(() => {
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

function formatCurrency(num) {
    return num.toLocaleString('en-PK');
}

window.sendWhatsApp = function (productType) {
    if (!currentEstimates.area || currentEstimates.area <= 0) {
        alert("براہ کرم پہلے رقبہ درج کریں۔");
        return;
    }

    let productDetails = "";
    let productName = "";

    if (productType === 'coating') {
        productName = "سولر روف کوٹنگ";
        productDetails = `(قیمت: ${formatCurrency(currentEstimates.coating)} روپے)`;
    } else if (productType === 'chips') {
        productName = "سولر چپس (ٹھنڈی چپس)";
        productDetails = `(سپیشل کوالٹی: ${formatCurrency(currentEstimates.chipsSpecial)} روپے، سپر فیس: ${formatCurrency(currentEstimates.chipsSuper)} روپے، اکانومی: ${formatCurrency(currentEstimates.chipsEconomy)} روپے)`;
    } else if (productType === 'tiles') {
        productName = "سولر ٹائل";
        productDetails = `(منور ائرکنڈیشننگ ٹائل: ${formatCurrency(currentEstimates.tileMat)} روپے، صرف گرمی والی ٹائل: ${formatCurrency(currentEstimates.tileSummer)} روپے)`;
    }

    const msg = `السلام علیکم، میں نے آپ کی ویب سائٹ پر اپنی چھت کا رقبہ (${currentEstimates.area} مربع فٹ) درج کر کے ہیٹ پروفنگ کا تخمینہ لگایا ہے۔\n\nمیں *${productName}* کے حوالے سے آپ کی سروس حاصل کرنے میں دلچسپی رکھتا/رکھتی ہوں۔\nاس ایسٹیمیٹ کے مطابق بل یہ بن رہا ہے: ${productDetails}\n\nبراہ کرم مزید رہنمائی فرمائیں۔`;

    const whatsappNumber = "923006786890";
    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(msg)}`;

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
