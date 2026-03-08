const fs = require('fs');
const path = require('path');

const files = [
    'resources/views/backend/product/products/create.blade.php',
    'resources/views/backend/product/products/edit.blade.php',
    'resources/views/seller/product/products/create.blade.php',
    'resources/views/seller/product/products/edit.blade.php',
];

files.forEach(file => {
    if (!fs.existsSync(file)) {
        console.log(`File not found: ${file}`);
        return;
    }

    let content = fs.readFileSync(file, 'utf8');

    // Clean up duplicated ID and attributes in selects
    // Specifically target the mess created by previous script
    content = content.replace(/id="hsn_code_select" data-live-search="true" data-size="5" id="hsn_code_select"/g, 'id="hsn_code_select" data-live-search="true" data-size="5"');
    content = content.replace(/data-live-search="true" data-size="5" data-live-search="true" data-size="5"/gs, 'data-live-search="true" data-size="5"');

    // Also fix potential double-nested placeholders in selects
    content = content.replace(/<option value="">{{ translate\('Search TN VED by code or product name\.\.\.'\) }}<\/option>\s*<option value="">{{ translate\('Search TN VED by code or product name\.\.\.'\) }}<\/option>/gs, '<option value="">{{ translate(\'Search TN VED by code or product name...\') }}</option>');

    fs.writeFileSync(file, content);
    console.log(`Cleaned: ${file}`);
});
