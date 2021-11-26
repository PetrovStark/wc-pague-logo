/**
 * Valida os campos do gateway de pagamento a nível de front-end.
 */
function validate_payment_fields() {
    const checkout_form = document.getElementsByName('checkout').item(0),
    payment_fields = [
        'pague_logo_card_name',
        'pague_logo_card_number',
        'pague_logo_card_expiry',
        'pague_logo_card_cvv'
    ];

    payment_fields.forEach(function(field_id){
        var field = document.getElementById(field_id);

        console.log(field.children[1]);
    });

    checkout_form.onsubmit = function () {
        payment_fields.forEach(function(field_id){
            var field = document.getElementById(field_id);

            field.classList.remove('errored');

            if (field.children[1].value == '') {
                field.classList.add('errored');
            }
        });
    }
}

validate_payment_fields();