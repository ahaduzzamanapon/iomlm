const fs = require('fs');
const js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// Let's search for html markup or iframe in the js
const matches = js.match(/'[^']*(?:iframe|modal|checkout|payment|style)[^']*'/gi);
console.log('Matches:', matches ? matches.slice(0, 50) : 'none');
