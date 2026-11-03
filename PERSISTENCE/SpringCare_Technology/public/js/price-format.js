(() => {
    'use strict';

    document.querySelectorAll('[data-price-format]').forEach((input) => {
        const format = (trimZeroDecimals = false) => {
            const rawValue = input.value;
            const cursor = input.selectionStart ?? rawValue.length;
            const digitsBeforeCursor = (rawValue.slice(0, cursor).match(/\d/g) || []).length;
            const decimalBeforeCursor = rawValue.slice(0, cursor).includes('.');
            const cleaned = rawValue.replace(/,/g, '').replace(/[^\d.]/g, '');
            const decimalIndex = cleaned.indexOf('.');
            const integer = (decimalIndex < 0 ? cleaned : cleaned.slice(0, decimalIndex)).replace(/^0+(?=\d)/, '');
            let fraction = decimalIndex < 0 ? '' : cleaned.slice(decimalIndex + 1).replace(/\./g, '').slice(0, 2);
            let hasDecimal = decimalIndex >= 0;

            if (trimZeroDecimals) {
                fraction = fraction.replace(/0+$/, '');
                hasDecimal = hasDecimal && fraction.length > 0;
            }

            input.value = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (hasDecimal ? `.${fraction}` : '');

            if (document.activeElement === input && typeof input.setSelectionRange === 'function') {
                let digitsSeen = 0;
                let newCursor = 0;

                if (digitsBeforeCursor > 0) {
                    for (let index = 0; index < input.value.length; index += 1) {
                        if (/\d/.test(input.value[index])) digitsSeen += 1;
                        if (digitsSeen === digitsBeforeCursor) {
                            newCursor = index + 1;
                            break;
                        }
                    }
                }

                if (decimalBeforeCursor && input.value.includes('.')) {
                    newCursor = Math.max(newCursor, input.value.indexOf('.') + 1);
                }

                input.setSelectionRange(newCursor, newCursor);
            }
        };

        input.addEventListener('input', () => format());
        input.addEventListener('blur', () => format(true));
        format(true);
    });
})();
