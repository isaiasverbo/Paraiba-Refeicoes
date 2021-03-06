<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Marmitas extends CI_Controller {

    /**
     * @author Jobson Ribeiro
     */
    public function __construct() {
        parent::__construct();
        $this->form_validation->set_message('required', '%s não pode ser vazio');
        $this->form_validation->set_message('valid_email', '%s não possui um email válido');
        $this->log_in->is_logged(array('administrador', 'funcionario'));
        $this->load->model('gerenciador/marmita');
        $this->load->model('gerenciador/feijao');
        $this->load->model('gerenciador/arroz');
        $this->load->model('gerenciador/macarrao');
        $this->load->model('gerenciador/salada');
        $this->load->model('gerenciador/acompanhamento');
        $this->load->model('gerenciador/carne');
        $this->load->model('gerenciador/bebida');
    }

    public function index($pagina = false) {

        $dados = array();

        $paginacao = $pagina != false ? $pagina : 0;

        $config['base_url'] = base_url() . 'gerenciador/marmitas/';
        $config['uri_segment'] = 3;
        $config['total_rows'] = $this->marmita->total_marmita();
        $config['per_page'] = 25;
        $config['cur_tag_open'] = '<span class="selected">';
        $config['cur_tag_close'] = '</span>';

        $this->pagination->initialize($config);

        $dados['msg'] = $this->session->flashdata('cad_ok');
        $dados['erro'] = $this->session->flashdata('cad_erro');

        $dados['marmitas'] = $this->marmita->listar_marmita(array(), $paginacao, $config['per_page'], 'mar_data', 'DESC');
        $dados['titulo'] = $this->configs->get_titulo() . ' - Gerenciador';

        $this->render->view('gerenciador/marmitas/lista_marmita', $dados);
    }

    public function add_marmita() {

        $dados = array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $dados_bd = array('mar_dia_semana' => $this->input->post('mar_dia_semana'), 'mar_data' => $this->input->post('mar_data'));

            $dados_bd['mar_data'] = data_for_unix($dados_bd['mar_data']);

            $dadosItens = $this->input->post();

            if ($this->marmita->inserir($dados_bd)) {
                $id_marmita = $this->db->insert_id();

                foreach ($dadosItens['mtf_fei_id'] as $feijao) {
                    $this->feijao->inserir_feijao_marmita(array('mtf_fei_id' => $feijao, 'mtf_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['mta_arr_id'] as $arroz) {
                    $this->arroz->inserir_arroz_marmita(array('mta_arr_id' => $arroz, 'mta_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['mtm_mac_id'] as $macarrao) {
                    $this->macarrao->inserir_macarrao_marmita(array('mtm_mac_id' => $macarrao, 'mtm_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['mts_sal_id'] as $salada) {
                    $this->salada->inserir_salada_marmita(array('mts_sal_id' => $salada, 'mts_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['maa_aco_id'] as $acompanhamento) {
                    $this->acompanhamento->inserir_acompanhamento_marmita(array('maa_aco_id' => $acompanhamento, 'maa_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['mtc_car_id'] as $carne) {
                    $this->carne->inserir_carne_marmita(array('mtc_car_id' => $carne, 'mtc_mar_id' => $id_marmita));
                }
                foreach ($dadosItens['mtb_beb_id'] as $bebida) {
                    $this->bebida->inserir_bebida_marmita(array('mtb_beb_id' => $bebida, 'mtb_mar_id' => $id_marmita));
                }

                $this->session->flashdata('cad_ok', 'Marmita cadastrado com sucesso.');
                redirect('gerenciador/marmitas');
            } else {
                $dados['erros'] = 'Não foi possível cadastrar no banco de dados.';
            }
        }
        $dados['titulo'] = $this->configs->get_titulo() . ' - Gerenciador';

        $dados['feijoes'] = $this->feijao->listar_feijao(array(), 0, 99);

        $dados['arrozes'] = $this->arroz->listar_arroz(array(), 0, 99);

        $dados['macarroes'] = $this->macarrao->listar_macarrao(array(), 0, 99);

        $dados['saladas'] = $this->salada->listar_salada(array(), 0, 99);

        $dados['acompanhamentos'] = $this->acompanhamento->listar_acompanhamento(array(), 0, 99);

        $dados['carnes'] = $this->carne->listar_carne(array(), 0, 99);

        $dados['bebidas'] = $this->bebida->listar_bebida(array(), 0, 99);

        $this->render->view('gerenciador/marmitas/add_marmita', $dados);
    }

    public function editar_marmita($id_marmita) {

        $dados = array();
        $dados['marmita'] = $this->marmita->get_marmita(array('mar_id' => $id_marmita));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $dados_bd = array('mar_dia_semana' => $this->input->post('mar_dia_semana'), 'mar_data' => $this->input->post('mar_data'));

            $dados_bd['mar_data'] = data_for_unix($dados_bd['mar_data']);

            $dadosItens = $this->input->post();

            $this->marmita->atualizar(array('mar_id' => $id_marmita), $dados_bd);

            $id_marmita = $id_marmita;

            if (!empty($dadosItens['mtf_fei_id'])) {

                $this->feijao->delete_feijao_marmita(array('mtf_mar_id' => $id_marmita));

                foreach ($dadosItens['mtf_fei_id'] as $feijao) {
                    $this->feijao->inserir_feijao_marmita(array('mtf_fei_id' => $feijao, 'mtf_mar_id' => $id_marmita));
                }
            } else {
                $this->feijao->delete_feijao_marmita(array('mtf_mar_id' => $id_marmita));
            }

            if (!empty($dadosItens['mta_arr_id'])) {
                $this->arroz->delete_arroz_marmita(array('mta_mar_id' => $id_marmita));
                foreach ($dadosItens['mta_arr_id'] as $arroz) {                                        
                    
                    $this->arroz->inserir_arroz_marmita(array('mta_arr_id' => $arroz, 'mta_mar_id' => $id_marmita));
                }
            } else {
                $this->arroz->delete_arroz_marmita(array('mta_mar_id' => $id_marmita));
            }

            if (!empty($dadosItens['mtm_mac_id'])) {
                $this->macarrao->delete_macarrao_marmita(array('mtm_mar_id' => $id_marmita));
                foreach ($dadosItens['mtm_mac_id'] as $macarrao) {                                        
                    
                    $this->macarrao->inserir_macarrao_marmita(array('mtm_mac_id' => $macarrao, 'mtm_mar_id' => $id_marmita));
                }
            } else {
                $this->macarrao->delete_macarrao_marmita(array('mtm_mar_id' => $id_marmita));
            }

            if (!empty($dadosItens['mts_sal_id'])) {
                $this->salada->delete_salada_marmita(array('mts_mar_id' => $id_marmita));
                foreach ($dadosItens['mts_sal_id'] as $salada) {                                        
                    
                    $this->salada->inserir_salada_marmita(array('mts_sal_id' => $salada, 'mts_mar_id' => $id_marmita));
                }
            } else {
                $this->salada->delete_salada_marmita(array('mts_mar_id' => $id_marmita));
            }

            if (!empty($dadosItens['maa_aco_id'])) {
                $this->acompanhamento->delete_acompanhamento_marmita(array('maa_mar_id' => $id_marmita));
                
                foreach ($dadosItens['maa_aco_id'] as $acompanhamento) {                                        
                    
                    $this->acompanhamento->inserir_acompanhamento_marmita(array('maa_aco_id' => $acompanhamento, 'maa_mar_id' => $id_marmita));
                }
            } else {
                $this->acompanhamento->delete_acompanhamento_marmita(array('maa_mar_id' => $id_marmita));
            }

            if (!empty($dadosItens['mtc_car_id'])) {
                $this->carne->delete_carne_marmita(array('mtc_mar_id' => $id_marmita));
                foreach ($dadosItens['mtc_car_id'] as $carne) {                                    
                    
                    $this->carne->inserir_carne_marmita(array('mtc_car_id' => $carne, 'mtc_mar_id' => $id_marmita));
                }
            } else {
              $this->carne->delete_carne_marmita(array('mtc_mar_id' => $id_marmita));  
            }

            if (!empty($dadosItens['mtb_beb_id'])) {
                $this->bebida->delete_bebida_marmita(array('mtb_mar_id' => $id_marmita));
                
                foreach ($dadosItens['mtb_beb_id'] as $bebida) {                                        
                    
                    $this->bebida->inserir_bebida_marmita(array('mtb_beb_id' => $bebida, 'mtb_mar_id' => $id_marmita));
                }
            } else {
                $this->bebida->delete_bebida_marmita(array('mtb_mar_id' => $id_marmita));
            }

            $this->session->set_flashdata('cad_ok', 'Marmita atualizado com sucesso');
            redirect('gerenciador/marmitas');
        }

        $dados['feijoes'] = $this->feijao->listar_feijao(array(), 0, 99);

        $dados['arrozes'] = $this->arroz->listar_arroz(array(), 0, 99);

        $dados['macarroes'] = $this->macarrao->listar_macarrao(array(), 0, 99);

        $dados['saladas'] = $this->salada->listar_salada(array(), 0, 99);

        $dados['acompanhamentos'] = $this->acompanhamento->listar_acompanhamento(array(), 0, 99);

        $dados['carnes'] = $this->carne->listar_carne(array(), 0, 99);

        $dados['bebidas'] = $this->bebida->listar_bebida(array(), 0, 99);


        $dados['titulo'] = $this->configs->get_titulo() . ' - Gerenciador';

        $this->render->view('gerenciador/marmitas/edit_marmita', $dados);
    }

    public function excluir_marmita($id_marmita) {

        if ($this->marmita->delete(array('mar_id' => $id_marmita)) > 0) {

            $this->session->set_flashdata('cad_ok', 'Marmita excluido com sucesso');
        }

        redirect('gerenciador/marmitas');
    }

}