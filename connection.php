<?php
define ('HOST','mysql.intranetfuturo.com.br');
define ('USER','intranetfuturo01');
define ('PASS','Futuro2022');
define ('BASE','intranetfuturo01');  

// define ('HOST','localhost');
// define ('USER','root');
// define ('PASS','16062001');
// define ('BASE','intranet');  

$connection = new mysqli(HOST, USER, PASS, BASE); 