<?php 
	namespace febraban;

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include("vendor/autoload.php");
	include('connection.php');

    //Recebendo por parâmetro na url convenio, vencimento e optante
	$convenio   = $_GET['convenio'];

	$vencimento = date('Y-m-d',strtotime($_GET['data']));

	$optante    = $_GET['optante'];

    if(isset($_GET['convenio']))
    {
        // Busca os dados do convenio
	    $sql = "SELECT * , bancos.nome_banco, bancos.codigo_febraban, convenios_debito_em_conta.banco_id
                FROM convenios_debito_em_conta 
                INNER JOIN bancos
                ON bancos.id = convenios_debito_em_conta.banco_id	
                WHERE `cod_convenio` = ".$convenio;
        $res = $connection->query($sql);
        $row = $res->fetch_object();

        
        //Inicializa as variáveis
		$cod_banco = $row->codigo_febraban;
		$convenio  = $row->cod_convenio;	
        $contador_registros = 2;
        $numero_sequencial_arquivo  = $row->numero_sequencial_arquivo  + 1;

        switch($cod_banco)
        {
            case 1:
                // Adiciona 1 ao numero sequencial do arquivo e guarda no convenio 
				$sql = "UPDATE `convenios_debito_em_conta` SET `numero_sequencial_arquivo` = $numero_sequencial_arquivo  WHERE `cod_convenio` = ".$convenio;
				$res = $connection->query($sql);

                //REGISTRO A
                $RegistroA = array();
                $RegistroA["cod_registro"]                  = "A";
                $RegistroA["cod_remessa"]                   = 1;
                $RegistroA["cod_convenio"]                  = $convenio;
                $RegistroA["nome_destinataria"]             = $row->nome_empresa;
                $RegistroA["cod_depositaria"]               = $cod_banco;
                $RegistroA["nome_depositaria"]              = $row->nome_banco;
                $RegistroA["data_geracao"]                  = Date('Ymd');
                $RegistroA["numero_sequencial_arquivo"]     = $numero_sequencial_arquivo;
                $RegistroA["versao_layout"]                 = $row->versao_layout;
                $RegistroA["identificacao_servico"]         = $row->identificacao_servico;
                $RegistroA["reservado_futuro"]              = " ";

                $content  = '';
                
                $content .= bbDebAuto150Layout::RegistroA($RegistroA).PHP_EOL;

                //REGISTRO E
                // Busca as parcelas
                $sql = "SELECT negocio_parcelas.id as parcela,
                        negocio_parcelas.negocio_id,
                        negocio_parcelas.documento, 
                        negocio_parcelas.vencimento,   
                        negocio_parcelas.valor,   
                        negocio_parcelas.data_pagamento,  
                        negocio_parcelas.multa,  
                        negocio_parcelas.juros,
                        negocio_parcelas.total, 
                        negocio_parcelas.pagamento_parcelas,
                        negocio_parcelas.cod_retorno,  
                        negocio_parcelas.cod_retorno1,  
                        negocio_parcelas.cod_retorno2,  
                        negocio_parcelas.cod_retorno3,
                        negocio_parcelas.cod_retorno4,
                        negocio_parcelas.cod_retorno5,  
                        negocio_parcelas.numero_parcela,   
                        negocio_parcelas.num_sequencial_arquivo_debito,
                        negocio_parcelas.numero_registro_e,  
                        negocio_parcelas.numero_agendamento_cliente,
                        negocio_parcelas.vencimento_original,
                        negocio_parcelas.valor_tarifa,
                        negocio_parcelas.status,
                        C.id,
                        C.endereco,
                        C.numero_endereco,
                        C.complemento_endereco,
                        C.bairro,
                        C.nome,
                        C.sobrenome,
                        C.cpf,
                        C.cep,
                        C.cidade,  
                        T.nome_cidade,
                        U.nome_uf,
                        V.cod_convenio,
                        F.dias_antecedencia_cobranca_debito,
                        D.agencia_bancaria,
                        D.conta_corrente,
                        V.mensagem_cliente,
                        V.codigo_carteira
                        FROM negocio_parcelas
                        INNER JOIN negocios as N ON N.id = negocio_id
                        INNER JOIN clientes as C ON N.cliente_id = C.id
                        INNER JOIN clientes_dados_debito as D ON N.conta_debito = D.id
                        LEFT JOIN cidades as T ON T.id = C.cidade
                        LEFT JOIN ufs as U ON U.id = T.uf_cidade
                        INNER JOIN forma_pagamento as F ON N.forma_pagamento = F.id
                        INNER JOIN convenios_debito_em_conta as V ON F.cod_convenio = V.id
                        WHERE negocio_parcelas.numero_registro_e = 0 AND negocio_parcelas.vencimento <= '$vencimento' AND V.cod_convenio = $convenio AND negocio_parcelas.status = 1";
			 $res3 = $connection->query($sql);
           
			$soma_valores = 0;
            
            while ($row2 = $res3->fetch_object())
            {
               if($optante == 0)
               {
                   // Verifica se a data de vencimento é menor que a data passada no parâmetro, se sim, atualiza o vencimento para o parametro passado
                   if (str_replace('-', '', $row2->vencimento) < str_replace('-', '', $vencimento))
                   {
                       $data_vencimento = str_replace('-', '', $vencimento);
                   } else {
                       $data_vencimento = str_replace('-', '', $row2->vencimento);
                   }
       
				    $sql  = "UPDATE `negocio_parcelas` SET `numero_registro_e` = " . $contador_registros . ", vencimento = " . $data_vencimento . ",`num_sequencial_arquivo_debito` = " . $numero_sequencial_arquivo . " WHERE `negocio_id` = " . $row2->negocio_id;
				    $res4 = $connection->query($sql);
                
                   // Soma e Formata o valor da parcela
                    $soma_valores = $soma_valores + $row2->total;
				    $inteiro      = intval($row2->total);
				    $centavos     = substr(number_format($row2->total, 2, ',', '.'), strpos(number_format($row2->total, 2, ',', '.'), ',', 0) + 1, strlen(number_format($row2->total, 2, ',', '.')));

				    $formata_vencimento = date('dmy', strtotime($row2->vencimento));
                   
               } else {
                   $data_vencimento = '00000000';
                   $soma_valores    = 0;
                   $inteiro         = 0;
                   $centavos        = '00';
               }

                $limpa_campo_conta_corrente =   [
                                                    'A', 'a', 'B', 'b', 'C', 'c',
                                                    'D', 'd', 'E', 'e', 'F', 'f',
                                                    'G', 'g', 'H', 'h', 'I', 'i',
                                                    'J', 'j', 'K', 'k', 'L', 'l',
                                                    'M', 'm', 'N', 'n', 'O', 'o',
                                                    'P', 'p', 'Q', 'q', 'R', 'r',
                                                    'S', 's', 'T', 't', 'U', 'u',
                                                    'V', 'v', 'W', 'w', 'X', 'x',
                                                    'Y', 'y', 'Z', 'z'
				                                ];

                // Preenche array do Registro E
                $RegistroE = array();
                $RegistroE["cod_registro_e"]                = "E";
                $RegistroE["id_cliente_destinataria"]       = $row2->cpf;
                $RegistroE["agencia_debito"]                = $row2->agencia_bancaria;
                $RegistroE["id_cliente_depositaria"]        = intval(str_replace($limpa_campo_conta_corrente, 0, $row2->conta_corrente));
                $RegistroE["prazo_validade_contrato"]       = $data_vencimento;
                $RegistroE["valor_debito"]                  = intval($inteiro.$centavos);
                $RegistroE["cod_moeda"]                     = 03;
                $RegistroE["uso_instituicao_destinataria"]  = $row2->parcela;
                $RegistroE["uso_instituicao_destinataria2"] = "X"; 
                $RegistroE["tipo_identificacao"]            = 2;
                $RegistroE["identificacao"]                 = $row2->cpf;
                $RegistroE["tipo_operacao"]                 = 3;
                $RegistroE["utilizacao_cheque_especial"]    = 2;
                $RegistroE["opcao_debito_parcial"]          = 2;
                $RegistroE["reservado_futuro_E"]            = " ";

                if($optante == 0) 
                {
                    $RegistroE["cod_movimento"]        =  0;
                } else {
                    $RegistroE["cod_movimento"]        =  5;
                }
              
                $contador_registros += +1;

                $content .= bbDebAuto150Layout::RegistroE($RegistroE).PHP_EOL;
            }

            // Registro Z, confere a somatoria dos Registros E
                $RegistroZ = array();
                $inteiro 										= intval($soma_valores);
                $centavos 										= substr(number_format($soma_valores, 2, ',', '.'), strpos(number_format($soma_valores, 2, ',', '.'),',',0)+1, strlen(number_format($soma_valores, 2, ',', '.')));
                $RegistroZ["cod_registro_z"]                    = "Z";
                $RegistroZ["total_registros_arquivo"]           =  $contador_registros;
                $RegistroZ["valor_total_registros_arquivo"]     = $inteiro.$centavos;
                $RegistroZ["reservado_futuro_Z"]                = " ";

                $content .= bbDebAuto150Layout::RegistroZ($RegistroZ).PHP_EOL;

                //Cria o arquivo
                $nome_arquivo = "DEB_".$cod_banco."_".$convenio."_".date('ymd')."_".str_pad($numero_sequencial_arquivo, 5 , '0' , STR_PAD_LEFT).".REM";
                $fp = fopen($_SERVER['DOCUMENT_ROOT'] ."/$nome_arquivo","wb");
                fwrite($fp,$content);
                fclose($fp);
                    
                header("Location: index_bb.php");

                break;

            default:
                    echo 'Layout não encontrado';
                 
                break;
        }
    }
