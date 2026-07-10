const fs = require('fs');

const files = fs.readdirSync('.').filter(f => f.endsWith('.html') && f !== 'edukasi.html' && f !== 'index.html');

function processFile(file) {
    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    // Find the buildMoreMenu function and add edukasi after the Restock line
    const moreMenuIdx = content.indexOf('buildMoreMenu');
    if (moreMenuIdx === -1) return;
    
    // Check if edukasi is already in the moreMenu section
    const moreMenuSection = content.substring(moreMenuIdx, moreMenuIdx + 2000);
    if (moreMenuSection.includes("'Edukasi")) return;

    // Insert after: const items = [{icon:'🚚',label:'Restock',link:'restock.html'}];
    // We need to find this line AFTER buildMoreMenu
    const searchFrom = content.indexOf("const items = [{icon:'🚚'", moreMenuIdx);
    if (searchFrom === -1) return;

    const lineEnd = content.indexOf('];', searchFrom);
    if (lineEnd === -1) return;

    const insertPos = lineEnd + 2; // after ];
    content = content.substring(0, insertPos) + 
        "\n    items.push({icon:'📚',label:'Edukasi & SOP',link:'edukasi.html'});" + 
        content.substring(insertPos);

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        console.log('Updated moreMenu in: ' + file);
    }
}

for (const file of files) {
    processFile(file);
}
console.log('More menu update complete.');
