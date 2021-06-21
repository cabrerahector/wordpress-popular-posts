export function escape_html(value) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
        "/": '&#x2F;',
        '`': '&grave;',
    };
    const reg = /[&<>"'/]/ig;
    return value.replace(reg, (match)=>(map[match]));
}

export function unescape_html(value) {
    var div = document.createElement('div');
    div.innerHTML = value;
    var child = div.childNodes[0];
    return child ? child.nodeValue : '';
}
