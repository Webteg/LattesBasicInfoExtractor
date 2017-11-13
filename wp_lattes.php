/*
 Inserir no functions.php:
    include('wp_lattes.php');
 Depois em qualquer página:
    [lattes]
*/
<?php
    function getPeople(){
        $txt_url="http://docs.google.com/document/export?format=txt&id=12uCOqdhoLR6QO-1ed6bE3epM-UW87YVCjpq2bYrgQ6s";
        $website = file_get_contents($txt_url);
        $people = explode("\r\n", remove_utf8_bom($website));
        return $people;
    }
    
    function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
    
    function getFotoUrl($id){
        $dir = '/var/www/pesquisa/pqes/pictures/';
        //$dir = './pictures/';
        
        $fotocnpqurl = 'http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id=';
        
        $url = get_redirect_target(get_redirect_target('http://buscatextual.cnpq.br/buscatextual/cv?id='.$id));
        $kid = substr($url, strpos($url, 'id=')+3);
        
        if (!file_exists($dir.$id)) {
            //file_put_contents($dir.$id, file_get_contents($fotocnpqurl.$kid));
            $handle = curl_init($fotocnpqurl.$kid);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            
            /* Get the HTML or whatever is linked in $url. */
            $response = curl_exec($handle);
            
            /* Check for 404 (file not found). */
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            
            if ($httpCode == 200) {
                /* Handle 200 here. */
                file_put_contents($dir.$id, $response);
                if(!exif_imagetype($dir.$id)) {
                    unlink($dir.$id);
                     return $fotocnpqurl.$kid;
                }
            }else if ($httpCode == 404) {
                return $fotocnpqurl.$kid;
            }
            curl_close($handle);
        }
        
        return 'pictures/'.$id;
    }
    
    function tmp(){
        $s = '--1) Colocar os seus alunos abaixo do nome de vocês (com quatro espaços de indentação)';
        echo '#'.$s.'#'."\r\n";
        echo substr(trim($s), 0, 2 );
        echo (substr(trim($s), 0, 2 ) === "--");
    }
    
    function getVitaes() {
        if (function_exists('get_transient')){
            $vitaes = get_transient( 'vitaes' );
            if (false !== $vitaes){
                echo '<!--cached-->'.$vitaes;
                return;
            }
        }
        
        $people = getPeople();
        
        $vitaes = '<table class="professores" border="0" cellspacing="2" cellpadding="1">';
        for($i = 0; $i < count($people); ++$i) {
            if (trim($people[$i])==='' || substr(trim($people[$i]), 0, 2 ) == "--"){
                continue;
            }
            
            $vitaes = $vitaes.'<tr>'."\r\n";
            
            $data = explode(",", $people[$i]);
            //var_dump($data);
            
            $professor = ($data[0][0] !== ' ');
            
            if ($professor){
                $vitaes = $vitaes.'<td class="foto"><div class="fotodiv" style="background-image: url('."'".getFotoUrl($data[1])."'".');"></div></td>'."\r\n";
                $vitaes = $vitaes.'<td class="nome" colspan="2"><a href="http://lattes.cnpq.br/'.$data[1].'">'.trim($data[0]).'</a></td>'."\r\n";
            }else{
                $vitaes = $vitaes.'<td></td>';
                $vitaes = $vitaes.'<td class="foto-aluno"><div class="foto-alunodiv" style="background-image: url('."'".getFotoUrl($data[1])."'".');"></div></td>'."\r\n";
                $vitaes = $vitaes.'<td class="nome-aluno"><a href="http://lattes.cnpq.br/'.$data[1].'">'.trim($data[0]).'</a> <span style="font-size: 50%">('.$data[2].')</span></td>'."\r\n";
            }
            
            $vitaes = $vitaes.'</tr>'."\r\n";
        }
        $vitaes = $vitaes.'</table>';
        
        if (function_exists('set_transient')){
            set_transient('vitaes', $vitaes,120); //Mudar para 86400
        }

        echo $vitaes;
    }


    
    /* Author: davejamesmiller */
    /* From: https://gist.github.com/davejamesmiller/dbefa0ff167cc5c08d6d */
    function get_redirect_target($url){
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

    if (function_exists('add_shortcode')){
        add_shortcode('lattes', 'getVitaes');
    }
?>
