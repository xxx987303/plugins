/**
 * JavaScript to handle modal behavior
 */

    // Get the modal
    var modal = document.getElementById('ybModal');

    // Get the link that opens the modal
    var link = document.getElementById('ybLink');

    // Get the image and insert it inside the modal
    var modalImg = document.getElementById("yb_img01");

    link.onclick = function(event) {
        event.preventDefault();
        modal.style.display = "block";
        modalImg.src = "/sh_rx/wp-content/uploads/2024/05/clock_str12.webp";
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() { 
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
