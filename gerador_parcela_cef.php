<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include('connection.php');

    // Pegando apenas o dia da data passada por parâmetro
    $dia = date('d',strtotime($_GET['data'])); 

    // Pegando a data informada por parâmetro
    $data = date('Ymd',strtotime(substr($_GET['data'],8,2).'-'.substr($_GET['data'],5,2).'-'.substr($_GET['data'],0,4)));
    
    // Passando por parâmetro optante
    $optante = $_GET['optante'];

    // Passando por parâmetro cod do convênio
    $convenio = $_GET['convenio'];

    // Gera parcelas
    while ($dia >= 1)
    {
            // Consulta negócio pelo dia da data informada
            $sql = "SELECT * FROM `negocios` WHERE dia_debito = $dia AND status_negocio = 1";          
            $res = $connection->query($sql);
            
            while($row = $res->fetch_object())
            {   
                $data_original =  date('Ymd', strtotime($row->dia_debito. '-' .substr($_GET['data'],5,2). '-' . substr($_GET['data'],0,4)));
                
                // Gera apenas parcelas aonde minha data original (dia + mês + ano) seja maior ou igual a minha data de venda de negócios, evitando cobrança retroativa 
                if($data_original >= $row->data_venda)
                { 
                    // Se possui meu negocio ele busca na data de vencimento informada
                    $sql = "SELECT id FROM negocio_parcelas WHERE negocio_id = $row->id AND vencimento_original = '$data_original'";
                    $res2 = $connection->query($sql);

                    // Verifica se meu negocio possui parcela
                    if($res2->lengths == null)
                    {
                        // Conta meu número de parcelas pelo meu negocio                    
                        $sql = "SELECT COUNT(*) AS contador FROM negocio_parcelas WHERE negocio_id = $row->id";
                        $res3 = $connection->query($sql);

                        $row3 = $res3->fetch_object();

                        // Acrescenta sempre uma parcela pelo meu contador gerado a partir do negocio 
                        $numero_parcelas = $row3->contador;
                        // Geramos nossa parcela e inserimos os dados no banco
                        $sql = "INSERT INTO negocio_parcelas (negocio_id, vencimento, valor, total, pagamento_parcelas, numero_parcela, vencimento_original)
                                VALUES ($row->id, $data, $row->valor_total, $row->valor_total, 0, $numero_parcelas, $data_original)";
                        $res4 = $connection->query($sql);
                        
                    }
                }
            }

            $dia+= -1;
    }

         header("Location: index_cef.php");

    ?>