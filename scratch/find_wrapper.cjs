const fs = require('fs');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// Find all indices of bKashFrameWrapper
let pos = 0;
while (true) {
    const idx = js.indexOf('bKashFrameWrapper', pos);
    if (idx === -1) break;
    console.log('Index:', idx);
    console.log(js.slice(Math.max(0, idx - 100), Math.min(js.length, idx + 200)));
    console.log('-------------------------');
    pos = idx + 1;
}
