export function T(key) {
    const i18n = (typeof window !== 'undefined') ? window.PP_I18N : null;
    if (i18n && i18n.strings && Object.prototype.hasOwnProperty.call(i18n.strings, key)) {
        return i18n.strings[key];
    }
    return key;
}
