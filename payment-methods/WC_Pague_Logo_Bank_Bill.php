<?php
/**
 * WC_Pague_Logo_Bank_Bill
 * 
 * Método de pagamento desenvolvido para a integração com boleto bancário do gateway de pagamento "Pague Logo".
 * 
 * Estes links podem lhe ajudar
 * @see https://rudrastyh.com/woocommerce/payment-gateway-plugin.html Artigo que ensina a criar gateways de pagamento no WooCommerce.
 * 
 * @author The Samurai Petrus (https://github.com/SamuraiPetrus)
 */
class WC_Pague_Logo_Bank_Bill extends \WC_Payment_Gateway
{
    /**
     * Define as propriedades do gateway.
     */
    public function __construct()
    {
        $this->plugin_dir_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_dir_url = plugin_dir_url(dirname(__FILE__));

        $admin_options = get_option('wc_pague_logo_settings');
        $this->usuario = $admin_options['wc_pague_logo_usuario'];
        $this->senha = $admin_options['wc_pague_logo_senha'];

        $this->id = 'wc-pague-logo-bank-bill';
        $this->icon = 'https://www.paguelogo.com.br/assets/img/logo.png';
        $this->has_fields = true;
        $this->method_title = 'Pague Logo - Boleto Bancário';
        $this->method_description = '';
        $this->supports = [
            'products'
        ];
        
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->desctiption = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->orientation = $this->get_option('orientation');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);
    }

    /**
     * Define as opções do painel de configuração do gateway.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Habilitar Boleto Bancário',
                'label'       => 'Ativar o método de pagamento de boleto bancário.',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Título',
                'type'        => 'text',
                'description' => 'Essa opção gerencia o texto que aparece no título do gateway no momento do checkout.',
                'default'     => 'Boleto Bancário',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Descrição',
                'type'        => 'textarea',
                'description' => 'Essa opção gerencia a descrição do gateway no momento do checkout.',
                'default'     => 'Realize o pagamento com boleto bancário através da Pague Logo.',
            ),
            'orientation' => array(
                'title'       => 'Orientação',
                'type'        => 'textarea',
                'description' => 'Essa opção gerencia a orientação feita ao usuário para o pagamento do seu boleto bancário.',
                'default'    => 'Você receberá um link para impressão do boleto após finalizar sua compra, caso queira pagar em outro momento, acesse "Minha Conta" no menu lateral, clique em "Meus Pedidos" e clique no link de impressão.',
            )
        );
    }

    /**
	 * Init settings for gateways.
	 */
	public function init_settings() {
		parent::init_settings();
		$this->enabled = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
	}

    /**
     * payment_scripts
     * 
     * Carrega os scripts e as folhas de estilo do gateway.
     * 
     * @see https://github.com/jessepollak/card Biblioteca Javascript que gera o cartão interativo.
     */
    public function payment_scripts()
    {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
        }

        if ('no' === $this->enabled) {
            return;
        }
        
        wp_enqueue_style('pague-logo-bank-bill-css', $this->plugin_dir_url . 'assets/css/bank-bill.css');
    }

    /**
     * payment_fields
     * 
     * Gera a interface front-end do gateway.
     */
    public function payment_fields()
    {
        $admin_options = [
            'orientation'
        ];

        foreach ($admin_options as $option) {
            $$option = $this->$option;
        }

        include $this->plugin_dir_path . 'views/bank-bill.php';
    }
}