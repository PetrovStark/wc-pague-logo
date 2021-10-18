<?php
    use PagueLogo\Source\Card;

    $Card = new Card();

    $fields = $Card->getCamposDoCartao();
?>

<div id="pague-logo-screen">
    <div class='card-wrapper'></div>
    <fieldset id="payment-fields">
        <?php foreach ($fields as $field) : ?>
            <div id="pague_logo_<?=$field['slug']?>" class="field">
                <span class="error-msg">Campo obrigatório</span>
                <input type="text" name="billing_<?=$field['slug']?>" placeholder="<?=$field['name']?>"/>
            </div>
        <?php endforeach; ?>
    </fieldset>
</div>

<script src="<?=plugin_dir_url(__FILE__) . '../assets/js/index.js'?>"></script>