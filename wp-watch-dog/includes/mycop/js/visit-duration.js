document.addEventListener("DOMContentLoaded", function() {
    var startTime = Date.now();
    document.cookie = "start_time=" + startTime + "; path=/";

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    }

    function sendDuration() {
        var endTime = Date.now();
        var startTime = getCookie('start_time');
	alert(startTime);
        if (startTime) {
            var duration = Math.round((endTime - startTime) / 1000); // Duration in seconds

            var xhr = new XMLHttpRequest();
            xhr.open("POST", ajaxurl, true);
/*          xhr.open("POST", "/restor/wp-admin/admin-ajax.php?action=track_visit_duration", true); */
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        /*  xhr.send("duration=" + duration); */
            xhr.send("action=track_visit_duration&duration=" + duration);
	    
            // Delete the cookie
            document.cookie = "start_time=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    }

    window.addEventListener("beforeunload", sendDuration);
});
