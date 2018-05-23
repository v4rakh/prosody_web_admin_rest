$(".alert-danger").delay(30000).fadeOut(300);
$(".alert-success").delay(10000).fadeOut(300);
$(".alert-info").delay(10000).fadeOut(300);

window.addEventListener("load", function () {
    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "#252e39"
            },
            "button": {
                "background": "#14a7d0"
            }
        },
        "content": {
            "message": "{{ 'cookiemodal.message'|trans }}",
            "dismiss": "{{ 'cookiemodal.dismiss'|trans }}",
            "href": "/signup"
        }
    })
});