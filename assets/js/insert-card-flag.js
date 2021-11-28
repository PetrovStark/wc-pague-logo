/**
 * Insere a bandeira do cartão como um parâmetro do formulário.
 */
 function insert_card_flag()
 {
    const checkout_form = document.getElementsByName('checkout').item(0);
    const ignore_classes = [
        'jp-card-invalid',
        'identified'
    ];

    checkout_form.onsubmit = function () {
        const card_number_field = document.querySelector('#pague_logo_card_number input[name=billing_card_number]');
        const form = document.querySelector('#pague-logo-screen');

        card_number_field.classList.forEach(function(className){
            if (!inArray(className, ignore_classes)) {
                var previous_card_flag = document.getElementById('pague_logo_card_flag');

                if (previous_card_flag !== null) {
                    previous_card_flag.remove();
                }

                form.insertAdjacentHTML('beforeend', '<input type="hidden" id="pague_logo_card_flag" name="billing_card_flag" value="'+ className +'">');
            }
        });
    }
 }
 
 function inArray(needle, haystack) {
     var length = haystack.length;
     for(var i = 0; i < length; i++) {
         if(haystack[i] == needle) return true;
     }
     return false;
 }
 
 insert_card_flag();