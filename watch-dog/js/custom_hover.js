document.addEventListener('DOMContentLoaded', function() {
    const navItem = document.querySelector('.wp-block-navigation-item__content[href="/restor/clock1896/"]');
    const clockImage = document.querySelector('img.clock_btn');
/*    alarm(navItem); */
    if (navItem && clockImage) {
        navItem.addEventListener('mouseover', function() {
            clockImage.style.opacity = '1';
        });

        navItem.addEventListener('mouseout', function() {
            clockImage.style.opacity = '0.3';
        });
    }
});
