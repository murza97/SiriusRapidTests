(function changeInputAmount() {
    $('.minus').click(function() {
        var $input = $(this).parent().find('.paypal-form__input');
        var count = parseInt($input.val()) - 1;
        count = count < 1 ? 1 : count;
        $input.val(count);
        $input.change();
    });
    $('.plus').click(function() {
        var $input = $(this).parent().find('.paypal-form__input');
        $input.val(parseInt($input.val()) + 1);
        $input.change();
    });
}());

