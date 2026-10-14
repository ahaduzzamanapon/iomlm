const fs = require('fs');
const vm = require('vm');
let js = fs.readFileSync('scratch/bkash_script_utf8.js', 'utf8');

const operations = [];

function createElement(tag) {
    const el = {
        tagName: tag.toUpperCase(),
        style: {},
        attributes: {},
        children: [],
        appendChild(child) {
            this.children.push(child);
            child.parentNode = this;
            return child;
        },
        insertBefore(newNode, refNode) {
            this.children.push(newNode);
            newNode.parentNode = this;
            return newNode;
        },
        setAttribute(k, v) { this.attributes[k] = v; this[k] = v; },
        getAttribute(k) { return this.attributes[k]; },
        addEventListener(e, fn) {
            if (e === 'click') this._click = fn;
            if (e === 'load') this._load = fn;
        }
    };
    return el;
}

const body = createElement('body');
const btn = createElement('button');
btn.id = 'bKash_button';
body.appendChild(btn);

const elementsById = { bKash_button: btn };

const doc = {
    createElement: (tag) => {
        const el = createElement(tag);
        return new Proxy(el, {
            set(t, p, v) {
                if (p === 'id') elementsById[v] = el;
                return Reflect.set(t, p, v);
            }
        });
    },
    getElementById: (id) => elementsById[id] || null,
    body: body,
    head: createElement('head')
};

const ctx = {
    console,
    navigator: { userAgent: 'Mozilla/5.0' },
    document: doc,
    setInterval: () => {},
    setTimeout: (fn) => fn(),
    addEventListener: () => {}
};
ctx.window = ctx;

vm.createContext(ctx);
vm.runInContext(js, ctx);

ctx.bKash.init({
    paymentMode: 'checkout',
    paymentRequest: { amount: '100', intent: 'sale' },
    createRequest: () => {},
    executeRequestOnAuthorization: () => {}
});

btn._click();

// Find iframe
const wrapper = elementsById['bKashFrameWrapper'];
console.log('Wrapper found?', !!wrapper);
if (wrapper) {
    const iframe = wrapper.children.find(c => c.tagName === 'IFRAME');
    console.log('Iframe found?', !!iframe);
    if (iframe && iframe._load) {
        console.log('Triggering iframe load...');
        iframe._load();
        console.log('Iframe style after load:', iframe.style);
    }
}
