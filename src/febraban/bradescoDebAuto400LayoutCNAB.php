<?php

namespace febraban;

class bradescoDebAuto400LayoutCNAB{
    
    public static function Arquivo($cfg)
	{
		$salvar_remessa_em   = isset($cfg['salvar_remessa_em']) ? $cfg['salvar_remessa_em'] :' ';
		$nome_aquivo_remessa = isset($cfg['nome_aquivo_remessa']) ? $cfg['nome_aquivo_remessa'] : Date('dmY');
	}


    public static function RegistroA($cfg)
    {

        $cod_registro 						   = isset($cfg['cod_registro']) ? $cfg['cod_registro'] :' ';
        $cod_remessa  						   = isset($cfg['cod_remessa']) ? $cfg['cod_remessa'] : 1;
        $literal_remessa                       = isset($cfg['literal_remessa']) ? $cfg['literal_remessa'] : ' ';
        $cod_servico                           = isset($cfg['cod_servico']) ? $cfg['cod_servico'] : ' ';
        $literal_servico                       = isset($cfg['literal_servico']) ? $cfg['literal_servico'] : ' ';
        $cod_empresa                           = isset($cfg['cod_empresa']) ? $cfg['cod_empresa'] : ' ';
        $nome_empresa                          = isset($cfg['nome_empresa']) ? $cfg['nome_empresa'] : ' ';
        $num_bradesco_camara_compensacao       = isset($cfg['num_bradesco_camara_compensacao']) ? $cfg['num_bradesco_camara_compensacao'] : ' ';
        $nome_banco_extenso                    = isset($cfg['nome_banco_extenso']) ? $cfg['nome_banco_extenso'] : ' ';     
        $data_gravacao_arquivo                 = isset($cfg['data_gravacao_arquivo']) ? $cfg['data_gravacao_arquivo'] : ' ';
        $reservado_futuro                      = isset($cfg['reservado_futuro']) ? $cfg['reservado_futuro'] : ' ';
        $id_sistema                            = isset($cfg['id_sistema']) ? $cfg['id_sistema'] : ' ';
        $numero_sequencial_arquivo             = isset($cfg['numero_sequencial_arquivo']) ? $cfg['numero_sequencial_arquivo'] : ' ';
        $reservado_futuro2                     = isset($cfg['reservado_futuro2']) ? $cfg['reservado_futuro2'] : ' ';
        $numero_sequencial_registro            = isset($cfg['numero_sequencial_registro']) ? $cfg['numero_sequencial_registro'] : ' ';

        $campos                                = array();
        $campos['cod_registro']                            = array(1,1, '9:1',$cod_registro);
        $campos['cod_remessa']                             = array(2,2, '9:1',$cod_remessa);
        $campos['literal_remessa']                         = array(3,9, 'X:7',$literal_remessa);
        $campos['cod_servico']                             = array(10,11, '9:2',$cod_servico);
        $campos['literal_servico']                         = array(12,26, 'X:15',$literal_servico);
        $campos['cod_empresa']                             = array(27,46, '9:20',$cod_empresa);
        $campos['nome_empresa']                            = array(47,76, 'X:30',$nome_empresa);
        $campos['num_bradesco_camara_compensacao']         = array(77,79, '9:3',$num_bradesco_camara_compensacao);
        $campos['nome_banco_extenso']                      = array(80,94, 'X:15',$nome_banco_extenso);
        $campos['data_gravacao_arquivo']                   = array(95,100, '9:6',$data_gravacao_arquivo);
        $campos['reservado_futuro']                        = array(101,108, 'X:8',$reservado_futuro);
        $campos['id_sistema']                              = array(109,110, 'X:2',$id_sistema);
        $campos['numero_sequencial_arquivo']               = array(111,117, '9:7',$numero_sequencial_arquivo);
        $campos['$reservado_futuro2']                      = array(118,394, 'X:277',$reservado_futuro2);
        $campos['numero_sequencial_registro']              = array(395,400, '9:6',$numero_sequencial_registro);

        return bradescoDebAuto400LayoutCNAB::FormatarCampos($campos);

    }


    public static function RegistroE($cfg)
    {
        



    }



	public static function FormatarCampos($campos)
	{

		$RegistroA      = ''; //String do Registro A
		$permiteBrancos = array('reservado_futuro','uso_empresa'); //Campos que podem ser vazios

		foreach($campos as $index=>$value){
			
			$strInicio    = isset($value[0]) ? $value[0] : 1; 
			$strFim       = isset($value[1]) ? $value[1] : 1; 			
			$strValidacao = isset($value[2]) ? $value[2] : 'X:1';
			$strV         = isset($value[3]) ? $value[3] : '';
			$strValor     = bradescoDebAuto400LayoutCNAB::clearString($strV);

			$x = explode(':',$strValidacao); //Identifica tipo de validação x ou 9

			$validacao = isset($x[0]) ? $x[0] : 'X';
			$tamanho   = isset($x[1]) ? $x[1] : 1;

			if(!in_array($index,$permiteBrancos)&&$strValor===NULL)
			{
				
				die('O campo '.$index.' ('.$strValidacao.') não pode estar em branco! {'.$strValor.'}');
			
			} else {

				if($validacao=='X')
				{
					$contaCaracteres = strlen($strValor); //Conta valor recebido 

					if($contaCaracteres!=$tamanho)
					{
						$limitaTamanho   = substr($strValor,0,$tamanho); //Força string a caber no tamanho
						$completaTamanho = str_pad($limitaTamanho,$tamanho," ", STR_PAD_RIGHT); //completa com espaços caracteres faltantes 
						$strCampo        = $completaTamanho; 
					} else {
						$strCampo = $strValor;
					}

					$RegistroA .= $strCampo;

				} else {

					if(ctype_digit($strValor))
					{
						$converToString  = (string) $strValor;
						$limitaTamanho   = substr($converToString,0,$tamanho); //Força string a caber no tamanho
						$completaTamanho = str_pad($limitaTamanho,$tamanho, "0", STR_PAD_LEFT); //Completa com espaços caracteres faltantes 
						$strCampo        = $completaTamanho; 
						$RegistroA .= $strCampo;
					} else {
						echo('O campo '.$index.' ('.$strValidacao.') deve conter apenas numeros!');
					}
				}
			}
		}//Fim foreach

	  	return $RegistroA;
		
	}
	
	private static function clearString($string){
	    $limpa = preg_replace(array("/(ç)/","/(Ç)/","/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","c C a A e E i I o O u U n N"),$string);
	    return strtoupper(strtolower($limpa));
		// Para usar
	}
}
