const fs = require('fs');

const files = fs.readdirSync('.').filter(f => f.endsWith('.html') && f !== 'edukasi.html' && f !== 'index.html');

function processFile(file) {
    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    // 1. Add 'edukasi.html' to PAGES_FRONTLINE if not already there
    if (content.includes("PAGES_FRONTLINE") && !content.includes("'edukasi.html'")) {
        content = content.replace(
            /const PAGES_FRONTLINE\s*=\s*\[([^\]]+)\]/g,
            (match, inner) => {
                // Add edukasi.html to the array
                const trimmed = inner.trimEnd();
                if (trimmed.endsWith("'")) {
                    return match.replace(inner, inner.trimEnd() + ",'edukasi.html'");
                }
                return match;
            }
        );
    }

    // 2. Add navItem for Edukasi & SOP in frontline section (after laporan_staff.html line)
    if (content.includes("navItem") && !content.includes("Edukasi & SOP")) {
        content = content.replace(
            /(navItem\('🚀','Laporan Penjualan','laporan_staff\.html'\);)/g,
            "$1\n    let edukasiItem = navItem('📚','Edukasi & SOP','edukasi.html');\n    frontline += edukasiItem;"
        );

        // Alternative pattern without semicolon at end of laporan line (some files may differ)
        if (!content.includes("Edukasi & SOP")) {
            content = content.replace(
                /(navItem\('🚀','Laporan Penjualan','laporan_staff\.html'\)'?;)/g,
                "$1\n    frontline += navItem('📚','Edukasi & SOP','edukasi.html');"
            );
        }
    }

    // 3. Add edukasi to buildMoreMenu items (after restock push)
    if (content.includes("buildMoreMenu") && !content.includes("Edukasi & SOP")) {
        content = content.replace(
            /(items\s*=\s*\[\{icon:'🚚',label:'Restock',link:'restock\.html'\}\])/g,
            "$1;\n    items.push({icon:'📚',label:'Edukasi & SOP',link:'edukasi.html'})"
        );

        // Alt pattern: push after initial array
        if (!content.includes("Edukasi & SOP")) {
            content = content.replace(
                /(const items = \[\{icon:'🚚',label:'Restock',link:'restock\.html'\}\];)/g,
                "$1\n    items.push({icon:'📚',label:'Edukasi & SOP',link:'edukasi.html'});"
            );
        }
    }

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        console.log('Updated sidebar in: ' + file);
    }
}

for (const file of files) {
    processFile(file);
}
console.log('Sidebar update complete.');
