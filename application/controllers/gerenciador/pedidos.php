<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pedidos extends CI_Controller {

    /**
     * @author Isaias Filho
     */
    public function __construct() {
        parent::__construct();
        $this->form_validation->set_message('required', '%s não pode ser vazio');
        $this->form_validation->set_message('valid_email', '%s não possui um email válido');
        $this->log_in->is_logged(array('administrador', 'funcionario'));
        $this->load->model('gerenciador/pedido');
//        $this->load->model('gerenciador/endereco');        
//        $this->load->model('gerenciador/cliente');
        $this->load->model('gerenciador/produto');
    }

    public function index($pagina = false) {

        $dados = array();

        $paginacao = $pagina != false ? $pagina : 0;

        $config['base_url'] = base_url() . 'gerenciador/pedidos/';
        $config['uri_segment'] = 3;
        $config['total_rows'] = $this->pedido->total_pedido();
        $config['per_page'] = 25;
        $config['cur_tag_open'] = '<span class="selected">';
        $config['cur_tag_close'] = '</span>';

        $this->pagination->initialize($config);

        $dados['msg'] = $this->session->flashdata('cad_ok');
        $dados['erro'] = $this->session->flashdata('cad_erro');

        $dados['pedidos'] = $this->pedido->listar_pedido(array(), $paginacao, $config['per_page']);

        $dados['titulo'] = $this->configs->get_titulo() . ' - Gerenciador';

        $this->render->view('gerenciador/pedidos/lista_pedido', $dados);
    }

    public function visualizar($id_pedido) {

        $dados = array();
        $dados['oksend'] = $this->session->flashdata('oksend');
        
        $dados['pedido'] = $this->pedido->get_pedido(array('pedido.ped_id' => $id_pedido));
        $dados['itens'] = $this->pedido->listar_itempedido(array('pedido_item.pei_ped_id' => $id_pedido), 0, 9999);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($this->pedido->atualizar(array('ped_id' => $id_pedido), $this->input->post()) > 0) {

                if ($this->input->post('ped_status') == 'encaminhado') {
                    
                    $email_admin = $this->template_emails->email_status_pedido($id_pedido);                    
                    $this->sender->enviar_email(
                            $this->configs->get_smtpuser(), $this->configs->get_titulo(), $dados['pedido']->cli_email, 'Atualização de status do pedido - Paraíba Refeições.', $email_admin
                    );
                }
                $dados['sucesso'] = 'Status atualizado com sucesso';
            }
        }
        
        $dados['titulo'] = $this->configs->get_titulo() . ' - Pedido nº ' . $id_pedido;

        $this->render->view('gerenciador/pedidos/view_pedido', $dados);
    }

    public function enviar($id_pedido) {
        $dados = array();

//        $dados['itens'] = $this->pedido->listar_itempedido(array('pedido_item.pei_ped_id' => $id_pedido), 0, 9999);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {



//        print_r($this->input->post('email'));
//        exit;
            $mensagemEmail = $this->template_emails->email_resposta_cotacao($this->input->post('texto'));
//            print_r($mensagemEmail);exit;
            $this->sender->enviar_email(
                    $this->configs->get_smtpuser(), $this->configs->get_titulo(), $this->input->post('email'), 'Resposta da cotação', $mensagemEmail
            );

//                echo "<script>alert('Mensagem Enviada com sucesso!!'); $('#formEnvio').unblock();</script>";
            $this->session->set_flashdata('oksend', 'Email enviado com sucesso.');
            redirect('gerenciador/pedidos/visualizar/' . $id_pedido);
        }

        $dados['pedido'] = $this->pedido->get_pedido(array('pedido.ped_id' => $id_pedido));
        $dados['itens'] = $this->pedido->listar_itempedido(array('pedido_item.pei_ped_id' => $id_pedido), 0, 9999);

        $dados['titulo'] = $this->configs->get_titulo() . ' - Enviar Cotação - ' . $id_pedido;
        $this->render->view('gerenciador/pedidos/envia_pedido', $dados);
    }

}
