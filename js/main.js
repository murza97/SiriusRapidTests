$(document).ready(function() {
    window.onload = function() { setTimeout(function() { document.getElementById("preloader").style.display = "none" }, 400) };

    function e(e) { e.addEventListener("click", function() { o.classList.toggle("mobile-menu__open") }) }
    var o = document.querySelector(".mobile-menu"),
        n = document.querySelector(".tap-menu"),
        r = document.querySelector(".mobile-menu__close"),
        t = Array.from(document.querySelectorAll(".mobile-menu__nav-link"));
    e(n), e(t[0]), e(t[1]), e(t[2]), e(r)
});
var btnOrder = Array.from(document.querySelectorAll(".btn-order")),
    formSupplier = document.querySelector(".form-supplier"),
    btnCloseInForm = document.querySelector(".paypal-form__close"),
    openPayPalForm = function(e) { e.addEventListener("click", function() { formSupplier.classList.toggle("form-supplier--open") }) };
openPayPalForm(btnOrder[0]), openPayPalForm(btnOrder[1]), openPayPalForm(btnOrder[2]), openPayPalForm(btnOrder[3]), openPayPalForm(btnOrder[4]), openPayPalForm(btnCloseInForm), $("body").on("click", '[href*="#"]', function(e) { $("html,body").stop().animate({ scrollTop: $(this.hash).offset().top - 100 }, 1e3), e.preventDefault() });

$(function() {
    $("#phone").mask("+9 (999) 999-9999");
});