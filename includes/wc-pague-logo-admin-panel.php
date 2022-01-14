<?php
/**
 * Painel administrativo do plugin.
 * 
 * Esses links podem lhe ajudar:
 * @see https://developer.wordpress.org/plugins/settings/settings-api/
 * @see https://code.tutsplus.com/tutorials/the-wordpress-settings-api-part-8-validation-sanitisation-and-input-i--wp-25361
 * @author The Samurai Petrus (https://github.com/SamuraiPetrus)
 */

/**
 * Inicializa o painel administrativo do plugin.
 */
function wc_pague_logo_settings_init()
{
    register_setting('wc_pague_logo', 'wc_pague_logo_settings');

    add_settings_section(
        'wc_pague_logo_authentication_section',
        __('Dados de autenticação', 'wc_pague_logo'),
        'wc_pague_logo_authentication_section_callback',
        'wc_pague_logo'
    );

    # Adicione novas opções de configuração neste array.
    $fields = [
        [
            'name' => 'wc_pague_logo_sandbox',
            'label' => __('Habilitar sandbox', 'wc_pague_logo'),
            'type' => 'checkbox',
            'callback' => 'wc_pague_logo_checkbox_fields_callback'
        ],
        [
            'name' => 'wc_pague_logo_usuario',
            'label' => __('Usuário', 'wc_pague_logo'),
            'type' => 'text',
        ],
        [
            'name' => 'wc_pague_logo_senha',
            'label' => __('Senha', 'wc_pague_logo'),
            'type' => 'password',
        ]
    ];

    foreach ($fields as $field) {
        add_settings_field(
            $field['name'],
            $field['label'],
            isset($field['callback']) ? $field['callback'] : 'wc_pague_logo_text_fields_callback',
            'wc_pague_logo',
            'wc_pague_logo_authentication_section',
            [
                'name' => $field['name'],
                'type' => $field['type'],
            ]
        );
    }
}
add_action( 'admin_init', 'wc_pague_logo_settings_init' );

/**
 * Callback da sessão de cadastro. Retornando vazio pois não quero adicionar nada nesta seção.
 */
function wc_pague_logo_authentication_section_callback()
{
    return;
}

/**
 * Retorna uma opção de texto no painel.
 * 
 * @param array $args Argumentos passados no sexto parâmetro da função add_settings_field().
 */
function wc_pague_logo_text_fields_callback($args) 
{
    $settings = get_option('wc_pague_logo_settings');
    $value = isset($settings[$args['name']]) ? $settings[$args['name']] : '';
    ?>
    <input
        id="<?=$args['name']?>" 
        type="<?=$args['type']?>" 
        name="wc_pague_logo_settings[<?=$args['name']?>]"
        value="<?=$value?>"
    >
    <?php
}

/**
 * Retorna uma opção de checkbox no painel.
 * 
 * @param array $args Argumentos passados no sexto parâmetro da função add_settings_field().
 */
function wc_pague_logo_checkbox_fields_callback($args) 
{
    $settings = get_option('wc_pague_logo_settings');
    $checked = isset($settings[$args['name']]) && $settings[$args['name']] === 'on' ? 'checked' : '';
    ?>
    <input
        id="<?=$args['name']?>" 
        type="checkbox" 
        name="wc_pague_logo_settings[<?=$args['name']?>]"
        <?= $checked ?>
    >
    <?php
}

/**
 * Adiciona o link do painel administrativo do plugin na barra de navegação.
 */
function wc_pague_logo_add_admin_menu()
{
    add_menu_page(
        'Pague Logo',
        'Pague Logo',
        'manage_options',
        'wc_pague_logo',
        'wc_pague_logo_menu_page_html',
    );
}
add_action( 'admin_menu', 'wc_pague_logo_add_admin_menu' );

/**
 * Retorna o HTML da página do painel administrativo do plugin.
 */
function wc_pague_logo_menu_page_html()
{
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    # Feedback de erros para o administrador.
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'wc_pague_logo_messages', 'wc_pague_logo_message', __( 'Settings Saved', 'wporg' ), 'updated' );
    }
    settings_errors( 'wc_pague_logo_messages' );

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p>Página de configuração do plugin Pague Logo.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'wc_pague_logo' );
            do_settings_sections( 'wc_pague_logo' );
            submit_button( 'Salvar' );
            ?>
        </form>
    </div>
    <?php
}