const fs = require('fs');
const js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// The bKash script is an IIFE or similar with string deobfuscation.
// Let's run the deobfuscator function or search for strings in it.
// Let's find the array and function in the beginning:
const firstPart = js.slice(0, 5000);
console.log(firstPart.slice(0, 1500));
