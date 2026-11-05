(() => {
    'use strict';

    document.documentElement.classList.add('js-ready');
    window.lucide?.createIcons({ attrs: { 'stroke-width': 1.65, 'aria-hidden': 'true' } });

    const newsletterStatus = document.querySelector('[data-newsletter-status]');
    if (newsletterStatus && window.Swal) {
        window.Swal.fire({
            icon: newsletterStatus.dataset.type || 'success',
            title: newsletterStatus.dataset.title || 'Newsletter update',
            text: newsletterStatus.textContent.trim(),
            confirmButtonText: 'Got it',
            confirmButtonColor: '#176b53',
        });
        newsletterStatus.hidden = true;
    }

    const orderConfirmation = document.querySelector('[data-order-confirmation]');
    if (orderConfirmation && window.Swal) {
        window.Swal.fire({
            icon: 'success',
            title: 'Order received',
            text: `Order ${orderConfirmation.dataset.orderNumber} is awaiting payment. Delivery to ${orderConfirmation.dataset.deliveryCity} is scheduled for ${orderConfirmation.dataset.deliveryMonth} ${orderConfirmation.dataset.deliveryYear}.`,
            confirmButtonText: 'Got it',
            confirmButtonColor: '#176b53',
        });
    }

    const cart = document.querySelector('#cart-dialog');
    let cartOpener;

    document.querySelectorAll('[data-open-cart]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!cart || cart.open) return;
            cartOpener = button;
            cart.showModal();
            document.body.classList.add('dialog-open');
        });
    });

    document.querySelector('[data-close-cart]')?.addEventListener('click', () => cart?.close());
    cart?.addEventListener('close', () => {
        document.body.classList.remove('dialog-open');
        cartOpener?.focus();
    });
    cart?.addEventListener('click', (event) => {
        const bounds = cart.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) cart.close();
    });

    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileNav = document.querySelector('#mobile-nav');
    menuToggle?.addEventListener('click', () => {
        const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!expanded));
        mobileNav.hidden = expanded;
    });
    mobileNav?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileNav.hidden = true;
            menuToggle?.setAttribute('aria-expanded', 'false');
        });
    });

    const filterDetails = document.querySelector('[data-filter-details]');
    if (filterDetails) {
        const breakpoint = window.matchMedia('(min-width: 901px)');
        filterDetails.open = breakpoint.matches;
        breakpoint.addEventListener('change', (event) => { filterDetails.open = event.matches; });
        document.querySelector('#shop-filters')?.addEventListener('invalid', () => { filterDetails.open = true; }, true);
    }

    document.querySelectorAll('[data-auto-submit]').forEach((select) => {
        select.addEventListener('change', () => select.form?.requestSubmit());
    });

    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const image = gallery.querySelector('[data-main-photo]');
        const thumbnails = [...gallery.querySelectorAll('[data-photo-src]')];
        const photoCount = gallery.querySelector('[data-photo-count]');
        const mainPhoto = gallery.querySelector('.gallery-main');
        let photoIndex = Math.max(0, thumbnails.findIndex((thumbnail) => thumbnail.getAttribute('aria-pressed') === 'true'));

        const selectPhoto = (index) => {
            if (!image || thumbnails.length === 0) return;
            photoIndex = (index + thumbnails.length) % thumbnails.length;
            const thumbnail = thumbnails[photoIndex];
            image.hidden = false;
            const placeholder = gallery.querySelector('.photo-placeholder');
            if (placeholder) placeholder.hidden = true;
            image.src = thumbnail.dataset.photoSrc;
            image.alt = thumbnail.dataset.photoAlt;
            thumbnails.forEach((button, buttonIndex) => button.setAttribute('aria-pressed', String(buttonIndex === photoIndex)));
            if (photoCount) photoCount.textContent = `${photoIndex + 1} / ${thumbnails.length}`;
        };

        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', () => {
                selectPhoto(index);
            });
        });

        gallery.querySelector('[data-photo-previous]')?.addEventListener('click', () => selectPhoto(photoIndex - 1));
        gallery.querySelector('[data-photo-next]')?.addEventListener('click', () => selectPhoto(photoIndex + 1));

        let touchStart = null;
        mainPhoto?.addEventListener('touchstart', (event) => {
            if (event.changedTouches.length !== 1) return;
            touchStart = { x: event.changedTouches[0].clientX, y: event.changedTouches[0].clientY };
        }, { passive: true });
        mainPhoto?.addEventListener('touchend', (event) => {
            if (!touchStart || event.changedTouches.length !== 1) return;
            const deltaX = event.changedTouches[0].clientX - touchStart.x;
            const deltaY = event.changedTouches[0].clientY - touchStart.y;
            if (Math.abs(deltaX) > 45 && Math.abs(deltaX) > Math.abs(deltaY)) {
                selectPhoto(photoIndex + (deltaX < 0 ? 1 : -1));
            }
            touchStart = null;
        }, { passive: true });
        mainPhoto?.addEventListener('touchcancel', () => { touchStart = null; }, { passive: true });
    });

    document.querySelectorAll('[data-device-photo]').forEach((image) => {
        const showPlaceholder = () => {
            image.hidden = true;
            const placeholder = image.parentElement.querySelector('.photo-placeholder');
            if (placeholder) placeholder.hidden = false;
        };
        image.addEventListener('error', showPlaceholder);
        if (image.complete && !image.naturalWidth) showPlaceholder();
    });

    document.querySelectorAll('.add-to-cart-form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        });
    });
})();