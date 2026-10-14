const fs = require('fs');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// Search for 'visible' or 'visibility'
const regex = /visible|visibility/gi;
let m;
while ((m = regex.exec(js)) !== null) {
    console.log(js.slice(Math.max(0, m.index - 50), Math.min(js.length, m.index + 100)));
}
