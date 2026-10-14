const fs = require('fs');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// Search for appendChild, parentNode, insertBefore, getElementById, querySelector
const matches = [];
const regex = /(?:appendChild|parentNode|parentElement|insertBefore|getElementById|querySelector|append)[a-zA-Z0-9_\(\)\."']{0,60}/g;
let m;
while ((m = regex.exec(js)) !== null) {
    matches.push(m[0]);
}
console.log('DOM matches:', matches);
