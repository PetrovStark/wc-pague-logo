<?php

    use PagueLogo\Source\CardFieldsInfo;

    $CardFieldsInfo = new CardFieldsInfo();
    $fields = $CardFieldsInfo->getCamposDoCartao();

    $price = WC()->cart->cart_contents_total;
?>

<div id="pague-logo-screen">
    <div class='card-wrapper'></div>
    <fieldset id="payment-fields">
        <div id="pague_logo_installments" class="field">
            <span class="error-msg">Campo obrigatório</span>
            <select name="billing_installments">
                <option value="">Selecione o número de parcelas...</option>
                <?php for ($i = 1; $i <= $installments; $i++) : ?>
                    <option value="<?=$i?>"><?=$i?>x de R$ <?=number_format($price / $i, 2, ',', '.')?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <?php foreach ($fields as $field) : ?>
            <div id="pague_logo_<?=$field['slug']?>" class="field half">
                <span class="error-msg">Campo obrigatório</span>
                <input type="text" name="billing_<?=$field['slug']?>" placeholder="<?=$field['name']?>"/>
            </div>
        <?php endforeach; ?>
    </fieldset>
</div>

<script src="<?=plugin_dir_url(__FILE__) . '../assets/js/index.js'?>"></script>