document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.main-nav a');
    const sections = [...document.querySelectorAll('main section[id]')];

    const updateActiveNav = () => {
        const y = window.scrollY + 160;
        let current = 'home';
        sections.forEach(section => {
            if (section.offsetTop <= y) current = section.id;
        });
        links.forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${current}`));
    };

    window.addEventListener('scroll', updateActiveNav, { passive: true });
    updateActiveNav();

    const gallery = document.querySelector('[data-gallery]');
    if (!gallery) return;

    const slides = [...gallery.querySelectorAll('.gallery-slide')];
    let centerIndex = 2;

    const roleClasses = ['far-left', 'left', 'center', 'right', 'far-right'];

    function renderGallery() {
        slides.forEach((slide, index) => {
            slide.classList.remove(...roleClasses);
            let offset = index - centerIndex;
            if (offset > 2) offset -= slides.length;
            if (offset < -2) offset += slides.length;
            const className = roleClasses[offset + 2];
            if (className) slide.classList.add(className);
        });
    }

    gallery.querySelector('.prev')?.addEventListener('click', () => {
        centerIndex = (centerIndex - 1 + slides.length) % slides.length;
        renderGallery();
    });

    gallery.querySelector('.next')?.addEventListener('click', () => {
        centerIndex = (centerIndex + 1) % slides.length;
        renderGallery();
    });

    renderGallery();
});
