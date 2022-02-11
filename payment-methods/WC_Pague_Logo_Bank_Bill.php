<?php
use PagueLogo\Source\PagueLogoAuthentication;
use PagueLogo\Source\PagueLogoPaymentGateway;
use PagueLogo\Source\PagueLogoBankBill;
use PagueLogo\Source\PagueLogoPayer;

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
        $this->general_instructions = $this->get_option('general_instructions');
        $this->due_date = $this->get_option('due_date');

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
                'title'       => 'Orientação no checkout',
                'type'        => 'textarea',
                'description' => 'Essa opção gerencia a orientação feita ao usuário na etapa de checkout do pedido.',
                'default'    => 'Você receberá um link para impressão do boleto após finalizar sua compra, caso queira pagar em outro momento, acesse "Minha Conta" no menu lateral, clique em "Meus Pedidos" e clique no link de impressão.',
            ),
            'general_instructions' => array(
                'title'       => 'Instruções gerais',
                'type'        => 'textarea',
                'description' => 'Essa opção gerencia a mensagem impressa no boleto bancário, contendo as instruções gerais de pagamento.',
                'default'    => 'Pague este boleto online ou em uma casa lotérica.',
            ),
            'due_date' => array(
                'title'       => 'Data de Vencimento (em dias)',
                'type'        => 'number',
                'description' => 'Essa opção gerencia a data de vencimento do boleto bancário. (Não aceita números negativos, ou decimais)',
                'default'     => '5',
            ),
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
     * 
     * @return void
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

    /**
     * process_payment
     * 
     * Realiza a integração com a API de pagamento na opção "Boleto Bancário".
     * 
     * @param int $order_id ID do pedido.
     * 
     * @return array | void
     */
    public function process_payment($order_id)
    {
        try {
            $Order = wc_get_order($order_id);
            $Authentication = new PagueLogoAuthentication($this->usuario, $this->senha);
            $Payer = new PagueLogoPayer($_POST);
            $admin_options = [
                'due_date' => $this->due_date,
                'general_instructions' => $this->general_instructions,
            ];

            $BankBill = new PagueLogoBankBill($Order, $Payer, $Authentication, $admin_options);
            $PagueLogoPaymentGateway = new PagueLogoPaymentGateway($BankBill);
            
            $response = $PagueLogoPaymentGateway->processPayment();
            $PagueLogoPaymentGateway->insertPaymentMetaData($response);
            
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');

            update_post_meta($Order->get_id(), 'pague_logo_error_log', $e->getMessage());
            $Order->update_status('failed', $e->getMessage());

            return;
        }

          $Order->payment_complete();
          $Order->add_order_note( 'Recebemos um pagamento!', true );
  
          return array(
              'result' => 'success',
              'redirect' => $this->get_return_url( $Order )
          );
    }
}