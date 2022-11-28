<?php 
	namespace febraban;

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);


	include("vendor/autoload.php");
	include('connection.php');

    // Recebendo por parâmetro de url o cod do convenio e data de vencimento
    $convenio   = $_GET['convenio'];


    $vencimento = date('Y-m-d',strtotime($_GET['data']));


    if(isset($_GET['convenio']))
    {
        // Busca os dados do convenio
		$sql = "SELECT * , bancos.nome_banco, bancos.codigo_febraban, convenios_debito_em_conta.banco_id
                FROM convenios_debito_em_conta 
                INNER JOIN bancos
                ON bancos.id = convenios_debito_em_conta.banco_id	
                WHERE `cod_convenio` = ".$convenio;
        $res       = $connection->query($sql);
        
        $row       = $res->fetch_object();
        $cod_banco = $row->codigo_febraban;
        $convenio  = $row->cod_convenio;	
        $numero_sequencial_arquivo = $row->numero_sequencial_arquivo  + 1;
    

        switch($cod_banco)
        {
            case 237:
                // Adiciona 1 ao numero sequencial do arquivo e guarda no convenio
                $sql = "UPDATE `convenios_debito_em_conta` SET `numero_sequencial_arquivo` = $numero_sequencial_arquivo  WHERE `cod_convenio` = ".$convenio;
				$res = $connection->query($sql);


                // REGISTRO 0
                $Registro0 = array();
                $Registro0["cod_registro0"]                                 = 0;
                $Registro0["cod_remessa"]                                   = 1;
                $Registro0["literal_remessa"]                               = "REMESSA";
                $Registro0["cod_servico"]                                   = 1;
                $Registro0["literal_servico"]                               = "COBRANCA";
                $Registro0["cod_empresa"]                                   = "99999999999999999999";
                $Registro0["nome_empresa"]                                  = $row->nome_empresa;
                $Registro0["num_bradesco_camara_compensacao"]               = 237;
                $Registro0["nome_banco_extenso"]                            = "BRADESCO";
                $Registro0["data_gravacao_arquivo"]                         = date('dmy');
                $Registro0["reservado_futuro"]                              = " ";
                $Registro0["id_sistema"]                                    = "MX";
                $Registro0["numero_sequencial_arquivo"]                     = $numero_sequencial_arquivo;
                $Registro0["reservado_futuro2"]                             = " ";
                $Registro0["numero_sequencial_registro"]                    = $row->numero_registro_a;
                $content  = '';
                $content .= bradescoDebAuto400LayoutCNAB::Registro0($Registro0).PHP_EOL;


                // Busca as Parcelas
                $sql = "SELECT clientes.id, clientes.endereco, clientes.numero_endereco, clientes.complemento_endereco, clientes.bairro, 
                        clientes.nome, clientes.sobrenome, clientes.cpf, 
                        clientes_dados_debito.agencia_bancaria, clientes_dados_debito.conta_corrente,
                        cidades.nome_cidade, 
                        ufs.nome_uf, 
                        convenios_debito_em_conta.cod_convenio, forma_pagamento.dias_antecedencia_cobranca_debito, negocio_parcelas.*  
                        FROM `negocio_parcelas`
                        INNER JOIN negocios ON negocios.id = negocio_id 
                        INNER JOIN clientes_dados_debito ON negocios.conta_debito = clientes_dados_debito.id
                        INNER JOIN clientes ON negocios.cliente_id = clientes.id
                        INNER JOIN ufs ON ufs.id = clientes.uf
                        INNER JOIN cidades ON cidades.id = clientes.cidade
                        INNER JOIN forma_pagamento ON negocios.forma_pagamento = forma_pagamento.id
                        INNER JOIN convenios_debito_em_conta ON forma_pagamento.cod_convenio = convenios_debito_em_conta.id
                        WHERE negocio_parcelas.numero_registro_e = 0 AND negocio_parcelas.vencimento <= '$vencimento'"; 
				$res = $connection->query($sql);
                
                // Inicializa variáveis
                $Registro1 = array();
				$Registro6 = array();
                $numero_sequencial_registroE = 0;
				$soma_valores = 0;
                $conta = strval($row->conta_compromisso);
        
                while($row = $res->fetch_object())
				{
					// Atualiza o numero do registro 6 na parcela caso não esteja efetuando o cadastro
					$numero_sequencial_registroE = $numero_sequencial_registroE + 1;
                    
					// Verifica se a data de vencimento é menor que a data passada no parâmetro, se sim, atualiza o vencimento para o parametro passado
					if (str_replace('-', '', $row->vencimento) < str_replace('-', '',$vencimento))
					{
                        $data_vencimento = str_replace('-', '',$vencimento);
					} else {
                        $data_vencimento = str_replace('-', '', $row->vencimento);
					}
                    
					$sql  = "UPDATE `negocio_parcelas` SET `numero_registro_e` = ".$numero_sequencial_registroE.", vencimento = ".$data_vencimento." WHERE `id` = ".$row->id;
					$res2 = $connection->query($sql);
                    
					// Soma e Formata o valor da parcela
					$soma_valores = $soma_valores + $row->total;
					$inteiro      = intval($row->total);
					$centavos     = substr(number_format($row->total, 2, ',', '.'), strpos(number_format($row->total, 2, ',', '.'),',',0)+1, strlen(number_format($row->total, 2, ',', '.')));	
                    
                    // Preenche Array do REGISTRO 1"                    
                    $Registro1["cod_registro1"]                         = 1;
                    $Registro1["agencia_debito"]                        = substr($row->agencia_bancaria, 0, 4); 
                    $Registro1["digito_agencia_debito"]                 = substr($row->agencia_bancaria, -4, 1);
                    $Registro1["razao_conta_corrente"]                  = $row->conta_corrente;   
                    $Registro1["conta_corrente"]                        = substr($row->conta_corrente, 0, 5); 
                    $Registro1["digito_conta_corrente"]                 = substr($row->conta_corrente, -6, 1);; 
                    $Registro1["id_empresa_beneficiaria_banco"]         = "0" . $row->carteira . substr($conta, 1, 7,) . substr($conta, 0, -1) . str_repeat(" ", (7 - strlen($conta))) . substr($conta, -1, 1); 
                    $Registro1["num_controle_participante"]             = " "; 
                    $Registro1["cod_banco_deb_camara_compensacao"]      = 237;
                    $Registro1["campo_multa"]                           = 0;
                    $Registro1["percentual_multa"]                      = 0000;
                    $Registro1["id_titulo_banco"]                       = 00000000000;
                    $Registro1["digito_autoconferencia_num_bancario"]   = " ";
                    $Registro1["desconto_bonificacao_dia"]              = 0000000000;
                    $Registro1["condicao_emissao_papeleta_cobranca"]    = 0;
                    $Registro1["ident_emite_boleto_deb_auto"]           = "N";
                    $Registro1["id_operacao_banco"]                     = " ";
                    $Registro1["id_rateio_credito"]                     = " ";
                    $Registro1["enderacamento_aviso_deb_auto"]          = 2;
                    $Registro1["quantidade_pagamentos"]                 = " ";
                    $Registro1["id_ocorrencia"]                         = 01;
                    $Registro1["num_documento"]                         = " "; // Verificar com paulo
                    $Registro1["data_vencimento_titulo"]                = $data_vencimento; 
                    $Registro1["valor_titulo"]                          = $row->total;
                    $Registro1["banco_encarregado_cobranca"]            = 000;
                    $Registro1["agencia_depositaria"]                   = 00000;
                    $Registro1["identificacao"]                         = "N";
                    $Registro1["data_emissao_titulo"]                   = date('dmy');
                    $Registro1["instrucao1"]                            = 00;
                    $Registro1["instrucao2"]                            = 00;
                    $Registro1["valor_cobrado_dia_atraso"]              = 0000000000000;
                    $Registro1["data_limite_concessao_desconto"]        = 000000;
                    $Registro1["valor_desconto"]                        = 0000000000000;
                    $Registro1["valor_iof"]                             = 0000000000000;
                    $Registro1["valor_abatimento"]                      = 0000000000000;
                    $Registro1["id_tipo_inscricao_pagador"]             = 01;
                    $Registro1["num_inscricao_pagador"]                 = 000 . $row->cpf . 01;  
                    $Registro1["nome_pagador"]                          = $row->nome . ' ' . $row->sobrenome;
                    $Registro1["endereco_completo"]                     = $row->endereco.'-'.$row->numero_endereco.'-'.$row->complemento_endereco.'-'.$row->bairro.'-'.$row->nome_cidade.'-'.$row->nome_uf;
                    $Registro1["mensagem1"]                             = " ";
                    $Registro1["cep"]                                   = $row->cep;                                                                         ;
                    $Registro1["sufixo_cep"]                            = substr($row->cep, -3, 3);
                    $Registro1["mensagem2"]                             = " ";
                    $Registro1["num_sequencial_registro"]               = $numero_sequencial_registroE;
                    
                    $content .= bradescoDebAuto400LayoutCNAB::Registro1($Registro1).PHP_EOL;
                    
					// Preenche Array do REGISTRO 6"
					$Registro6["cod_registro6"]       			        = 6;
                    $Registro6["carteira"]                              = 999; // Preencher depois corretamente
                    $Registro6["agencia_debito"]                        = $row->agencia_bancaria;
                    $Registro6["conta_corrente"]                        = intval($row->conta_corrente);
                    $Registro6["numero_bradesco"]                       = 123456789111; // Preencher depois corretamente
                    $Registro6["digito_numero_bradesco"]                = "X"; // Preencher depois corretamente
                    $Registro6["tipo_operacao"]                         = 1;
                    $Registro6["utilizacao_cheque_especial"]            = "N";  // Preencher depois corretamente
                    $Registro6["consulta_saldo_apos_vencimento"]        = "N"; // Preencher depois corretamente
                    $Registro6["num_cod_id_contrato"]                   = $row->negocio_id; // Verificar o tamanho dos espaços
                    $Registro6["prazo_validade_contrato"]               = 99999999; // Preencher depois corretamente
                    $Registro6["reservado_futuro_6"]                    = " ";
                    $Registro6["num_sequencial_registro"]               = $numero_sequencial_registroE;
					
					$content .= bradescoDebAuto400LayoutCNAB::Registro6($Registro6).PHP_EOL;

				}
				

                // REGISTRO 9
                $Registro9 = array();
                $Registro9["cod_registro9"]            = 9;
                $Registro9["reservado_futuro_9"]       = " ";
                $Registro9["num_sequencial_registro"]  = 10; 
                $content .= bradescoDebAuto400LayoutCNAB::Registro9($Registro9).PHP_EOL;


                //Cria o arquivo
                $nome_arquivo = "CB".date('dm').str_pad($numero_sequencial_arquivo, 2 , '0' , STR_PAD_LEFT).".REM";
                $fp = fopen($_SERVER['DOCUMENT_ROOT'] ."/$nome_arquivo","wb");
                fwrite($fp,$content);
                fclose($fp);
                    
                // header("Location: index_bradesco.php");

                break;

            default:
					echo 'Layout não encontrado';
					break;

        }
    }
    
