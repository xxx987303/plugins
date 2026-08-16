//
// init.umd.js PhotoSwipe
//

// Remove multiple captions, if any
var e = document.getElementById("memoir-caption");
if (e) e.remove();
const caption_div = document.createElement('div');
caption_div.id = 'memoir-caption';

function updateCaption(pswp,id) {
    var slide = pswp.currSlide;
    caption_div.innerHTML = slide.data.element.dataset.caption;
    console.log('updateCaption'+id+': '+caption_div.innerHTML);
}

document.addEventListener('DOMContentLoaded', function () {

    //
    // Sanity check
    //
    if (typeof PhotoSwipe === 'undefined') {
        console.error('PhotoSwipe is not loaded.');
        return;
    }

    if (typeof PhotoSwipeLightbox === 'undefined') {
        console.error('PhotoSwipeLightbox is not loaded.');
        return;
    }

    //
    // Locate all Gutenberg images
    //
    const figures = document.querySelectorAll('.entry-content figure.wp-block-image');
    if (figures.length === 0) {
	console.log(document.title + ' - no images');
	return;
    }

    //
    // Ensure every image is wrapped in a link
    //
    figures.forEach(function (figure) {

        const link = figure.querySelector('a');
        const img  = figure.querySelector('img');
        if (!link || !img) return;

        //
        // PhotoSwipe likes to know image dimensions.
        // Use the values already known by the browser.
        //
	if (img.complete && img.naturalWidth > 0) {
	    link.dataset.pswpWidth  = img.naturalWidth;
	    link.dataset.pswpHeight = img.naturalHeight;
	} else {
	    img.addEventListener('load', function () {
		link.dataset.pswpWidth  = img.naturalWidth;
		link.dataset.pswpHeight = img.naturalHeight;
	    });
	}
        if (link.dataset.pswpWidth < 1)  console.error('Cant get width  '+ img.naturalWidth + ' ' + link); 

	// hook caption to the image
        const cap  = figure.querySelector('figcaption');
	link.dataset.caption =  cap ? cap.innerHTML : '';
    });

    //
    // Create lightbox
    //
    const lightbox = new PhotoSwipeLightbox({
        gallery: '.entry-content',
        children: 'figure.wp-block-image > a',
        pswpModule:PhotoSwipe
    });

    lightbox.init();
    //console.log('lightbox1='+lightbox);

  //document.addEventListener('click', function () {
    window.addEventListener('click', function () {
	//console.log('lightbox2='+lightbox);
	setTimeout(function () {
            if (!lightbox.pswp) { console.error("No pswp");
				  return;
				}
	    updateCaption(lightbox.pswp,1);
	    lightbox.pswp.element.appendChild(caption_div);
	
	    lightbox.pswp.on('openingAnimationEnd', updateCaption(lightbox.pswp,3));
            //console.log("Attaching directly to pswp");
            lightbox.pswp.on('change', function () {
		const slide = lightbox.pswp.currSlide;
		//console.log(slide);
		if (!slide || !slide.data || !slide.data.element) {
		    console.log("No element");
		    return;
		}

		updateCaption(lightbox.pswp,2);
		//lightbox.pswp.on('change',              updateCaption(lightbox.pswp,4));
	    });	   
        }, 500);
    }, { once: true });
    console.log('PhotoSwipe initialized with ' + figures.length + ' images');
});
