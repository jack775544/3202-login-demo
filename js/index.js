$("#login-form").submit(function(e){
    // Stop the form from submitting so we can do it via AJAX
    e.preventDefault();

    $.post('backend/login.php', $('#login-form').serialize(), function (r) {
        if (r.auth === true) {
            var store = window.localStorage;
            store.setItem("token", r.token);
            store.setItem("username", r.username);
            window.location = "hello.html";
        } else {
            alert("You aren't authorised to log in");
        }
    })
});