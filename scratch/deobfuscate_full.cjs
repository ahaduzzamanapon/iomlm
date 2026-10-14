const fs = require('fs');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

// Find the string array
const startArr = js.indexOf("['z2jXDg8'");
const endArr = js.indexOf("];_0x4d36=");
const arrCode = js.slice(startArr, endArr + 1);
const arr = eval(arrCode);

// The rotation function:
// (function(_0x5c7f8a,_0x2f9cb7){...}(_0x4d36,0x10b));
const startRot = js.indexOf("(function(_0x");
const endRot = js.indexOf(";function _0x48ae");
const rotCode = js.slice(startRot, endRot);

function _0x4d36() { return arr; }
// Run rotation:
const rotFunc = new Function('_0x4d36', rotCode);
rotFunc(_0x4d36);

// Now define _0x48ae:
const start48ae = js.indexOf("function _0x48ae");
const end48ae = js.indexOf("var _0x57e80a=_0x48ae;");
const func48ae = new Function('_0x4d36', js.slice(start48ae, end48ae) + "\n return _0x48ae;");
const _0x48ae = func48ae(_0x4d36);

console.log('0x133 =>', _0x48ae(0x133));

// Now let's decode ALL calls to _0x57e80a or _0x48ae in the entire script!
let decodedJs = js.replace(/(?:_0x57e80a|_0x48ae|_0xedf847|_0xaeef8a|_0x48b60d|_0x52d267|_0x27174b|_0x1ee042|_0x3144de|_0x221161|_0x40eee6|_0x558859|_0x2c194f|_0xc1816c|_0x153f16|_0x1ca7f4|_0x1007e0|_0x159849|_0x19d1d1|_0x3636be|_0x29ee34|_0x2624c9|_0x570b66|_0x195e45|_0x1aeb2b|_0x5abefc|_0x48a5e1|_0x197822)\((0x[0-9a-f]+)\)/g, (m, hex) => {
    try {
        const val = _0x48ae(parseInt(hex, 16));
        return JSON.stringify(val);
    } catch(e) {
        return m;
    }
});

fs.writeFileSync('scratch/bkash_decoded.js', decodedJs, 'utf8');
console.log('Saved scratch/bkash_decoded.js! Size:', decodedJs.length);
