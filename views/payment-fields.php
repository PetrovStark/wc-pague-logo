<div id="pague-logo-screen">
    <div class='card-wrapper'></div>
    <fieldset id="payment-fields">
        <div id="pague_logo_card_name" class="field">
            <span class="error-msg">Campo obrigatório</span>
            <input type="text" name="billing_card_name" placeholder="Nome completo"/>
        </div>
        <div id="pague_logo_card_number" class="field">
            <span class="error-msg">Campo obrigatório</span>
            <input type="text" name="billing_card_number" placeholder="•••• •••• •••• ••••">
        </div>
        <div id="pague_logo_card_expiry" class="field">
            <span class="error-msg">Campo obrigatório</span>
            <input type="text" name="billing_card_expiry" placeholder="••/••"/>
        </div>
        <div id="pague_logo_card_cvc" class="field">
            <span class="error-msg">Campo obrigatório</span>
            <input type="text" name="billing_card_cvc" placeholder="CVV"/>
        </div>
    </fieldset>
</div>

<script src="<?=plugin_dir_url(__FILE__) . '../assets/js/index.js'?>"></script>