<?php
    $foto = 'http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id=';
    
	$txt_url="http://docs.google.com/document/export?format=txt&id=12uCOqdhoLR6QO-1ed6bE3epM-UW87YVCjpq2bYrgQ6s";
	$website = file_get_contents($txt_url);

	$people = explode("\r\n", $website);       
	//var_dump($people);

	echo '<table>';	
	for($i = 0; $i < count($people); ++$i) {
		echo '<tr>'."\r\n";
		
        if (trim($people[$i])===''){
            continue;
        }
        
        $data = explode(",", $people[$i]);
        //var_dump($data);
        
		$url = get_redirect_target(get_redirect_target('http://buscatextual.cnpq.br/buscatextual/cv?id='.$data[1]));
        $kid = substr($url, strpos($url, 'id=')+3);
        
        $professor = ($data[0][0] !== ' ');
        
        if (!$professor){
            echo '<td></td>';
        }
        echo '<td><img src="'.$foto.$kid.'"/></td>'."\r\n";
        echo '<td><a href="http://lattes.cnpq.br/'.$data[1].'">'.trim($data[0]).'</a></td>'."\r\n";
        
        if (!$professor){
            echo '<td>'.$data[2].'</td>';
        }
        
		echo '</tr>'."\r\n";
	}
	echo '</table>';



    
/* Author: davejamesmiller */
/* From: https://gist.github.com/davejamesmiller/dbefa0ff167cc5c08d6d */
function get_redirect_target($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = curl_exec($ch);
    curl_close($ch);
    // Check if there's a Location: header (redirect)
    if (preg_match('/^Location: (.+)$/im', $headers, $matches))
        return trim($matches[1]);
    // If not, there was no redirect so return the original URL
    // (Alternatively change this to return false)
    return $url;
}

?>
