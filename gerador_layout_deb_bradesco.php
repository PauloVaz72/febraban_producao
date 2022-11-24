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
        $res = $connection->query($sql);
        
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

                // REGISTRO A
                $RegistroA = array();
                $RegistroA["cod_registro"]                    = 0;
                $RegistroA["cod_remessa"]                     = 1;
                $RegistroA["literal_remessa"]                 = "REMESSA";
                $RegistroA["cod_servico"]                     = 1;
                $RegistroA["literal_servico"]                 = "COBRANCA";
                $RegistroA["cod_empresa"]                     = "99999999999999999999";
                $RegistroA["nome_empresa"]                    = $row->nome_empresa;
                $RegistroA["num_bradesco_camara_compensacao"] = 237;
                $RegistroA["nome_banco_extenso"]              = "BRADESCO";
                $RegistroA["data_gravacao_arquivo"]           = date('dmy');
                $RegistroA["reservado_futuro"]                = " ";
                $RegistroA["id_sistema"]                      = "MX";
                $RegistroA["numero_sequencial_arquivo"]       = $numero_sequencial_arquivo;
                $RegistroA["reservado_futuro2"]               = " ";
                $RegistroA["numero_sequencial_registro"]      = $row->numero_registro_a;
                $content  = '';
                $content .= bradescoDebAuto400LayoutCNAB::RegistroA($RegistroA).PHP_EOL;

                // REGISTRO E








                // REGISTRO Z
                $RegistroZ = array();
                $RegistroZ["cod_registro"]             = 9;
                $RegistroZ["reservado_futuro_Z"]       = " ";
                $RegistroZ["num_sequencial_registro"]  = 10; 
                $content .= bradescoDebAuto400LayoutCNAB::RegistroZ($RegistroZ).PHP_EOL;


                //Cria o arquivo
                $nome_arquivo = "CB".date('dm').str_pad($numero_sequencial_arquivo, 2 , '0' , STR_PAD_LEFT).".REM";
                $fp = fopen($_SERVER['DOCUMENT_ROOT'] ."/$nome_arquivo","wb");
                fwrite($fp,$content);
                fclose($fp);
                    
                header("Location: index_bradesco.php");

                break;

            default:
					echo 'Layout não encontrado';
					break;
        }
    }
