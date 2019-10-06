$(document).ready(function() {
    $("#stats-container").appendTo("#myspan");
    
    $("input[type='checkbox']").change(function() {
        if(!$(this).hasClass('home-btns')) 
            startAjaxReservation(this.id);
    });

    $("#refresh-btn").click(function() {
        window.location.reload(true); //with 'true', it reloads a fresh copy from the server. Leaving it out will serve the page from cache
    });
    
    $("#purchase-btn").click(function() { 
        var seats_chosen = $(".seat-booked-by-me").map(function() {
            return $(this).attr("for");
        }).get();
        if(seats_chosen.length > 0) {
            var jsonString = JSON.stringify(seats_chosen.join());
            startAjaxPurchase(jsonString);
        } else 
            alert('Per favore seleziona almeno un posto!');
    });
});

function checkCookiePresence() {
    if (!navigator.cookieEnabled) {
        document.write("E' necessario abilitare i cookie per continuare la navigazione.");
        $("#content-wrapper").hide();
    }
}

function validateForm() {
    var pass = document.forms["login-form"]["password"].value;
    var pass_conf = document.forms["login-form"]["confirm_password"].value;

    if (pass != pass_conf) {
        alert('Le password non corrispondono.');
        return false; 
    }
    return true; 
}

function removeSeatClasses(el) {
    const prefix = "seat-";
    var classes = el.className.split(" ").filter(function(c) {
        return c.lastIndexOf(prefix, 0) !== 0;
    });
    //const classes = el.className.split(" ").filter(c => !c.startsWith(prefix));
    el.className = classes.join(" ").trim();    
}

function startAjaxReservation(seatNumber) {
$.ajax({
    url: 'reservation.php',
    type: 'POST',
    data: jQuery.param({ seat_number: seatNumber }),
    dataType: 'text',
    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
    success: function (responseText) {
        if(responseText == "free" || responseText == "booked") {
            removeSeatClasses(document.getElementById(seatNumber).nextElementSibling);
            document.getElementById(seatNumber).nextElementSibling.classList.add("seat-booked-by-me");
            alert("Il posto " + seatNumber + " è stato prenotato correttamente!");
        }
        else if(responseText == "booked-by-me") {
            removeSeatClasses(document.getElementById(seatNumber).nextElementSibling);
            document.getElementById(seatNumber).nextElementSibling.classList.add("seat-free");
            alert("Il posto " + seatNumber + " è stato liberato!");
        }
        else if(responseText == "purchased") {
            removeSeatClasses(document.getElementById(seatNumber).nextElementSibling);
            document.getElementById(seatNumber).nextElementSibling.classList.add("seat-purchased");
            alert("Ci dispiace, il posto " + seatNumber + " è stato già acquistato. Riprovare");
        }
        else if(responseText == "session-expired") {
            alert("Sessione scaduta. Per favore effettuare nuovamente il login");
            window.location.replace('login.php');        
        }
        else //DB_ERROR, SEAT_NOT_VALID, RESERVATION_FAILED
            alert("Si è verificato un errore durante la fase di prenotazione. Riprovare");
    },
    error: function (responseText) {
        alert("Errore durante la fase di prenotazione. Riprovare");
    }
}); 
}

function startAjaxPurchase(seatsChosen) {
$.ajax({
    url: 'purchase.php',
    type: 'POST',   
    data: jQuery.param({ seats_chosen: seatsChosen }),
    dataType: "json",
    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
    success: function (responseText) {
        alert(responseText[0].msg);
        window.location.replace(responseText[0].url);        
    },
    error: function (responseText) {
        alert("Errore durante la fase di acquisto. Riprovare");
    }
}); 
}