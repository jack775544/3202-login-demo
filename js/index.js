$("#login-form").submit(function(e){
    // Stop the form from submitting so we can do it via AJAX
    e.preventDefault();

    $.post('backend/login.php', $('#login-form').serialize(), function (r) {
        if (r.auth === true) {
            alert("You have been logged in successfully");
            window.location = "hello.html";
        } else {
            alert("You aren't authorised to log in");
        }
    })
});