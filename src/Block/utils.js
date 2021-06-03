export function sanitize_text_field(value) {
    const decoder = document.createElement('div');
    decoder.innerHTML = value;
    const sanitized = decoder.textContent;

    return sanitized;
}
