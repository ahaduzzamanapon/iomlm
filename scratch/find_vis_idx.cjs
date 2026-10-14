const fs = require('fs');
const vm = require('vm');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

const ctx = {
    console,
    navigator: { userAgent: 'Mozilla/5.0' },
    document: {
        createElement: () => ({ style: {}, setAttribute: () => {}, addEventListener: () => {} }),
        getElementById: () => null
    }
};
ctx.window = ctx;
vm.createContext(ctx);
vm.runInContext(js, ctx);

// In the closure, let's find what _0x48ae returns for all indices:
// We can extract _0x48ae by evaluating the preamble
const startArr = js.indexOf("['z2jXDg8'");
const endArr = js.indexOf("];_0x4d36=");
const arr = eval(js.slice(startArr, endArr + 1));
const rotCode = js.slice(js.indexOf("(function(_0x"), js.indexOf(";function _0x48ae"));
function _0x4d36() { return arr; }
(new Function('_0x4d36', rotCode))(_0x4d36);
const func48ae = new Function('_0x4d36', js.slice(js.indexOf("function _0x48ae"), js.indexOf("var _0x57e80a=_0x48ae;")) + "\n return _0x48ae;");
const _0x48ae = func48ae(_0x4d36);

for (let i = 0x8e; i < 0x8e + 450; i++) {
    try {
        const val = _0x48ae(i);
        if (typeof val === 'string' && (val.includes('visible') || val.includes('visibility') || val.includes('display') || val.includes('none') || val.includes('hidden'))) {
            console.log(i.toString(16), '=>', val);
        }
    } catch(e) {}
}
