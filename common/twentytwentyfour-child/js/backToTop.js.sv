document.addEventListener('DOMContentLoaded', function () {
    
    const button = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
            button.classList.add('visible');
        } else {
            button.classList.remove('visible');
        }
    });
    
    button.addEventListener('click', function (e) {
        e.preventDefault();
	
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
});
